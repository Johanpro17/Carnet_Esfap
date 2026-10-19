<?php
$title = 'Panel de gestión';
$metrics = $metrics ?? ['students' => 0, 'cards' => 0, 'expiring' => 0];
$states = $states ?? [];
$careers = $careers ?? [];
$recent = $recent ?? [];
ob_start();
?>
<div class="page-heading mb-4 flex-wrap">
    <div><span class="eyebrow">Administración</span>
        <h1>Panel de gestión de carnets</h1>
        <p>Control académico, vigencias y credenciales estudiantiles.</p>
    </div>
    <a href="<?= e(app_url('alumnos/crear')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo carnet</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card metric-card metric-primary h-100">
            <div class="card-body">
                <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
                <div class="metric-text"><span>Total estudiantes</span>
                    <h2><?= e($metrics['students']) ?></h2><small>Registros activos</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-card metric-info h-100">
            <div class="card-body">
                <div class="metric-icon"><i class="bi bi-person-vcard"></i></div>
                <div class="metric-text"><span>Carnets emitidos</span>
                    <h2><?= e($metrics['cards']) ?></h2><small>Total histórico</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-card metric-success h-100">
            <div class="card-body">
                <div class="metric-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="metric-text"><span>Activos</span>
                    <h2><?= e($states['ACTIVO'] ?? 0) ?></h2><a href="<?= e(app_url('alumnos?' . http_build_query(['estado' => 'ACTIVO']))) ?>">Ver alumnos <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card metric-card metric-warning h-100">
            <div class="card-body">
                <div class="metric-icon"><i class="bi bi-clock-history"></i></div>
                <div class="metric-text"><span>Por vencer</span>
                    <h2><?= e($metrics['expiring']) ?></h2><a href="<?= e(app_url('alumnos?' . http_build_query(['proximos' => 1]))) ?>">Próximos 30 días <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <section class="card section-card h-100">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-diagram-3 me-1"></i>Estudiantes por carrera</h2>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($careers as $career): ?><div class="list-group-item d-flex justify-content-between"><span><?= e($career['nombre']) ?></span><span class="badge text-bg-secondary"><?= e($career['total']) ?></span></div><?php endforeach; ?>
                <?php if (!$careers): ?><div class="list-group-item text-muted">Sin datos.</div><?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-lg-5">
        <section class="card section-card h-100">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-clipboard-data me-1"></i>Situación académica</h2>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($states as $state => $total): ?><div class="col-6">
                            <a class="d-block p-3 rounded-3 border h-100 text-decoration-none text-reset" href="<?= e(app_url('reportes?' . http_build_query(['estado' => $state]))) ?>"><span class="status-badge <?= e(strtolower($state)) ?>"><?= e($state) ?></span>
                                <div class="h3 mb-0 mt-1"><?= e($total) ?> <i class="bi bi-arrow-right-short"></i></div>
                            </a>
                        </div><?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</div>
<section class="card section-card mt-4">
    <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-clock-history me-1"></i>Registros recientes</h2>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($recent as $student): ?><a href="<?= e(app_url('alumnos/' . (int) $student['alumno_id'] . '/carnet')) ?>" class="list-group-item list-group-item-action d-flex justify-content-between"><span><strong><i class="bi bi-person-fill me-1"></i><?= e(student_full_name($student)) ?></strong><br><small class="text-muted"><?= e($student['carrera_nombre'] ?? '-') ?></small></span><small class="text-muted"><?= e(date('d/m/Y H:i', strtotime($student['fecha_registro']))) ?> <i class="bi bi-chevron-right"></i></small></a><?php endforeach; ?>
        <?php if (!$recent): ?><div class="list-group-item text-muted">Sin registros.</div><?php endif; ?>
    </div>
</section>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
