@extends('layouts.app')

@section('titulo', 'Registrar pedido')

@section('contenido')
    <div class="form-container">
        <header class="page-header">
            <div>
                <a class="btn btn-secondary" href="{{ route('pedidos.index') }}">← Volver a pedidos</a>
                <h1 class="page-title">Registrar pedido</h1>
                <p class="page-subtitle">Selecciona los platillos y sus cantidades. Los precios se calculan en el servidor.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pedidos.store') }}" method="POST" class="app-form" novalidate>
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="cliente">Nombre del cliente <span class="required">*</span></label>
                    <input class="form-control @error('cliente') is-invalid @enderror" id="cliente" name="cliente" type="text" maxlength="100" value="{{ old('cliente') }}" required>
                    @error('cliente')<p class="error-message">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="mesa">Mesa <span class="required">*</span></label>
                    <input class="form-control @error('mesa') is-invalid @enderror" id="mesa" name="mesa" type="number" min="1" max="50" value="{{ old('mesa') }}" required>
                    @error('mesa')<p class="error-message">{{ $message }}</p>@enderror
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="notas">Notas o indicaciones</label>
                    <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3" maxlength="500">{{ old('notas') }}</textarea>
                    @error('notas')<p class="error-message">{{ $message }}</p>@enderror
                </div>

                <fieldset class="form-group full-width dish-selection">
                    <legend class="form-label">Platillos disponibles <span class="required">*</span></legend>
                    @error('platillos')<p class="error-message">{{ $message }}</p>@enderror
                    @error('platillos.*.platillo_id')<p class="error-message">{{ $message }}</p>@enderror
                    @if ($platillos->isEmpty())
                        <p class="empty-state">No hay platillos disponibles para agregar al pedido.</p>
                    @else
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Seleccionar</th>
                                        <th scope="col">Platillo</th>
                                        <th scope="col">Categoría</th>
                                        <th scope="col">Precio</th>
                                        <th scope="col">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($platillos as $platillo)
                                        @php
                                            $seleccionado = old('platillos.'.$platillo->id.'.platillo_id') == $platillo->id;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="platillos[{{ $platillo->id }}][platillo_id]" value="{{ $platillo->id }}" @checked($seleccionado) aria-label="Agregar {{ $platillo->nombre }}">
                                            </td>
                                            <td>{{ $platillo->nombre }}</td>
                                            <td>{{ $platillo->categoria->nombre }}</td>
                                            <td>${{ number_format($platillo->precio, 2) }}</td>
                                            <td>
                                                <label class="visually-hidden" for="cantidad-{{ $platillo->id }}">Cantidad de {{ $platillo->nombre }}</label>
                                                <input class="form-control quantity-input" id="cantidad-{{ $platillo->id }}" name="platillos[{{ $platillo->id }}][cantidad]" type="number" min="1" max="20" value="{{ old('platillos.'.$platillo->id.'.cantidad', 1) }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </fieldset>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary btn-lg" type="submit" @disabled($platillos->isEmpty())>Guardar pedido</button>
                <a class="btn btn-secondary btn-lg" href="{{ route('pedidos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
