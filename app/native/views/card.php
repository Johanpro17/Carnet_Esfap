<?php
$carnet = $carnet ?? [];
$vigency = $carnet['fecha_vigencia'] ?? null;
$valid = ($carnet['alumno_estado'] ?? $carnet['estado'] ?? '') === 'ACTIVO'
    && ($carnet['carnet_estado'] ?? 'ACTIVO') === 'ACTIVO'
    && (!$vigency || $vigency >= date('Y-m-d'));
$apellidos = trim(($carnet['apellido_paterno'] ?? '') . ' ' . ($carnet['apellido_materno'] ?? ''));
?>
<article class="id-card">
    <header>
        <img class="brand-logo" src="<?= e(app_url('imagenes/logo_de_institucion.png')) ?>" alt="ESFAP">
        <div class="brand-text">
            <strong>Escuela Superior de Formaci&oacute;n Art&iacute;stica P&uacute;blica</strong>
            <span>&ldquo;Eduardo Meza Saravia&rdquo;</span>
        </div>
        <span class="validity <?= $valid ? 'valid' : 'invalid' ?>"><i class="bi bi-patch-check-fill me-1"></i><?= $vigency ? e(date('d/m/Y', strtotime($vigency))) : 'PENDIENTE' ?></span>
    </header>
    <div class="id-card-main">
        <div class="photo-top">
            <div class="photo">
                <?php if (!empty($carnet['foto']) && is_file(APP_ROOT . '/public/uploads/' . basename($carnet['foto']))): ?>
                    <img src="<?= e(app_url('uploads/' . rawurlencode(basename($carnet['foto'])))) ?>" alt="Foto del alumno">
                <?php else: ?>
                    <span><?= e(strtoupper(substr($carnet['nombres'] ?? '', 0, 1) . substr($carnet['apellido_paterno'] ?? '', 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-title-bar">
                <span class="info-role">Estudiante</span>
            </div>
        </div>
        <div class="id-card-info">
            <div class="info-left">
                <div class="info-field">
                    <span class="info-label"><i class="bi bi-person-fill"></i>Nombres</span>
                    <span class="info-value"><?= e($carnet['nombres'] ?? '-') ?></span>
                </div>
                <div class="info-field">
                    <span class="info-label"><i class="bi bi-people-fill"></i>Apellidos</span>
                    <span class="info-value"><?= e($apellidos !== '' ? $apellidos : '-') ?></span>
                </div>
                <div class="info-field">
                    <span class="info-label"><i class="bi bi-person-vcard"></i>DNI</span>
                    <span class="info-value"><?= e($carnet['dni'] ?? '-') ?></span>
                </div>
                <div class="info-field">
                    <span class="info-label"><i class="bi bi-mortarboard-fill"></i>Carrera</span>
                    <span class="info-value"><?= e($carnet['carrera_nombre'] ?? '-') ?></span>
                </div>
            </div>
            <div class="qr-block">
                <img src="<?= e(app_url('qr/' . rawurlencode($carnet['token_qr']))) ?>" alt="QR de verificaci&oacute;n">
                <small>Escanea para verificar</small>
            </div>
        </div>
    </div>
    <footer>
        <span><i class="bi bi-credit-card-2-front"></i>Carnet estudiantil</span>
        <span class="footer-code">N.&deg; <?= e($carnet['codigo_alumno'] ?? '-') ?></span>
    </footer>
</article>
