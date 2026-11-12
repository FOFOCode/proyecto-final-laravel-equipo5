@extends('layouts.app')

@section('titulo', 'Editar Platillo: ' . $platillo->nombre)

@section('contenido')
    <div class="form-container">
        <header class="page-header">
            <div>
                <a href="{{ route('platillos.show', $platillo) }}" class="btn btn-secondary" style="margin-bottom: 0.5rem;">
                    ← Volver a la Ficha del Platillo
                </a>
                <h1 class="page-title">Editar Platillo: {{ $platillo->nombre }}</h1>
                <p class="page-subtitle">Modifica los detalles, precios, disponibilidad o fotografía del platillo.</p>
            </div>
        </header>

        {{-- [SEGURIDAD] Metodo spoofing PUT y proteccion CSRF --}}
        <form action="{{ route('platillos.update', $platillo) }}" method="POST" enctype="multipart/form-data" class="app-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                {{-- Nombre --}}
                <div class="form-group full-width">
                    <label for="nombre" class="form-label">
                        Nombre del platillo <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        value="{{ old('nombre', $platillo->nombre) }}" 
                        class="form-control @error('nombre') is-invalid @enderror"
                        required
                    >
                    @error('nombre')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Categoria --}}
                <div class="form-group">
                    <label for="categoria_id" class="form-label">
                        Categoría del menú <span class="required">*</span>
                    </label>
                    <select 
                        id="categoria_id" 
                        name="categoria_id" 
                        class="form-control @error('categoria_id') is-invalid @enderror"
                        required
                    >
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id', $platillo->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio --}}
                <div class="form-group">
                    <label for="precio" class="form-label">
                        Precio en USD ($) <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="precio" 
                        name="precio" 
                        step="0.01" 
                        min="0.01" max="999999.99"
                        value="{{ old('precio', $platillo->precio) }}" 
                        class="form-control @error('precio') is-invalid @enderror"
                        required
                    >
                    <small class="field-hint">Regla: debe ser mayor a 0.00</small>
                    @error('precio')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tiempo de Preparacion --}}
                <div class="form-group">
                    <label for="tiempo_preparacion" class="form-label">
                        Tiempo de preparación (minutos) <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="tiempo_preparacion" 
                        name="tiempo_preparacion" 
                        min="1" 
                        max="180" 
                        value="{{ old('tiempo_preparacion', $platillo->tiempo_preparacion) }}" 
                        class="form-control @error('tiempo_preparacion') is-invalid @enderror"
                        required
                    >
                    <small class="field-hint">Regla: entre 1 y 180 minutos</small>
                    @error('tiempo_preparacion')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fotografia actual y reemplazo --}}
                <div class="form-group full-width">
                    <label for="imagen" class="form-label">
                        Fotografía del platillo
                    </label>
                    <div class="current-image-preview">
                        <div class="preview-thumbnail">
                            <img 
                                src="{{ $platillo->imagen ? asset('storage/'.$platillo->imagen) : asset('images/default-platillo.svg') }}" 
                                alt="{{ $platillo->nombre }}"
                            >
                        </div>
                        <div class="preview-info">
                            <p><strong>Fotografía actual:</strong> {{ $platillo->imagen ? basename($platillo->imagen) : 'Imagen por defecto del sistema' }}</p>
                            <p class="text-muted" style="margin-bottom: 0.5rem;">Para reemplazar la fotografía existente, selecciona un nuevo archivo a continuación:</p>
                            <input 
                                type="file" 
                                id="imagen" 
                                name="imagen" 
                                accept="image/jpeg,image/png,image/webp" 
                                class="form-control @error('imagen') is-invalid @enderror"
                            >
                            <small class="field-hint">Formatos: JPG, PNG o WEBP. Máximo 2 MB.</small>
                            @error('imagen')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Descripcion --}}
                <div class="form-group full-width">
                    <label for="descripcion" class="form-label">
                        Descripción detallada e ingredientes <span class="required">*</span>
                    </label>
                    <textarea 
                        id="descripcion" 
                        name="descripcion" 
                        rows="4" 
                        class="form-control @error('descripcion') is-invalid @enderror"
                        required
                    >{{ old('descripcion', $platillo->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Disponibilidad --}}
                <div class="form-group full-width">
                    <label class="checkbox-container">
                        <input 
                            type="checkbox" 
                            name="disponible" 
                            value="1" 
                            {{ old('disponible', $platillo->disponible) ? 'checked' : '' }}
                        >
                        <span class="checkbox-label">
                            <strong>Disponible para pedidos</strong> (desmarcar para dejar en estado agotado).
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    🔄 Actualizar Platillo
                </button>
                <a href="{{ route('platillos.show', $platillo) }}" class="btn btn-secondary btn-lg">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
