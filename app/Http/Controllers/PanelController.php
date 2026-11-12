<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $inicioHoy = now(config('app.display_timezone'))->startOfDay()->utc();
        $finHoy = $inicioHoy->copy()->addDay();
        $pedidosDelDia = Pedido::where('fecha', '>=', $inicioHoy)->where('fecha', '<', $finHoy);
        $ventasHoy = (clone $pedidosDelDia)
            ->where('estado', '!=', 'cancelado')
            ->sum('total');
        $pedidosHoy = (clone $pedidosDelDia)->count();
        $pedidosEnPreparacion = Pedido::where('estado', 'en_preparacion')->count();
        $pedidosAtendidos = Pedido::whereIn('estado', ['servido', 'pagado'])->count();
        $pedidosPorEstado = Pedido::query()
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado');
        $totalPlatillos = Platillo::count();

        $platillosMasVendidos = Platillo::with('categoria')
            ->withSum([
                'detallePedidos as total_vendido' => fn (Builder $query) => $query->whereHas(
                    'pedido',
                    fn (Builder $pedidoQuery) => $pedidoQuery->where('estado', '!=', 'cancelado'),
                ),
            ], 'cantidad')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();

        $ventasPorCategoria = Categoria::with([
            'platillos.detallePedidos' => fn (HasMany $query) => $query->whereHas(
                'pedido',
                fn (Builder $pedidoQuery) => $pedidoQuery->where('estado', '!=', 'cancelado'),
            ),
        ])->get()->map(fn (Categoria $categoria) => [
            'nombre' => $categoria->nombre,
            'total' => $categoria->platillos->flatMap->detallePedidos->sum('subtotal'),
        ]);

        $ultimosPedidos = Pedido::with('detalles.platillo')
            ->latest('fecha')
            ->take(6)
            ->get();

        return view('panel.index', compact(
            'ventasHoy',
            'pedidosHoy',
            'pedidosEnPreparacion',
            'pedidosAtendidos',
            'pedidosPorEstado',
            'totalPlatillos',
            'platillosMasVendidos',
            'ventasPorCategoria',
            'ultimosPedidos',
        ));
    }
}
