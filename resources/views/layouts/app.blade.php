<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Carnets ESFAP'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-mortarboard-fill me-2"></i>Carnet ESFAP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    @if (Auth::user()->esAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('alumnos.index') }}"><i class="bi bi-people-fill me-1"></i>Lista completa</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('alumnos.create') }}"><i class="bi bi-person-plus-fill me-1"></i>Crear carnet</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('verificar') }}"><i class="bi bi-qr-code-scan me-1"></i>Verificar QR</a></li>
                    @else
                        @php $miAlumno = Auth::user()->alumno; @endphp
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-house-fill me-1"></i>Inicio</a></li>
                        @if ($miAlumno)
                            <li class="nav-item"><a class="nav-link" href="{{ route('alumnos.carnet', $miAlumno) }}"><i class="bi bi-credit-card-2-front me-1"></i>Mi carnet</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('alumnos.edit', $miAlumno) }}"><i class="bi bi-pencil-square me-1"></i>Editar mis datos</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" href="{{ route('verificar') }}"><i class="bi bi-qr-code-scan me-1"></i>Verificar QR</a></li>
                    @endif
                    <li class="nav-item">
                        <span class="nav-link d-none d-lg-inline-flex align-items-center gap-1">
                            <i class="bi bi-person-circle"></i>
                            {{ Auth::user()->nombres ?? Auth::user()->username }}
                        </span>
                    </li>
                    <li class="nav-item d-flex align-items-center ms-md-2">
                        <form method="POST" action="{{ route('logout') }}" class="m-0" id="formLogout">
                            @csrf
                            <button type="button" data-logout class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="app-shell">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-body text-center pt-4 pb-3">
                    <i class="bi bi-box-arrow-right fs-1" style="color:#3d0650"></i>
                    <p class="h5 mt-3 mb-0">¿Está seguro de querer cerrar sesión?</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4" id="btnLogoutConfirm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var form = document.getElementById('formLogout');
            var modalEl = document.getElementById('logoutModal');
            var btn = document.getElementById('btnLogoutConfirm');
            if (!form) return;
            var modal = null;
            if (modalEl && typeof bootstrap !== 'undefined') {
                modal = new bootstrap.Modal(modalEl, {});
            }
            document.querySelectorAll('[data-logout]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    if (modal) {
                        e.preventDefault();
                        modal.show();
                    } else if (!window.confirm('¿Está seguro de querer cerrar sesión?')) {
                        e.preventDefault();
                    }
                });
            });
            if (btn && modal) {
                btn.addEventListener('click', function () {
                    modal.hide();
                    form.submit();
                });
            }
        })();
    </script>
    @include('partials.historial')
    @stack('scripts')
</body>
</html>