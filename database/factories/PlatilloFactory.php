<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Platillo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Platillo> */
class PlatilloFactory extends Factory
{
    public function definition(): array
    {
        $menu = [
            ['Bruschettas de Tomate', 'Entradas', 'Pan tostado con tomate fresco, albahaca y aceite de oliva.'],
            ['Nachos con Queso', 'Entradas', 'Totopos crujientes con queso fundido y pico de gallo.'],
            ['Ceviche Mixto', 'Entradas', 'Pescado y camarones marinados en limón con cilantro.'],
            ['Alitas de Pollo', 'Entradas', 'Alitas doradas con salsa picante de la casa.'],
            ['Crema de Calabaza', 'Entradas', 'Crema suave de calabaza acompañada de pan tostado.'],
            ['Ensalada Caprese', 'Entradas', 'Tomate, mozzarella y albahaca con aceite de oliva.'],
            ['Lasaña Boloñesa Tradicional', 'Platos fuertes', 'Capas de pasta con carne, salsa de tomate y queso gratinado.'],
            ['Hamburguesa Gourmet', 'Platos fuertes', 'Carne a la parrilla con queso, vegetales frescos y papas.'],
            ['Tacos de Birria', 'Platos fuertes', 'Tortillas rellenas de carne de res con consomé.'],
            ['Sopa de Mariscos', 'Platos fuertes', 'Caldo de pescado con camarones, mariscos y vegetales.'],
            ['Salmón a las Finas Hierbas', 'Platos fuertes', 'Filete de salmón con hierbas aromáticas y vegetales.'],
            ['Costillas BBQ', 'Platos fuertes', 'Costillas de cerdo con salsa barbacoa y papas asadas.'],
            ['Limonada Natural', 'Bebidas', 'Jugo de limón recién exprimido con agua y hielo.'],
            ['Horchata de Morro', 'Bebidas', 'Bebida tradicional de semillas de morro con canela.'],
            ['Jugo de Naranja', 'Bebidas', 'Naranjas recién exprimidas, servido frío.'],
            ['Batido de Fresa', 'Bebidas', 'Fresas licuadas con leche y hielo.'],
            ['Café con Leche', 'Bebidas', 'Café recién preparado acompañado de leche caliente.'],
            ['Té Helado de Durazno', 'Bebidas', 'Infusión de té con durazno, servida con hielo.'],
            ['Flan de Caramelo', 'Postres', 'Flan de leche y huevo cubierto con caramelo.'],
            ['Pastel de Chocolate', 'Postres', 'Bizcocho de chocolate con cobertura cremosa de cacao.'],
            ['Tres Leches', 'Postres', 'Bizcocho bañado en tres leches con crema batida.'],
            ['Cheesecake de Fresa', 'Postres', 'Tarta de queso sobre base de galleta con salsa de fresa.'],
            ['Arroz con Leche', 'Postres', 'Arroz cocido en leche con canela y vainilla.'],
            ['Helado de Vainilla', 'Postres', 'Helado cremoso de vainilla con salsa de chocolate.'],
        ];

        // El catalogo permite generar hasta 24 nombres distintos por lote.
        [$nombre, $categoria, $descripcion] = $menu[fake()->unique()->numberBetween(0, count($menu) - 1)];

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'categoria_id' => Categoria::where('nombre', $categoria)->firstOrFail()->id,
            'precio' => fake()->randomFloat(2, 3.50, 25.00),
            'tiempo_preparacion' => fake()->numberBetween(10, 45),
            'disponible' => fake()->boolean(90),
            'imagen' => null,
        ];
    }
}
