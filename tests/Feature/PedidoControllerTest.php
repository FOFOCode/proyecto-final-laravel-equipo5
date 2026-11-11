<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Platillo;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
    }

    public function test_store_calculates_order_total_from_database_prices(): void
    {
        $platillo = Platillo::factory()->create([
            'categoria_id' => Categoria::firstOrFail()->id,
            'precio' => 12.50,
            'disponible' => true,
        ]);

        $response = $this->post(route('pedidos.store'), [
            'cliente' => 'Cliente de prueba',
            'mesa' => 4,
            'notas' => 'Sin cebolla',
            'total' => 0.01,
            'platillos' => [[
                'platillo_id' => $platillo->id,
                'cantidad' => 2,
                'precio_unitario' => 0.01,
                'subtotal' => 0.01,
            ]],
        ]);

        $pedido = Pedido::with('detalles')->firstOrFail();
        $response->assertRedirect(route('pedidos.index'))
            ->assertSessionHas('exito');
        $this->assertSame('25.00', $pedido->total);
        $this->assertSame('12.50', $pedido->detalles->first()->precio_unitario);
        $this->assertSame('25.00', $pedido->detalles->first()->subtotal);
        $this->assertSame(2, $pedido->detalles->first()->cantidad);
    }

    public function test_unavailable_dish_rejects_order_without_saving_partial_data(): void
    {
        $platillo = Platillo::factory()->create([
            'categoria_id' => Categoria::firstOrFail()->id,
            'disponible' => false,
        ]);

        $this->from(route('platillos.index'))
            ->post(route('pedidos.store'), [
                'cliente' => 'Cliente de prueba',
                'mesa' => 4,
                'platillos' => [['platillo_id' => $platillo->id, 'cantidad' => 1]],
            ])
            ->assertRedirect(route('platillos.index'))
            ->assertSessionHasErrors('platillos.0.platillo_id');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);
    }

    public function test_order_requires_a_dish_and_valid_quantities(): void
    {
        $this->from(route('platillos.index'))
            ->post(route('pedidos.store'), [
                'cliente' => '',
                'mesa' => 0,
                'platillos' => [],
            ])
            ->assertRedirect(route('platillos.index'))
            ->assertSessionHasErrors(['cliente', 'mesa', 'platillos']);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_update_estado_changes_only_to_a_supported_order_state(): void
    {
        $pedido = Pedido::create([
            'codigo' => 'PED-STATE-001',
            'cliente' => 'Cliente estado',
            'mesa' => 3,
            'fecha' => now(),
            'estado' => 'pendiente',
            'total' => 10,
        ]);

        $this->from(route('platillos.index'))
            ->patch(route('pedidos.updateEstado', $pedido), ['estado' => 'en_preparacion'])
            ->assertRedirect(route('platillos.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado' => 'en_preparacion',
        ]);

        $this->from(route('platillos.index'))
            ->patch(route('pedidos.updateEstado', $pedido), ['estado' => 'invalido'])
            ->assertRedirect(route('platillos.index'))
            ->assertSessionHasErrors('estado');

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado' => 'en_preparacion',
        ]);
    }
}
