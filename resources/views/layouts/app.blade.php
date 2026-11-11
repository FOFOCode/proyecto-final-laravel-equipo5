<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Menú') · Restaurante Equipo 5</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="navbar">
        <nav class="navbar-container" aria-label="Navegación principal">
            <a class="navbar-brand" href="{{ route('platillos.index') }}">
                <span aria-hidden="true">◈</span> Restaurante <span>Equipo 5</span>
            </a>
            <ul class="nav-links">
                <li>
                    <a class="nav-link {{ request()->routeIs('platillos.index') ? 'active' : '' }}" href="{{ route('platillos.index') }}">
                        Menú de Platillos
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ request()->routeIs('platillos.create') ? 'active' : '' }}" href="{{ route('platillos.create') }}">
                        + Nuevo Platillo
                    </a>
                </li>
                @if (Route::has('pedidos.index'))
                    <li>
                        <a class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}" href="{{ route('pedidos.index') }}">
                            Pedidos
                        </a>
                    </li>
                @endif
                @if (Route::has('panel.index'))
                    <li>
                        <a class="nav-link {{ request()->routeIs('panel.*') ? 'active' : '' }}" href="{{ route('panel.index') }}">
                            Panel y Reportes
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </header>

    <main class="main-container">
        {{-- Mensajes flash de exito y error --}}
        @if (session('exito'))
            <div class="alert alert-success" role="alert">
                <span class="alert-icon">✓</span>
                <div>{{ session('exito') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                <span class="alert-icon">✕</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('contenido')
    </main>

    <footer class="footer">
        <div class="footer-content">
            <p><strong>Restaurante Equipo 5</strong> · Caso 5: Menú y Pedidos · UNICAES</p>
            <p>Desarrollado por: Diego · Miguel · Kendel · Rodolfo Rivas</p>
        </div>
    </footer>
</body>
</html>
