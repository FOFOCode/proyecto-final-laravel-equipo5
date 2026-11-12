@extends('layouts.app')

@section('titulo', 'Ticket '.$pedido->codigo)

@section('contenido')
    @php
        $etiquetasEstado = [
            'pendiente' => 'Pendiente',
            'en_preparacion' => 'En preparación',
            'servido' => 'Servido',
            'pagado' => 'Pagado',
            'cancelado' => 'Cancelado',
        ];
    @endphp

    <div class="page-header ticket-actions no-print">
        <a class="btn btn-secondary" href="{{ route('pedidos.index') }}">← Volver a pedidos</a>
        <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir ticket</button>
    </div>

    <article class="ticket">
        <header class="ticket-header">
            <p class="ticket-brand">Restaurante Equipo 5</p>
            <h1>Ticket de pedido</h1>
            <p class="ticket-code">{{ $pedido->codigo }}</p>
        </header>

        <dl class="ticket-details">
            <div><dt>Fecha</dt><dd>{{ $pedido->fecha->copy()->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Mesa</dt><dd>{{ $pedido->mesa }}</dd></div>
            <div><dt>Cliente</dt><dd>{{ $pedido->cliente }}</dd></div>
            <div><dt>Estado</dt><dd>{{ $etiquetasEstado[$pedido->estado] }}</dd></div>
        </dl>

        @if ($pedido->notas)
            <p class="ticket-notes"><strong>Notas:</strong> {{ $pedido->notas }}</p>
        @endif

        <div class="table-container ticket-table">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Platillo</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Cant.</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->platillo->nombre }}</td>
                            <td>{{ $detalle->platillo->categoria->nombre }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td>${{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" scope="row">Total</th>
                        <td class="ticket-total">${{ number_format($pedido->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </article>
@endsection
