<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Platillo;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\PedidoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_displays_sales_and_top_selling_dishes(): void
    {
        $this->seed(CategoriaSeeder::class);
        $categoria = Categoria::firstOrFail();
        $platillo = Platillo::factory()->create(['categoria_id' => $categoria->id, 'precio' => 10]);
        $pedido = Pedido::create([
            'codigo' => 'PED-PANEL-001',
            'cliente' => 'Cliente Panel',
            'mesa' => 2,
            'fecha' => now(),
            'estado' => 'pagado',
            'total' => 20,
        ]);
        $pedido->platillos()->attach($platillo, [
            'cantidad' => 2,
            'precio_unitario' => 10,
            'subtotal' => 20,
        ]);
        $pedidoCancelado = Pedido::create([
            'codigo' => 'PED-PANEL-002',
            'cliente' => 'Cliente cancelado',
            'mesa' => 3,
            'fecha' => now(),
            'estado' => 'cancelado',
            'total' => 100,
        ]);
        $pedidoCancelado->platillos()->attach($platillo, [
            'cantidad' => 10,
            'precio_unitario' => 10,
            'subtotal' => 100,
        ]);

        $this->get(route('panel.index'))
            ->assertOk()
            ->assertSee('$20.00')
            ->assertSee('Pedidos atendidos')
            ->assertSee($platillo->nombre)
            ->assertSee($pedido->codigo)
            ->assertSee('Pagado')
            ->assertViewHas('ventasHoy', fn ($ventas) => (float) $ventas === 20.0)
            ->assertViewHas('platillosMasVendidos', fn ($platillos) => (int) $platillos->first()?->total_vendido === 2)
            ->assertViewHas('ventasPorCategoria', fn ($categorias) => (float) $categorias->first()['total'] === 20.0);
    }

    public function test_pedido_seeder_creates_repeatable_orders_and_valid_totals(): void
    {
        $this->seed();
        $this->seed(PedidoSeeder::class);
        $this->seed(PedidoSeeder::class);

        $this->assertDatabaseCount('pedidos', 12);
        $this->assertDatabaseCount('detalle_pedidos', 32);

        foreach (Pedido::with('detalles')->get() as $pedido) {
            $this->assertNotEmpty($pedido->detalles);
            $this->assertEquals(
                $pedido->detalles->sum('subtotal'),
                $pedido->total,
            );
        }
    }
}
