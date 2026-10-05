<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Carnets ESFAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        input:-webkit-autofill,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-box-shadow: 0 0 0px 1000px white inset !important;
            box-shadow: 0 0 0px 1000px white inset !important;
            -webkit-text-fill-color: inherit !important;
        }

        html, body { height: 100%; }

        .login-body {
            background: linear-gradient(135deg, #eaf1fb 0%, #d6e6f8 47%, #f3f6fc 100%);
            min-height: 100%;
            display: flex;
            align-items: center;
            padding: 1.5rem;
        }

        .auth-card {
            max-width: 980px;
            margin: 0 auto;
            width: 100%;
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(90, 12, 112, 0.2);
        }

        .auth-panel {
            background: linear-gradient(160deg, #3d0650 0%, #5a0c70 55%, #7b2d92 100%);
            color: #fff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-panel .logo-box {
            width: 112px;
            height: 112px;
            border-radius: 28px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
            margin-bottom: 1.5rem;
        }

        .auth-panel .logo-box img {
            max-width: 88px;
            object-fit: contain;
        }

        .auth-panel h1 {
            font-weight: 800;
            letter-spacing: 1px;
        }

        .auth-panel .motto {
            color: #ffe082;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.4px;
        }

        .auth-panel .accent-line {
            width: 52px;
            height: 5px;
            background: linear-gradient(90deg, #ffd54f, #d4af37);
            border-radius: 3px;
            margin: 1rem 0 1.25rem;
        }

        .auth-panel .feat {
            font-size: 0.92rem;
            color: #e3edfb;
        }

        .auth-form-wrap {
            background: #fff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form-wrap h2 {
            font-weight: 700;
            color: #3d0650;
        }

        .auth-form-wrap .sub {
            color: #64748b;
            font-size: 0.95rem;
        }

        .auth-input {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .auth-input:focus {
            border-color: #7b2d92;
            box-shadow: 0 0 0 4px rgba(90, 12, 112, 0.12);
        }

        .btn-auth {
            background: linear-gradient(135deg, #3d0650 0%, #5a0c70 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            transition: all 0.25s ease;
        }

        .btn-auth:hover,
        .btn-auth:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(90, 12, 112, 0.35);
            color: #fff;
        }

        .pw-toggle {
            border: 2px solid #e2e8f0;
            border-left: 0;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            color: #7b2d92;
        }

        @media (max-width: 767.98px) {
            .auth-card { max-width: 460px; }
            .auth-panel {
                padding: 1.75rem 1.5rem;
                text-align: center;
            }
            .auth-panel .accent-line {
                margin-left: auto;
                margin-right: auto;
            }
            .auth-form-wrap {
                padding: 1.75rem 1.5rem;
            }
            .login-body { align-items: flex-start; padding: 1rem; }
        }
    </style>
</head>
<body class="login-body">
    <div class="container px-0">
        <div class="card auth-card shadow-sm">
            <div class="row g-0">
                <div class="col-md-5 auth-panel">
                    <div class="logo-box mx-auto mx-md-0">
                        <img src="{{ asset('imagenes/logo_de_institucion.png') }}" alt="ESFAP logo">
                    </div>
                    <h1 class="h3 mb-1">ESFAP</h1>
                    <p class="motto mb-0">Educación Superior de Formación Artística</p>
                    <div class="accent-line"></div>
                    <p class="feat mb-3"><i class="bi bi-patch-check-fill me-2"></i>Carnet digital con QR de verificación</p>
                    <p class="feat mb-3"><i class="bi bi-arrow-repeat me-2"></i>Vigencia y semestre actualizados</p>
                    <p class="feat mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Acceso seguro para alumnos y administrativos</p>
                </div>
                <div class="col-md-7 auth-form-wrap">
                    <h2 class="h4 mb-2">Bienvenido</h2>
                    <p class="sub mb-4">Ingresa tu contraseña para acceder a tu espacio.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" autocomplete="on">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label fw-semibold" for="password">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" id="pwIcon"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="password" id="password"
                                    class="form-control auth-input rounded-start-0" placeholder="Escribe tu contraseña"
                                    autocomplete="current-password" required autofocus>
                                <button type="button" class="btn btn-outline-secondary pw-toggle" id="btnMostrar"
                                        aria-label="Mostrar contraseña" tabindex="-1">
                                    <i class="bi bi-eye" id="iconOjo"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Los alumnos usan su correo institucional como contraseña.
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Recordarme en este equipo</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-auth btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var pw = document.getElementById('password');
            var btn = document.getElementById('btnMostrar');
            var ojo = document.getElementById('iconOjo');
            if (pw && btn) {
                btn.addEventListener('click', function () {
                    var oculto = pw.type === 'password';
                    pw.type = oculto ? 'text' : 'password';
                    ojo.className = oculto ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }
        })();
    </script>
    @include('partials.historial')
</body>
</html>