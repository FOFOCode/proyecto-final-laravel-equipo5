@extends('layouts.app')

@section('titulo', 'Menú del Restaurante')

@section('contenido')
    <header class="page-header">
        <div>
            <h1 class="page-title">Menú del Restaurante</h1>
            <p class="page-subtitle">Explora, gestiona y administra los platillos ofrecidos a nuestros clientes.</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-info">{{ $platillos->total() }} platillos</span>
            <a href="{{ route('platillos.create') }}" class="btn btn-primary">
                <span>+</span> Registrar Nuevo Platillo
            </a>
        </div>
    </header>

    {{-- [CRUD-READ] Barra de busqueda y filtros --}}
    <form method="GET" action="{{ route('platillos.index') }}" class="filter-card">
        <div class="filter-grid">
            <div class="filter-group">
                <label for="buscar" class="filter-label">Buscar platillo</label>
                <input 
                    type="text" 
                    id="buscar" 
                    name="buscar" 
                    value="{{ request('buscar') }}" 
                    placeholder="Ej. Hamburguesa, Ceviche, Pasta..." 
                    class="form-control"
                >
            </div>
            <div class="filter-group">
                <label for="categoria_id" class="filter-label">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="disponible" class="filter-label">Disponibilidad</label>
                <select id="disponible" name="disponible" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ request('disponible') === '1' ? 'selected' : '' }}>Solo Disponibles</option>
                    <option value="0" {{ request('disponible') === '0' ? 'selected' : '' }}>Solo Agotados</option>
                </select>
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">
                    🔍 Filtrar
                </button>
                @if (request()->hasAny(['buscar', 'categoria_id', 'disponible']))
                    <a href="{{ route('platillos.index') }}" class="btn btn-secondary">
                        ✕ Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Grid de Platillos --}}
    <div class="dish-grid">
        @forelse ($platillos as $platillo)
            <article class="dish-card">
                <div class="dish-card-img-wrapper">
                    <img 
                        class="dish-card-img" 
                        src="{{ $platillo->imagen ? asset('storage/'.$platillo->imagen) : asset('images/default-platillo.svg') }}" 
                        alt="{{ $platillo->nombre }}" 
                        loading="lazy"
                    >
                </div>
                <div class="dish-card-body">
                    <p class="dish-category">{{ $platillo->categoria->nombre }}</p>
                    <div class="dish-card-header">
                        <h2 class="dish-card-title">{{ $platillo->nombre }}</h2>
                        <span class="dish-card-price">${{ number_format($platillo->precio, 2) }}</span>
                    </div>
                    <p class="dish-card-desc">{{ Str::limit($platillo->descripcion, 90) }}</p>
                    <div class="dish-card-footer">
                        <span>⏱ {{ $platillo->tiempo_preparacion }} min</span>
                        <span class="badge {{ $platillo->disponible ? 'badge-success' : 'badge-danger' }}">
                            {{ $platillo->disponible ? 'Disponible' : 'Agotado' }}
                        </span>
                    </div>

                    {{-- Acciones del CRUD (Ver, Editar, Eliminar) --}}
                    <div class="card-actions">
                        <a class="btn btn-sm btn-info" href="{{ route('platillos.show', $platillo) }}" title="Ver ficha técnica">
                            👁 Ver Detalle
                        </a>
                        <a class="btn btn-sm btn-secondary" href="{{ route('platillos.edit', $platillo) }}" title="Editar platillo">
                            ✎ Editar
                        </a>
                        {{-- [CRUD-DELETE] [SEGURIDAD] Eliminacion segura mediante peticion DELETE, nunca enlace GET --}}
                        <form 
                            action="{{ route('platillos.destroy', $platillo) }}" 
                            method="POST" 
                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar este platillo? Esta acción no se puede deshacer.');"
                            class="delete-form"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar del menú">
                                🗑 Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>No hay platillos registrados o coincidentes con la búsqueda.</p>
                <a href="{{ route('platillos.index') }}" class="btn btn-primary" style="margin-top: 1rem;">
                    Restablecer Menú
                </a>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 2rem;">
        {{ $platillos->links('pagination.menu') }}
    </div>
@endsection
