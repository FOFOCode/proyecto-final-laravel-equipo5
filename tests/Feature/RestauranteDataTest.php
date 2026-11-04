<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Platillo;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestauranteDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_seeder_creates_four_active_categories_without_duplicates(): void
    {
        $this->seed(CategoriaSeeder::class);
        $this->seed(CategoriaSeeder::class);

        $this->assertDatabaseCount('categorias', 4);
        $this->assertEqualsCanonicalizing(
            ['Entradas', 'Platos fuertes', 'Bebidas', 'Postres'],
            Categoria::pluck('nombre')->all(),
        );

        foreach (Categoria::all() as $categoria) {
            $this->assertTrue($categoria->activo);
            $this->assertNotEmpty($categoria->descripcion);
        }
    }

    public function test_database_seeder_creates_twenty_valid_unique_dishes(): void
    {
        $this->seed();

        $this->assertDatabaseCount('categorias', 4);
        $this->assertDatabaseCount('platillos', 20);
        $this->assertSame(20, Platillo::distinct()->count('nombre'));
        $this->assertSame(4, Platillo::distinct()->count('categoria_id'));

        foreach (Platillo::with('categoria')->get() as $platillo) {
            $this->assertNotEmpty($platillo->nombre);
            $this->assertNotEmpty($platillo->descripcion);
            $this->assertGreaterThanOrEqual(3.50, (float) $platillo->precio);
            $this->assertLessThanOrEqual(25.00, (float) $platillo->precio);
            $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $platillo->precio);
            $this->assertIsInt($platillo->tiempo_preparacion);
            $this->assertGreaterThanOrEqual(10, $platillo->tiempo_preparacion);
            $this->assertLessThanOrEqual(45, $platillo->tiempo_preparacion);
            $this->assertIsBool($platillo->disponible);
            $this->assertNull($platillo->imagen);
            $this->assertInstanceOf(Categoria::class, $platillo->categoria);
            $this->assertTrue($platillo->categoria->platillos->contains($platillo));
        }
    }

    public function test_order_relationships_preserve_pivot_values_and_cascade_details(): void
    {
        $this->seed(CategoriaSeeder::class);
        $platillo = Platillo::factory()->create();
        $pedido = Pedido::create([
            'codigo' => 'PED-TEST-001',
            'cliente' => 'Cliente de prueba',
            'mesa' => '2',
            'fecha' => '2026-11-04 12:00:00',
            'estado' => 'pendiente',
            'total' => '25.00',
            'notas' => 'Sin picante',
        ]);
        $detalle = $pedido->detalles()->create([
            'platillo_id' => $platillo->id,
            'cantidad' => '2',
            'precio_unitario' => '12.50',
            'subtotal' => '25.00',
        ]);

        $this->assertSame(2, $pedido->fresh()->mesa);
        $this->assertSame('2026-11-04', $pedido->fresh()->fecha->toDateString());
        $this->assertSame('25.00', $pedido->fresh()->total);
        $this->assertSame(2, $detalle->fresh()->cantidad);
        $this->assertSame('12.50', $detalle->fresh()->precio_unitario);
        $this->assertSame('25.00', $detalle->fresh()->subtotal);
        $this->assertTrue($detalle->pedido->is($pedido));
        $this->assertTrue($detalle->platillo->is($platillo));
        $this->assertTrue($platillo->detallePedidos->contains($detalle));

        foreach ([$pedido->platillos->first(), $platillo->pedidos->first()] as $related) {
            $this->assertNotNull($related);
            $this->assertSame(2, (int) $related->pivot->cantidad);
            $this->assertEquals(12.50, $related->pivot->precio_unitario);
            $this->assertEquals(25.00, $related->pivot->subtotal);
            $this->assertNotNull($related->pivot->created_at);
            $this->assertNotNull($related->pivot->updated_at);
        }

        try {
            $platillo->delete();
            $this->fail('No se debe eliminar un platillo usado en un pedido.');
        } catch (QueryException) {
            $this->assertDatabaseHas('platillos', ['id' => $platillo->id]);
        }

        try {
            $detalle->replicate()->save();
            $this->fail('No se debe repetir un platillo en el mismo pedido.');
        } catch (QueryException) {
            $this->assertDatabaseCount('detalle_pedidos', 1);
        }

        $pedido->delete();
        $this->assertDatabaseCount('detalle_pedidos', 0);
        $this->assertDatabaseHas('platillos', ['id' => $platillo->id]);
    }

    public function test_category_with_dishes_cannot_be_deleted(): void
    {
        $this->seed(CategoriaSeeder::class);
        $platillo = Platillo::factory()->create();

        $this->expectException(QueryException::class);
        $platillo->categoria->delete();
    }

    public function test_dish_names_must_be_unique(): void
    {
        $this->seed(CategoriaSeeder::class);
        $platillo = Platillo::factory()->create();

        $this->expectException(QueryException::class);
        $platillo->replicate()->save();
    }

    public function test_dishes_require_an_existing_category(): void
    {
        $this->seed(CategoriaSeeder::class);

        $this->expectException(QueryException::class);
        Platillo::factory()->create(['categoria_id' => 999999]);
    }
}
