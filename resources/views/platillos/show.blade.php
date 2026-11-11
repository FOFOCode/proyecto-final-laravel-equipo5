@extends('layouts.app')

@section('titulo', $platillo->nombre . ' · Ficha de Detalle')

@section('contenido')
    <div class="page-header">
        <a class="btn btn-secondary" href="{{ route('platillos.index') }}">
            ← Volver al Menú
        </a>
        <div class="header-actions">
            <a href="{{ route('platillos.edit', $platillo) }}" class="btn btn-primary">
                ✎ Editar Platillo
            </a>
            {{-- [CRUD-DELETE] [SEGURIDAD] Eliminacion por metodo DELETE con confirmacion --}}
            <form 
                action="{{ route('platillos.destroy', $platillo) }}" 
                method="POST" 
                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este platillo?');" 
                style="display: inline-block;"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑 Eliminar Platillo
                </button>
            </form>
        </div>
    </div>

    <article class="detail-card">
        <div class="detail-img-container">
            <img 
                class="detail-img" 
                src="{{ $platillo->imagen ? asset('storage/'.$platillo->imagen) : asset('images/default-platillo.svg') }}" 
                alt="{{ $platillo->nombre }}"
            >
        </div>
        <div class="detail-content">
            <span class="badge badge-info" style="margin-bottom: 0.5rem;">
                {{ $platillo->categoria->nombre }}
            </span>
            <h1 class="page-title" style="margin-bottom: 0.5rem;">{{ $platillo->nombre }}</h1>
            <p class="dish-card-price" style="font-size: 2rem; margin-bottom: 1rem;">
                ${{ number_format($platillo->precio, 2) }}
            </p>

            <div class="detail-meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Estado actual:</span>
                    <span class="badge {{ $platillo->disponible ? 'badge-success' : 'badge-danger' }}">
                        {{ $platillo->disponible ? 'Disponible' : 'Agotado' }}
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tiempo estimado:</span>
                    <strong>⏱ {{ $platillo->tiempo_preparacion }} min</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Fecha de alta:</span>
                    <span>{{ $platillo->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3 class="dish-card-title">Descripción</h3>
                <p class="dish-description" style="margin-top: 0.5rem; line-height: 1.7;">
                    {{ $platillo->descripcion }}
                </p>
            </div>

            <p class="page-subtitle" style="margin-top: 1rem;">
                Fase 2: Gestión completa de platillos, pedidos e imágenes.
            </p>
        </div>
    </article>

    {{-- Entidad relacionada: Historial de pedidos que incluyen este platillo --}}
    <section class="related-section" style="margin-top: 3rem;">
        <h2 class="section-title">Pedidos que incluyen este platillo</h2>
        <p class="page-subtitle" style="margin-bottom: 1rem;">
            Historial de pedidos en los que los clientes han ordenado {{ $platillo->nombre }}.
        </p>

        @if ($platillo->detallePedidos->isNotEmpty())
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código Pedido</th>
                            <th>Mesa</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                            <th>Estado Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($platillo->detallePedidos as $detalle)
                            <tr>
                                <td>
                                    <strong>{{ $detalle->pedido->codigo }}</strong>
                                </td>
                                <td>Mesa #{{ $detalle->pedido->mesa }}</td>
                                <td>{{ $detalle->pedido->cliente }}</td>
                                <td>{{ $detalle->pedido->fecha->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $detalle->cantidad }} u.</span>
                                </td>
                                <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td>
                                    <strong>${{ number_format($detalle->subtotal, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ ucfirst(str_replace('_', ' ', $detalle->pedido->estado)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <p>Este platillo aún no ha sido solicitado en ningún pedido registrado.</p>
            </div>
        @endif
    </section>
@endsection
