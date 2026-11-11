<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function create(): View
    {
        $platillos = Platillo::with('categoria')
            ->where('disponible', true)
            ->orderBy('nombre')
            ->get();

        return view('pedidos.create', compact('platillos'));
    }

    public function store(StorePedidoRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $pedido = DB::transaction(function () use ($datos): Pedido {
            $lineas = collect($datos['platillos']);
            $platillos = Platillo::query()
                ->whereIn('id', $lineas->pluck('platillo_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($platillos->count() !== $lineas->count()) {
                throw ValidationException::withMessages([
                    'platillos' => 'Uno de los platillos seleccionados ya no está disponible.',
                ]);
            }

            $totalCentavos = 0;
            $pedido = Pedido::create([
                'codigo' => 'PED-'.now()->format('ymd').'-'.Str::upper(Str::random(9)),
                'cliente' => $datos['cliente'],
                'mesa' => $datos['mesa'],
                'fecha' => now(),
                'estado' => 'pendiente',
                'total' => 0,
                'notas' => $datos['notas'] ?? null,
            ]);

            foreach ($lineas as $indice => $linea) {
                $platillo = $platillos->get($linea['platillo_id']);

                if (! $platillo->disponible) {
                    throw ValidationException::withMessages([
                        "platillos.{$indice}.platillo_id" => "El platillo {$platillo->nombre} ya no está disponible.",
                    ]);
                }

                $precioCentavos = (int) round((float) $platillo->precio * 100);
                $subtotalCentavos = $precioCentavos * $linea['cantidad'];
                $totalCentavos += $subtotalCentavos;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'platillo_id' => $platillo->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => number_format($precioCentavos / 100, 2, '.', ''),
                    'subtotal' => number_format($subtotalCentavos / 100, 2, '.', ''),
                ]);
            }

            $pedido->update(['total' => number_format($totalCentavos / 100, 2, '.', '')]);

            return $pedido;
        });

        return redirect()->route('pedidos.index')
            ->with('exito', 'Pedido registrado correctamente.');
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'estado' => ['nullable', 'in:'.implode(',', Pedido::ESTADOS)],
        ]);

        $pedidos = Pedido::with(['platillos.categoria', 'detalles.platillo.categoria'])
            ->when($validated['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->latest('fecha')
            ->paginate(10)
            ->withQueryString();

        return view('pedidos.index', compact('pedidos'));
    }
}
