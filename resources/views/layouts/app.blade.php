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
            <a class="navbar-brand" href="{{ url('/') }}"><span aria-hidden="true">◈</span> Restaurante <span>Equipo 5</span></a>
            <ul class="nav-links">
                <li><a class="nav-link" href="{{ url('/') }}">Inicio</a></li>
                <li><a class="nav-link active" href="{{ route('platillos.index') }}">Menú de Platillos</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-container">
        @yield('contenido')
    </main>
    <footer class="footer">
        <div class="footer-content">
            <p>Restaurante Equipo 5 · UNICAES</p>
            <p>Diego · Miguel · Kendel · Rodolfo</p>
        </div>
    </footer>
</body>
</html>
