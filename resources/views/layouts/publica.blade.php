<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ESFAP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body class="bg-light">
    <div class="public-top">
        <div class="container">
            <a href="{{ url('/') }}" class="text-white text-decoration-none d-flex align-items-center gap-2">
                <img src="{{ asset('imagenes/logo_de_institucion.png') }}" width="38" height="38" class="rounded-2" alt="Escudo">
                <div>
                    <div class="fw-bold lh-1">ESFAP</div>
                    <div class="small" style="color:#e9d5f3">Carnet de identificación institucional</div>
                </div>
            </a>
        </div>
    </div>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="text-center text-muted py-3 small">
        Escuela Superior de Formación Artística Pública &middot; Verificación de carnets
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.historial')
    @yield('scripts')
</body>
</html>