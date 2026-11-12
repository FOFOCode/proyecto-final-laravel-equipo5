<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        DB::transaction(function () use ($datos): Pedido {
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

                // [REGLA-NEGOCIO] Disponibilidad verificada bajo bloqueo en la transaccion.
                if (! $platillo->disponible) {
                    throw ValidationException::withMessages([
                        "platillos.{$indice}.platillo_id" => "El platillo {$platillo->nombre} ya no está disponible.",
                    ]);
                }

                // [REGLA-NEGOCIO] Precios y totales del servidor calculados en centavos.
                $precioCentavos = (int) round((float) $platillo->precio * 100);
                $subtotalCentavos = $precioCentavos * $linea['cantidad'];
                $totalCentavos += $subtotalCentavos;

                if ($totalCentavos > 9999999999) {
                    throw ValidationException::withMessages([
                        'platillos' => 'El total del pedido no puede superar $99,999,999.99.',
                    ]);
                }

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

    public function show(Pedido $pedido): View
    {
        $pedido->load(['detalles.platillo.categoria']);

        return view('pedidos.show', compact('pedido'));
    }

    public function updateEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $validated = $request->validate(
            ['estado' => ['required', Rule::in(Pedido::ESTADOS)]],
            [
                'estado.required' => 'Seleccione el nuevo estado del pedido.',
                'estado.in' => 'El estado seleccionado no es válido.',
            ],
        );

        $pedido->update(['estado' => $validated['estado']]);

        return back()->with('exito', 'Estado del pedido actualizado.');
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'estado' => ['nullable', 'in:'.implode(',', Pedido::ESTADOS)],
        ], ['estado.in' => 'El estado seleccionado no es válido.']);

        $pedidos = Pedido::with(['platillos.categoria', 'detalles.platillo.categoria'])
            ->when($validated['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->latest('fecha')
            ->paginate(10)
            ->withQueryString();

        return view('pedidos.index', compact('pedidos'));
    }
}
