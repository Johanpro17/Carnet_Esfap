<?php
$carnet = $carnet ?? [];
$printOnly = $printOnly ?? false;
$title = $printOnly ? 'Carnet para imprimir' : 'Verificación de carnet';
$vigency = $carnet['fecha_vigencia'] ?? null;
$valid = ($carnet['alumno_estado'] ?? $carnet['estado'] ?? '') === 'ACTIVO'
    && ($carnet['carnet_estado'] ?? 'ACTIVO') === 'ACTIVO'
    && (!$vigency || $vigency >= date('Y-m-d'));
ob_start();
?>
<style>
    @media print {
        body {
            background: #fff !important
        }

        .public-top,
        footer,
        .print-actions {
            display: none !important
        }

        main.container {
            max-width: none !important;
            padding: 0 !important
        }

        .id-card {
            box-shadow: none;
            border-radius: 0;
            margin: 0 auto;
            break-inside: avoid
        }
    }
</style>
<div class="text-center mb-3">
    <div class="alert alert-primary d-inline-block mb-2"><i class="bi bi-person-check-fill me-1"></i><strong>REGISTRADO EN EL SISTEMA</strong><br><small>El carnet escaneado pertenece a un estudiante registrado en la ESFAP.</small></div>
    <div class="alert <?= $valid ? 'alert-success' : 'alert-danger' ?> d-inline-block mb-2"><i class="bi <?= $valid ? 'bi-patch-check-fill' : 'bi-x-octagon-fill' ?> me-1"></i><strong><?= $valid ? 'CARNET VÁLIDO' : 'CARNET NO VIGENTE' ?></strong><br><small><?= $vigency ? 'Vigencia hasta ' . e(date('d/m/Y', strtotime($vigency))) : 'Sin fecha de vigencia registrada' ?></small></div>
    <?php if (!$printOnly): ?><div class="small text-muted">Registro oficial verificado por el sistema institucional.</div><?php endif; ?>
</div>
<?php require __DIR__ . '/card.php'; ?>
<?php if (!$printOnly): ?><div class="print-actions d-flex justify-content-center gap-2 mt-4"><a class="btn btn-primary" target="_blank" href="<?= e(app_url('verificar/' . rawurlencode($carnet['token_qr']) . '/pdf')) ?>"><i class="bi bi-filetype-pdf me-1"></i>Descargar PDF</a><a class="btn btn-outline-secondary" href="<?= e(app_url('verificar')) ?>"><i class="bi bi-arrow-left me-1"></i>Verificar otro carnet</a></div><?php else: ?><div class="print-actions text-center mt-3"><button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-filetype-pdf me-1"></i>Guardar como PDF</button></div>
    <script>
        window.addEventListener('load', () => window.print());
    </script><?php endif; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/public-layout.php';
