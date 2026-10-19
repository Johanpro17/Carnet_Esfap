<?php
$title = (string) ($code ?? 500) . ' · Carnets ESFAP';
ob_start();
?>
<section class="container py-5 text-center">
    <p class="eyebrow"><i class="bi bi-exclamation-octagon-fill me-1"></i>ERROR <?= e($code ?? 500) ?></p>
    <h1 class="h3">No se pudo completar la solicitud</h1>
    <p class="text-muted"><?= e($message ?? 'Ocurrió un error inesperado.') ?></p>
    <a class="btn btn-primary" href="<?= e(app_url()) ?>"><i class="bi bi-house me-1"></i>Volver al inicio</a>
</section>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/public-layout.php';
