<?php
$title = 'Verificar carnet';
ob_start();
?>
<section class="text-center mb-4 py-4"><span class="eyebrow">Verificación pública</span>
    <h1 class="h2 mt-2">Verificar carnet estudiantil</h1>
    <p class="text-muted">Lee una imagen del QR o pega su código para consultar el carnet.</p>
</section>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">
        <?php if (!empty($error)): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?></div><?php endif; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="get" action="<?= e(app_url('verificar')) ?>" id="formVerificar">
                    <label for="archivoQr" class="form-label fw-semibold"><i class="bi bi-image me-1"></i>Imagen del QR</label>
                    <input type="file" id="archivoQr" accept="image/*" class="form-control mb-3">
                    <div id="estadoDecod" class="small text-muted mb-3" aria-live="polite"></div>
                    <label class="form-label fw-semibold" for="txtQr"><i class="bi bi-qr-code me-1"></i>Código o enlace del QR</label>
                    <div class="input-group"><input type="text" name="qr" id="txtQr" class="form-control" value="<?= e($input ?? '') ?>" autocomplete="off" placeholder="Pega el código o enlace"><button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Verificar</button></div>
                    <div class="form-text mt-2">La verificación consulta el registro institucional vigente.</div>
                </form>
                <canvas id="qrCanvas" class="d-none"></canvas>
            </div>
        </div>
        <div class="text-center mt-3"><a href="<?= e(app_url('')) ?>"><i class="bi bi-house me-1"></i>Volver al inicio</a> · <a href="<?= e(app_url('login')) ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Acceder al sistema</a></div>
    </div>
</div>
<script src="<?= e(app_url('vendor/jsqr/jsQR.js')) ?>"></script>
<script>
    (() => {
        const input = document.getElementById('archivoQr');
        const status = document.getElementById('estadoDecod');
        const text = document.getElementById('txtQr');
        const canvas = document.getElementById('qrCanvas');
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);
            image.onload = () => {
                const scale = Math.min(1, 1200 / Math.max(image.width, image.height));
                canvas.width = Math.round(image.width * scale);
                canvas.height = Math.round(image.height * scale);
                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                const result = window.jsQR && window.jsQR(context.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height);
                URL.revokeObjectURL(objectUrl);
                if (!result || !result.data) {
                    status.textContent = 'No se pudo leer el QR de esa imagen.';
                    return;
                }
                text.value = result.data;
                status.textContent = 'Código leído. Verificando…';
                document.getElementById('formVerificar').requestSubmit();
            };
            image.onerror = () => {
                status.textContent = 'No se pudo abrir la imagen.';
                URL.revokeObjectURL(objectUrl);
            };
            image.src = objectUrl;
        });
    })();
</script>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/public-layout.php';
