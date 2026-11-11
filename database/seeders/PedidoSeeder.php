<?php

namespace Database\Seeders;

use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $platillos = Platillo::orderBy('id')->get();

        if ($platillos->count() < 4) {
            throw new RuntimeException('Se necesitan al menos cuatro platillos para generar pedidos de prueba.');
        }

        $muestras = [
            ['Ana Morales', 3, 0, 'pagado', [0 => 2, 12 => 1]],
            ['Carlos Pérez', 5, 0, 'servido', [6 => 1, 13 => 2, 18 => 1]],
            ['María López', 2, 0, 'en_preparacion', [1 => 2, 7 => 1]],
            ['José Hernández', 8, 1, 'pagado', [2 => 1, 14 => 2, 19 => 1]],
            ['Lucía Martínez', 1, 1, 'cancelado', [8 => 1, 15 => 1]],
            ['Pedro García', 4, 1, 'servido', [3 => 2, 9 => 1, 16 => 1]],
            ['Sofía Ramírez', 6, 2, 'pagado', [4 => 1, 10 => 2]],
            ['Miguel Torres', 7, 2, 'pendiente', [5 => 1, 11 => 1, 17 => 2]],
            ['Elena Cruz', 9, 3, 'pagado', [0 => 1, 6 => 1, 18 => 2]],
            ['Andrés Flores', 10, 4, 'cancelado', [2 => 2, 13 => 1]],
            ['Valeria Díaz', 2, 5, 'servido', [7 => 1, 14 => 1, 19 => 2]],
            ['Daniel Ruiz', 5, 6, 'pagado', [1 => 1, 8 => 1, 12 => 2, 17 => 1]],
        ];

        foreach ($muestras as $indice => [$cliente, $mesa, $dias, $estado, $lineas]) {
            $pedido = Pedido::updateOrCreate(
                ['codigo' => sprintf('PED-DEMO-%03d', $indice + 1)],
                [
                    'cliente' => $cliente,
                    'mesa' => $mesa,
                    'fecha' => Carbon::today()->subDays($dias)->setTime(12, $indice),
                    'estado' => $estado,
                    'total' => 0,
                    'notas' => null,
                ],
            );

            $detalles = [];
            $totalCentavos = 0;

            foreach ($lineas as $platilloIndice => $cantidad) {
                $platillo = $platillos[$platilloIndice % $platillos->count()];
                $precioCentavos = (int) round((float) $platillo->precio * 100);
                $subtotalCentavos = $precioCentavos * $cantidad;
                $totalCentavos += $subtotalCentavos;
                $detalles[$platillo->id] = [
                    'cantidad' => $cantidad,
                    'precio_unitario' => number_format($precioCentavos / 100, 2, '.', ''),
                    'subtotal' => number_format($subtotalCentavos / 100, 2, '.', ''),
                ];
            }

            $pedido->platillos()->sync($detalles);
            $pedido->update(['total' => number_format($totalCentavos / 100, 2, '.', '')]);
        }
    }
}
