<?php

namespace Tests\Feature;

use App\Models\Platillo;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatilloReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_paginates_twenty_dishes_in_pages_of_eight(): void
    {
        $this->seed();
        $dishes = Platillo::orderBy('id')->get();

        foreach ([1 => 8, 2 => 8, 3 => 4] as $page => $count) {
            $response = $this->get('/platillos?page='.$page)->assertOk()
                ->assertSee('Página '.$page.' de 3')
                ->assertViewHas('platillos', fn ($items) => $items->count() === $count && $items->total() === 20);

            foreach ($dishes->slice(($page - 1) * 8, $count) as $dish) {
                $response->assertSee($dish->nombre)->assertSee($dish->categoria->nombre)
                    ->assertSee(route('platillos.show', $dish));
            }
        }

        $this->get('/platillos')->assertDontSee($dishes[8]->nombre);
    }

    public function test_empty_menu_and_page_beyond_last_have_an_empty_message(): void
    {
        $this->get('/platillos')->assertOk()->assertSee('No hay platillos');
        $this->seed();
        $this->get('/platillos?page=99')->assertOk()->assertSee('No hay platillos');
    }

    public function test_detail_displays_fields_fallback_image_and_availability(): void
    {
        $this->seed(CategoriaSeeder::class);
        $dish = Platillo::factory()->create(['precio' => 12.50, 'tiempo_preparacion' => 25, 'disponible' => false]);

        $this->get(route('platillos.show', $dish))->assertOk()
            ->assertSee($dish->nombre)->assertSee($dish->descripcion)
            ->assertSee($dish->categoria->nombre)->assertSee('$12.50')
            ->assertSee('25 min')->assertSee('Agotado')
            ->assertSee(asset('images/default-platillo.svg'))
            ->assertSee('Volver al Menú')->assertSee('Fase 2');

        $dish->update(['disponible' => true, 'imagen' => 'platillos/prueba.jpg']);
        foreach (['/platillos', route('platillos.show', $dish)] as $url) {
            $this->get($url)->assertOk()->assertSee('Disponible')
                ->assertSee(asset('storage/platillos/prueba.jpg'));
        }
    }

    public function test_unknown_dish_returns_404(): void
    {
        $this->get('/platillos/999999')->assertNotFound();
    }

    public function test_menu_and_detail_escape_untrusted_content(): void
    {
        $this->seed(CategoriaSeeder::class);
        $dish = Platillo::factory()->create(['descripcion' => '<script>alert(1)</script>']);

        foreach (['/platillos', route('platillos.show', $dish)] as $url) {
            $this->get($url)->assertOk()->assertSee('&lt;script&gt;', false)
                ->assertDontSee('<script>alert(1)</script>', false);
        }
    }
}
