@extends('layouts.app')

@section('titulo', 'Menú del Restaurante')

@section('contenido')
    <header class="page-header">
        <div>
            <h1 class="page-title">Menú del Restaurante</h1>
            <p class="page-subtitle">Conoce nuestros sabores y encuentra tu próximo favorito.</p>
        </div>
        <span class="badge badge-info">{{ $platillos->total() }} platillos</span>
    </header>
    <div class="dish-grid">
        @forelse ($platillos as $platillo)
            <article class="dish-card">
                <div class="dish-card-img-wrapper">
                    <img class="dish-card-img" src="{{ $platillo->imagen ? asset('storage/'.$platillo->imagen) : asset('images/default-platillo.svg') }}" alt="{{ $platillo->nombre }}" loading="lazy">
                </div>
                <div class="dish-card-body">
                    <p class="page-subtitle">{{ $platillo->categoria->nombre }}</p>
                    <div class="dish-card-header">
                        <h2 class="dish-card-title">{{ $platillo->nombre }}</h2>
                        <span class="dish-card-price">${{ number_format($platillo->precio, 2) }}</span>
                    </div>
                    <p class="dish-card-desc">{{ $platillo->descripcion }}</p>
                    <div class="dish-card-footer">
                        <span>{{ $platillo->tiempo_preparacion }} min</span>
                        <span class="badge {{ $platillo->disponible ? 'badge-success' : 'badge-danger' }}">{{ $platillo->disponible ? 'Disponible' : 'Agotado' }}</span>
                    </div>
                    <a class="btn btn-primary dish-detail-link" href="{{ route('platillos.show', $platillo) }}" aria-label="Ver detalle de {{ $platillo->nombre }}">Ver Detalle →</a>
                </div>
            </article>
        @empty
            <p class="empty-state">No hay platillos para mostrar en esta página. <a href="{{ route('platillos.index') }}">Volver al menú</a></p>
        @endforelse
    </div>
    {{ $platillos->links('pagination.menu') }}
@endsection
