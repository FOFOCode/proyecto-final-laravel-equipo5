@extends('layouts.app')

@section('titulo', 'Panel y Reportes')

@section('contenido')
    <div class="page-header">
        <div>
            <h1 class="page-title">Panel de control</h1>
            <p class="page-subtitle">Resumen de ventas y actividad del restaurante.</p>
        </div>
    </div>

    <section class="dashboard-grid" aria-label="Indicadores del restaurante">
        <article class="dashboard-card">
            <span class="dashboard-label">Ventas de hoy</span>
            <strong class="dashboard-value">${{ number_format($ventasHoy, 2) }}</strong>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-label">Pedidos atendidos</span>
            <strong class="dashboard-value">{{ $pedidosAtendidos }}</strong>
            <span class="dashboard-hint">{{ $pedidosHoy }} pedidos registrados hoy</span>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-label">En preparación</span>
            <strong class="dashboard-value">{{ $pedidosEnPreparacion }}</strong>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-label">Platillos en catálogo</span>
            <strong class="dashboard-value">{{ $totalPlatillos }}</strong>
        </article>
    </section>

    <section class="dashboard-section">
        <h2 class="section-title">Pedidos por estado</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Estado</th>
                        <th scope="col">Pedidos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['pendiente' => 'Pendiente', 'en_preparacion' => 'En preparación', 'servido' => 'Servido', 'pagado' => 'Pagado', 'cancelado' => 'Cancelado'] as $estado => $etiqueta)
                        <tr>
                            <td>{{ $etiqueta }}</td>
                            <td>{{ $pedidosPorEstado->get($estado, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="dashboard-section">
        <h2 class="section-title">Platillos más vendidos</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Platillo</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Unidades vendidas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($platillosMasVendidos as $platillo)
                        <tr>
                            <td>{{ $platillo->nombre }}</td>
                            <td>{{ $platillo->categoria->nombre }}</td>
                            <td>{{ $platillo->total_vendido ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">Aún no hay platillos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="dashboard-section">
        <h2 class="section-title">Ventas por categoría</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Categoría</th>
                        <th scope="col">Ventas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventasPorCategoria as $categoria)
                        <tr>
                            <td>{{ $categoria['nombre'] }}</td>
                            <td>${{ number_format($categoria['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="dashboard-section">
        <h2 class="section-title">Últimos pedidos</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Mesa</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Total</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Ticket</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimosPedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->cliente }}</td>
                            <td>{{ $pedido->mesa }}</td>
                            <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format($pedido->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $pedido->estado === 'cancelado' ? 'badge-danger' : ($pedido->estado === 'pagado' ? 'badge-success' : 'badge-warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                </span>
                            </td>
                            <td>
                                @if (Route::has('pedidos.show'))
                                    <a class="btn btn-secondary" href="{{ route('pedidos.show', $pedido) }}">Ver ticket</a>
                                @else
                                    <span class="dashboard-hint">Módulo de pedidos pendiente de integrar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">Aún no hay pedidos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
