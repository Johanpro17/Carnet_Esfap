<?php
$title = 'No registrado · Carnets ESFAP';
$token = $token ?? '';
ob_start();
?>
<section class="container py-5 text-center">
    <div class="alert alert-danger d-inline-block mb-3">
        <i class="bi bi-x-octagon-fill me-1"></i><strong>NO REGISTRADO EN EL SISTEMA</strong><br>
        <small>El carnet escaneado no corresponde a ningún estudiante registrado en la ESFAP.</small>
    </div>
    <h1 class="h4">Carnet no encontrado</h1>
    <p class="text-muted">Verifica que el código o el enlace escaneado sea correcto. Si crees que es un error, comunícate con la institución.</p>
    <?php if ($token !== ''): ?><p class="text-muted small">Código consultado: <code><?= e($token) ?></code></p><?php endif; ?>
    <div class="d-flex justify-content-center gap-2 mt-3">
        <a class="btn btn-primary" href="<?= e(app_url('verificar')) ?>"><i class="bi bi-qr-code-scan me-1"></i>Verificar otro carnet</a>
        <a class="btn btn-outline-secondary" href="<?= e(app_url()) ?>"><i class="bi bi-house me-1"></i>Volver al inicio</a>
    </div>
</section>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/public-layout.php';
