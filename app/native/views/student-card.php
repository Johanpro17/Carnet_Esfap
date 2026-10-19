<?php
$student = $student ?? [];
$carnet = $carnet ?? null;
$studentId = (int) ($student['alumno_id'] ?? 0);
$title = 'Carnet de ' . student_full_name($student);
$user = $user ?? current_user();
ob_start();
?>
<div class="page-heading mb-4">
    <div><span class="eyebrow"><i class="bi bi-person-vcard me-1"></i>Credencial estudiantil</span>
        <h1>Mi carnet</h1>
    </div><?php if (is_admin($user)): ?><a class="btn btn-outline-secondary" href="<?= e(app_url('alumnos')) ?>"><i class="bi bi-arrow-left me-1"></i>Alumnos</a><?php else: ?><a class="btn btn-outline-secondary" href="<?= e(app_url('alumnos/' . $studentId . '/editar')) ?>"><i class="bi bi-pencil-square me-1"></i>Editar datos</a><?php endif; ?>
</div>
<?php if ($carnet): ?><?php require __DIR__ . '/card.php'; ?><div class="d-flex justify-content-center gap-2 mt-4"><a class="btn btn-primary" target="_blank" href="<?= e(app_url('verificar/' . rawurlencode($carnet['token_qr']) . '/pdf')) ?>"><i class="bi bi-filetype-pdf me-1"></i>Descargar PDF</a><a class="btn btn-outline-secondary" href="<?= e(app_url('verificar/' . rawurlencode($carnet['token_qr']))) ?>"><i class="bi bi-qr-code-scan me-1"></i>Verificación pública</a></div><?php else: ?><div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>El alumno todavía no tiene un carnet emitido.</div><?php endif; ?>
<?php if ($carnet && is_admin($user)): ?><section class="card mt-4">
        <div class="card-body">
            <h2 class="h5"><i class="bi bi-arrow-clockwise me-1"></i>Renovar vigencia</h2>
            <form method="post" action="<?= e(app_url('alumnos/' . $studentId . '/renovar')) ?>" class="row g-2 align-items-end"><?= csrf_field() ?><div class="col-auto"><label class="form-label" for="fecha_vigencia"><i class="bi bi-calendar-check me-1"></i>Nueva fecha de vigencia</label><input id="fecha_vigencia" class="form-control" type="date" name="fecha_vigencia" required></div>
                <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="bi bi-check-circle me-1"></i>Renovar</button></div>
            </form>
        </div>
    </section><?php endif; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
