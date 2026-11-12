@extends('layouts.app')

@section('titulo', 'Pedidos')

@section('contenido')
    <header class="page-header">
        <div>
            <h1 class="page-title">Pedidos</h1>
            <p class="page-subtitle">Consulta los pedidos y actualiza su estado.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('pedidos.create') }}">+ Registrar pedido</a>
    </header>

    <form method="GET" action="{{ route('pedidos.index') }}" class="filter-card">
        <div class="filter-grid order-filter-grid">
            <div class="filter-group">
                <label class="filter-label" for="estado">Filtrar por estado</label>
                <select class="form-control" id="estado" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach (['pendiente' => 'Pendiente', 'en_preparacion' => 'En preparación', 'servido' => 'Servido', 'pagado' => 'Pagado', 'cancelado' => 'Cancelado'] as $estado => $etiqueta)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-buttons">
                <button class="btn btn-primary" type="submit">Filtrar</button>
                @if (request()->filled('estado'))
                    <a class="btn btn-secondary" href="{{ route('pedidos.index') }}">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Mesa</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Fecha y hora</th>
                    <th scope="col">Total</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    @php
                        $etiquetasEstado = [
                            'pendiente' => 'Pendiente',
                            'en_preparacion' => 'En preparación',
                            'servido' => 'Servido',
                            'pagado' => 'Pagado',
                            'cancelado' => 'Cancelado',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $pedido->codigo }}</td>
                        <td>{{ $pedido->mesa }}</td>
                        <td>{{ $pedido->cliente }}</td>
                        <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                        <td>${{ number_format($pedido->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $pedido->estado === 'cancelado' ? 'badge-danger' : ($pedido->estado === 'pagado' ? 'badge-success' : 'badge-warning') }}">
                                {{ $etiquetasEstado[$pedido->estado] }}
                            </span>
                        </td>
                        <td>
                            <div class="order-actions">
                                <a class="btn btn-secondary" href="{{ route('pedidos.show', $pedido) }}">Ver ticket</a>
                                <form action="{{ route('pedidos.updateEstado', $pedido) }}" method="POST" class="order-status-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="visually-hidden" for="estado-{{ $pedido->id }}">Estado de {{ $pedido->codigo }}</label>
                                    <select class="form-control" id="estado-{{ $pedido->id }}" name="estado">
                                        @foreach ($etiquetasEstado as $estado => $etiqueta)
                                            <option value="{{ $estado }}" @selected($pedido->estado === $estado)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary" type="submit">Guardar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">No hay pedidos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pedidos->links() }}
@endsection
