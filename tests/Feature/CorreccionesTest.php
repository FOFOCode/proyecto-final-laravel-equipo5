<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorreccionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CategoriaSeeder::class);
    }

    public function test_form_only_orders_checked_dishes_and_preserves_error_indices(): void
    {
        $dish = Platillo::factory()->create(['id' => 4, 'disponible' => true, 'precio' => 12.50]);
        $payload = ['cliente' => 'Cliente prueba', 'mesa' => 1, 'platillos' => [
            0 => ['cantidad' => 1],
            4 => ['platillo_id' => $dish->id, 'cantidad' => 2],
        ]];
        $this->post(route('pedidos.store'), $payload)->assertRedirect(route('pedidos.index'));
        $this->assertDatabaseCount('detalle_pedidos', 1);
        $this->assertSame('25.00', Pedido::first()->total);
        $payload['platillos'][4]['cantidad'] = 0;
        $this->from(route('pedidos.create'))->post(route('pedidos.store'), $payload)
            ->assertSessionHasErrors(['platillos.4.cantidad' => 'La cantidad debe estar entre 1 y 20.']);
        $payload['platillos'] = [['cantidad' => 1]];
        $this->post(route('pedidos.store'), $payload)->assertSessionHasErrors('platillos');
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_invalid_prices_cannot_create_or_change_dishes(): void
    {
        $dish = Platillo::factory()->create(['precio' => 12.50]);
        $payload = $dish->only(['nombre', 'categoria_id', 'precio', 'descripcion', 'tiempo_preparacion']);
        foreach (['0.001', '1000000', '1e309', '12.345'] as $price) {
            $payload['precio'] = $price;
            $this->putJson(route('platillos.update', $dish), $payload)->assertUnprocessable()->assertJsonValidationErrors('precio');
            $payload['nombre'] = 'Otro platillo';
            $this->postJson(route('platillos.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('precio');
        }
        $this->assertSame('12.50', $dish->fresh()->precio);
        $this->assertDatabaseCount('platillos', 1);
    }

    public function test_failed_database_writes_keep_old_photo_and_remove_new_upload(): void
    {
        Storage::fake('public');
        $old = UploadedFile::fake()->image('anterior.jpg')->store('platillos', 'public');
        $dish = Platillo::factory()->create(['imagen' => $old]);
        $payload = $dish->only(['nombre', 'categoria_id', 'precio', 'descripcion', 'tiempo_preparacion']);
        $payload['imagen'] = UploadedFile::fake()->image('nueva.jpg');
        Platillo::updating(fn () => throw new \RuntimeException('Fallo de escritura simulado'));
        Platillo::creating(fn () => throw new \RuntimeException('Fallo de escritura simulado'));
        try {
            $this->put(route('platillos.update', $dish), $payload)->assertStatus(500);
            $this->assertSame($old, $dish->fresh()->imagen);
            $this->assertSame([$old], Storage::disk('public')->allFiles('platillos'));
            $payload['nombre'] = 'Creacion fallida';
            $payload['imagen'] = UploadedFile::fake()->image('otra.jpg');
            $this->post(route('platillos.store'), $payload)->assertStatus(500);
            $this->assertSame([$old], Storage::disk('public')->allFiles('platillos'));
            $this->assertDatabaseCount('platillos', 1);
        } finally {
            Platillo::flushEventListeners();
        }
    }

    public function test_invalid_filters_return_validation_instead_of_server_error(): void
    {
        $this->getJson('/platillos?buscar[]=texto')->assertUnprocessable()->assertJsonValidationErrors('buscar');
        $this->getJson('/platillos?categoria_id=texto')->assertUnprocessable()->assertJsonValidationErrors('categoria_id');
        $this->getJson('/pedidos?estado=inventado')->assertUnprocessable()->assertJsonValidationErrors('estado');
    }

    public function test_order_total_overflow_rolls_back_the_entire_order(): void
    {
        $dishes = Platillo::factory()->count(6)->create(['disponible' => true, 'precio' => '999999.99']);
        $this->postJson(route('pedidos.store'), [
            'cliente' => 'Pedido excesivo', 'mesa' => 1,
            'platillos' => $dishes->map(fn ($dish) => ['platillo_id' => $dish->id, 'cantidad' => 20])->all(),
        ])->assertUnprocessable()->assertJsonValidationErrors('platillos');
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);
    }

    public function test_daily_report_uses_local_day_and_keeps_unpaid_sales_rule(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-12 08:00:00', 'UTC'));
        foreach (['2026-11-12 05:59:59', '2026-11-12 06:00:00', '2026-11-13 06:00:00'] as $index => $date) {
            Pedido::create(['codigo' => 'LOCAL-'.$index, 'cliente' => 'Cliente', 'mesa' => 1,
                'fecha' => $date, 'estado' => 'pendiente', 'total' => 10]);
        }
        $this->get(route('panel.index'))->assertOk()
            ->assertViewHas('ventasHoy', fn ($total) => (float) $total === 10.0)
            ->assertViewHas('pedidosHoy', 1)
            ->assertSee('incluye pedidos sin pagar')
            ->assertSee('12/11/2026 00:00');
    }
}
