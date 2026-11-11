<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Platillo;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatilloCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);
        Storage::fake('public');
    }

    public function test_create_form_displays_active_categories(): void
    {
        $response = $this->get(route('platillos.create'));
        $response->assertOk()
            ->assertSee('Registrar Nuevo Platillo')
            ->assertSee('Platos fuertes')
            ->assertSee('Bebidas');
    }

    public function test_can_store_new_platillo_with_image(): void
    {
        $categoria = Categoria::first();
        $imagen = UploadedFile::fake()->image('ceviche.jpg', 600, 400);

        $payload = [
            'nombre' => 'Ceviche Clásico Peruano',
            'categoria_id' => $categoria->id,
            'precio' => 14.50,
            'tiempo_preparacion' => 15,
            'descripcion' => 'Pescado fresco marinado en jugo de limón con cebolla morada y cilantro.',
            'disponible' => '1',
            'imagen' => $imagen,
        ];

        $response = $this->post(route('platillos.store'), $payload);

        $response->assertRedirect(route('platillos.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('platillos', [
            'nombre' => 'Ceviche Clásico Peruano',
            'precio' => 14.50,
            'tiempo_preparacion' => 15,
            'disponible' => true,
        ]);

        $platillo = Platillo::where('nombre', 'Ceviche Clásico Peruano')->first();
        $this->assertNotNull($platillo->imagen);
        Storage::disk('public')->assertExists($platillo->imagen);
    }

    public function test_business_rules_and_server_validations_are_enforced(): void
    {
        $categoria = Categoria::first();
        Platillo::factory()->create(['nombre' => 'Platillo Duplicado']);

        // 1. Validar precio menor o igual a 0
        $response1 = $this->post(route('platillos.store'), [
            'nombre' => 'Sopa Económica',
            'categoria_id' => $categoria->id,
            'precio' => 0.00,
            'tiempo_preparacion' => 20,
            'descripcion' => 'Sopa del día.',
        ]);
        $response1->assertSessionHasErrors(['precio']);

        // 2. Validar tiempo fuera de rango (mayor a 180 o menor a 1)
        $response2 = $this->post(route('platillos.store'), [
            'nombre' => 'Asado Lento',
            'categoria_id' => $categoria->id,
            'precio' => 20.00,
            'tiempo_preparacion' => 200,
            'descripcion' => 'Cocción ultra lenta.',
        ]);
        $response2->assertSessionHasErrors(['tiempo_preparacion']);

        // 3. Validar nombre unico
        $response3 = $this->post(route('platillos.store'), [
            'nombre' => 'Platillo Duplicado',
            'categoria_id' => $categoria->id,
            'precio' => 15.00,
            'tiempo_preparacion' => 30,
            'descripcion' => 'Intento de duplicado.',
        ]);
        $response3->assertSessionHasErrors(['nombre']);

        // 4. Validar categoria inexistente
        $response4 = $this->post(route('platillos.store'), [
            'nombre' => 'Platillo Huerfano',
            'categoria_id' => 99999,
            'precio' => 10.00,
            'tiempo_preparacion' => 20,
            'descripcion' => 'Sin categoría válida.',
        ]);
        $response4->assertSessionHasErrors(['categoria_id']);
    }

    public function test_edit_form_loads_with_preloaded_data(): void
    {
        $platillo = Platillo::factory()->create();

        $response = $this->get(route('platillos.edit', $platillo));
        $response->assertOk()
            ->assertSee($platillo->nombre)
            ->assertSee($platillo->descripcion)
            ->assertSee('Actualizar Platillo');
    }

    public function test_can_update_platillo_and_replaces_old_image(): void
    {
        $oldImage = UploadedFile::fake()->image('antigua.jpg')->store('platillos', 'public');
        $platillo = Platillo::factory()->create(['imagen' => $oldImage]);

        Storage::disk('public')->assertExists($oldImage);

        $newImage = UploadedFile::fake()->image('nueva.jpg');

        $response = $this->put(route('platillos.update', $platillo), [
            'nombre' => 'Nombre Modificado',
            'categoria_id' => $platillo->categoria_id,
            'precio' => 22.00,
            'tiempo_preparacion' => 35,
            'descripcion' => 'Nueva descripción editada.',
            'disponible' => '1',
            'imagen' => $newImage,
        ]);

        $response->assertRedirect(route('platillos.index'))
            ->assertSessionHas('exito');

        $platillo->refresh();
        $this->assertEquals('Nombre Modificado', $platillo->nombre);
        $this->assertEquals(22.00, $platillo->precio);

        // La imagen antigua debe haber sido eliminada y la nueva debe existir
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($platillo->imagen);
    }

    public function test_can_delete_dish_without_associated_orders(): void
    {
        $image = UploadedFile::fake()->image('borrar.jpg')->store('platillos', 'public');
        $platillo = Platillo::factory()->create(['imagen' => $image]);

        Storage::disk('public')->assertExists($image);

        $response = $this->delete(route('platillos.destroy', $platillo));

        $response->assertRedirect(route('platillos.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseMissing('platillos', ['id' => $platillo->id]);
        Storage::disk('public')->assertMissing($image);
    }

    public function test_cannot_delete_dish_with_associated_orders(): void
    {
        $platillo = Platillo::factory()->create(['nombre' => 'Platillo en Pedido']);
        $pedido = Pedido::create([
            'codigo' => 'PED-TEST-001',
            'cliente' => 'Cliente Prueba',
            'mesa' => 5,
            'fecha' => now(),
            'estado' => 'pendiente',
            'total' => 25.00,
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'platillo_id' => $platillo->id,
            'cantidad' => 2,
            'precio_unitario' => 12.50,
            'subtotal' => 25.00,
        ]);

        $response = $this->delete(route('platillos.destroy', $platillo));

        $response->assertRedirect(route('platillos.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('platillos', ['id' => $platillo->id]);
    }

    public function test_search_and_category_filter(): void
    {
        $categoriaBebidas = Categoria::where('nombre', 'Bebidas')->first();
        $categoriaEntradas = Categoria::where('nombre', 'Entradas')->first();

        $jugo = Platillo::factory()->create([
            'nombre' => 'Jugo Natural de Naranja',
            'categoria_id' => $categoriaBebidas->id,
        ]);

        $nachos = Platillo::factory()->create([
            'nombre' => 'Nachos Supremos',
            'categoria_id' => $categoriaEntradas->id,
        ]);

        // Busqueda por texto
        $resBusqueda = $this->get(route('platillos.index', ['buscar' => 'Naranja']));
        $resBusqueda->assertOk()
            ->assertSee('Jugo Natural de Naranja')
            ->assertDontSee('Nachos Supremos');

        // Filtro por categoria
        $resCat = $this->get(route('platillos.index', ['categoria_id' => $categoriaEntradas->id]));
        $resCat->assertOk()
            ->assertSee('Nachos Supremos')
            ->assertDontSee('Jugo Natural de Naranja');
    }
}
