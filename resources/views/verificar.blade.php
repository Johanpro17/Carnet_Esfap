@extends('layouts.publica')

@section('title', 'Verificar carnet')

@section('styles')
    <style>
        .verify-hero {
            background: linear-gradient(135deg, #3d0650 0%, #5a0c70 60%, #7b2d92 100%);
            color: #fff;
            padding: 2.75rem 1rem;
            text-align: center;
            margin: -1.5rem calc(50% - 50vw) 2rem;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 8px 24px rgba(61, 6, 80, .18);
        }
        .verify-hero h1 { font-weight: 800; font-size: clamp(1.4rem, 4vw, 2.2rem); }
        .verify-hero p { opacity: .95; margin-bottom: 0; }
        .upload-zone {
            border: 2px dashed #7b2d92;
            border-radius: 14px;
            padding: 2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: background .2s;
        }
        .upload-zone:hover { background: #f6effa; }
        .upload-zone.has-image { border-style: solid; }
        .upload-zone img { max-width: 240px; max-height: 240px; border-radius: 12px; }
    </style>
@endsection

@section('content')
    <div class="verify-hero">
        <div class="container">
            <h1><i class="bi bi-qr-code me-2"></i>Verificar Carnet Estudiantil</h1>
            <p>Sube la foto del QR o pega el código para ver el carnet del estudiante</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            @if ($errors->has('qr'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error:</strong> {{ $errors->first('qr') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-header text-white rounded-top-4" style="background:linear-gradient(135deg, var(--primary), var(--primary-light))">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Sube la imagen del QR</h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('verificar') }}" id="formVerificar">
                        <label for="archivoQr" class="upload-zone d-block mb-3">
                            <span id="zonaTexto" class="d-block">
                                <i class="bi bi-image display-4 text-primary"></i>
                                <p class="mt-3 mb-0 fw-semibold">Haz clic aquí para subir la imagen del QR</p>
                                <small class="text-muted">JPG, PNG o GIF</small>
                            </span>
                            <img id="vistaPrevia" src="" alt="Vista previa" class="d-none mx-auto">
                        </label>
                        <input type="file" id="archivoQr" accept="image/*" class="d-none">
                        <div id="estadoDecod" class="text-muted small mb-3"></div>

                        <div class="my-3 d-flex align-items-center text-muted">
                            <hr class="flex-grow-1"><span class="px-2">o</span><hr class="flex-grow-1">
                        </div>

                        <label class="form-label fw-semibold">Pega el código del QR</label>
                        <div class="input-group">
                            <input type="text" name="qr" id="txtQr" class="form-control"
                                   placeholder="Código o enlace del QR..." value="{{ request('qr') }}" autocomplete="off">
                            <button class="btn btn-primary fw-semibold" type="submit">
                                <i class="bi bi-search me-2"></i>Verificar
                            </button>
                        </div>
                        <input type="hidden" name="qr_decoded" id="qrDecoded">
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-shield-check me-1"></i>El QR del carnet contiene un enlace oficial: al escanearlo se abre la página de validación del sistema ESFAP.
                        </small>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 mb-4">
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retroceder
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>Acceder al sistema
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
    <script>
        (function () {
            var input = document.getElementById('archivoQr');
            var zona = document.querySelector('.upload-zone');
            var zonaTexto = document.getElementById('zonaTexto');
            var previa = document.getElementById('vistaPrevia');
            var estado = document.getElementById('estadoDecod');
            var txtQr = document.getElementById('txtQr');
            var qrDecoded = document.getElementById('qrDecoded');
            var form = document.getElementById('formVerificar');

            if (!input || typeof jsQR === 'undefined') return;

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;

                var img = new Image();
                var url = URL.createObjectURL(file);
                img.onload = function () {
                    var max = 1200;
                    var escala = Math.min(1, max / Math.max(img.width, img.height));
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.round(img.width * escala);
                    canvas.height = Math.round(img.height * escala);
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    previa.src = canvas.toDataURL('image/png');
                    previa.classList.replace('d-none', 'd-block');
                    zona.classList.add('has-image');
                    zonaTexto.classList.add('d-none');
                    estado.innerHTML = '<span class="text-primary"><i class="bi bi-hourglass-split me-1"></i>Escaneando el código QR...</span>';

                    var datos = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    var resultado = jsQR(datos.data, datos.width, datos.height, { inversionAttempts: 'dontInvert' });
                    if (resultado && resultado.data) {
                        var texto = resultado.data;
                        var m = texto.match(/[?&]token=([0-9a-f]+)/i);
                        var token = m ? m[1] : texto;
                        txtQr.value = token;
                        qrDecoded.value = token;
                        estado.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>QR leído correctamente. Abriendo el carnet...</span>';
                        form.submit();
                    } else {
                        estado.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>No se pudo leer el QR en la imagen. Intenta con otra foto.</span>';
                    }
                    URL.revokeObjectURL(url);
                };
                img.onerror = function () {
                    estado.innerHTML = '<span class="text-danger">La imagen no se pudo cargar.</span>';
                };
                img.src = url;
            });
        })();
    </script>
@endsection