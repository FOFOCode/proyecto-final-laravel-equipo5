@extends('layouts.app')

@section('titulo', $platillo->nombre)

@section('contenido')
    <div class="page-header">
        <a class="btn btn-secondary" href="{{ route('platillos.index') }}">← Volver al Menú</a>
    </div>
    <article class="detail-card">
        <img class="detail-img" src="{{ $platillo->imagen ? asset('storage/'.$platillo->imagen) : asset('images/default-platillo.svg') }}" alt="{{ $platillo->nombre }}">
        <div class="detail-content">
            <p class="page-subtitle">{{ $platillo->categoria->nombre }}</p>
            <h1 class="page-title">{{ $platillo->nombre }}</h1>
            <p class="dish-card-price">${{ number_format($platillo->precio, 2) }}</p>
            <p><span class="badge {{ $platillo->disponible ? 'badge-success' : 'badge-danger' }}">{{ $platillo->disponible ? 'Disponible' : 'Agotado' }}</span></p>
            <p><strong>Tiempo de preparación:</strong> {{ $platillo->tiempo_preparacion }} min</p>
            <div>
                <h2 class="dish-card-title">Descripción</h2>
                <p class="dish-description">{{ $platillo->descripcion }}</p>
            </div>
            <p class="page-subtitle">Los pedidos relacionados estarán disponibles en la Fase 2.</p>
        </div>
    </article>
@endsection
