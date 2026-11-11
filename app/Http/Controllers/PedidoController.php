<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Http\Request;
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
