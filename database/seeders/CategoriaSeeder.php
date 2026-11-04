<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Entradas' => 'Aperitivos y bocadillos para comenzar',
            'Platos fuertes' => 'Especialidades culinarias y platos principales',
            'Bebidas' => 'Bebidas frías, calientes, naturales y refrescos',
            'Postres' => 'Dulces, pasteles y delicias tradicionales',
        ];

        foreach ($categorias as $nombre => $descripcion) {
            Categoria::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'activo' => true],
            );
        }
    }
}
