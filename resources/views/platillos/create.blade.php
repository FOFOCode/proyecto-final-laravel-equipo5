@extends('layouts.app')

@section('titulo', 'Registrar Nuevo Platillo')

@section('contenido')
    <div class="form-container">
        <header class="page-header">
            <div>
                <a href="{{ route('platillos.index') }}" class="btn btn-secondary" style="margin-bottom: 0.5rem;">
                    ← Volver al Menú
                </a>
                <h1 class="page-title">Registrar Nuevo Platillo</h1>
                <p class="page-subtitle">Ingresa la información detallada para incorporar un nuevo platillo a la carta.</p>
            </div>
        </header>

        {{-- [SEGURIDAD] Formulario con proteccion CSRF y codificacion multipart para archivos --}}
        <form action="{{ route('platillos.store') }}" method="POST" enctype="multipart/form-data" class="app-form">
            @csrf

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
                        value="{{ old('nombre') }}" 
                        placeholder="Ej. Salmón a las Finas Hierbas" 
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
                        <option value="">-- Selecciona una categoría --</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
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
                        min="0.01" 
                        value="{{ old('precio') }}" 
                        placeholder="Ej. 12.50" 
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
                        value="{{ old('tiempo_preparacion', 20) }}" 
                        placeholder="Ej. 25" 
                        class="form-control @error('tiempo_preparacion') is-invalid @enderror"
                        required
                    >
                    <small class="field-hint">Regla: entre 1 y 180 minutos</small>
                    @error('tiempo_preparacion')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fotografia --}}
                <div class="form-group">
                    <label for="imagen" class="form-label">
                        Fotografía del platillo (opcional)
                    </label>
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

                {{-- Descripcion --}}
                <div class="form-group full-width">
                    <label for="descripcion" class="form-label">
                        Descripción detallada e ingredientes <span class="required">*</span>
                    </label>
                    <textarea 
                        id="descripcion" 
                        name="descripcion" 
                        rows="4" 
                        placeholder="Describe los ingredientes, la cocción y acompañamientos del platillo..." 
                        class="form-control @error('descripcion') is-invalid @enderror"
                        required
                    >{{ old('descripcion') }}</textarea>
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
                            {{ old('disponible', true) ? 'checked' : '' }}
                        >
                        <span class="checkbox-label">
                            <strong>Disponible de inmediato para pedidos</strong> (si se desmarca, aparecerá como agotado en la carta).
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Guardar Platillo en el Menú
                </button>
                <a href="{{ route('platillos.index') }}" class="btn btn-secondary btn-lg">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
