<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
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
