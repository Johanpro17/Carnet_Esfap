<?php
$title = 'Reportes por estado';
$countByState = $countByState ?? [];
$studentsByState = $studentsByState ?? [];
$pagesByState = $pagesByState ?? [];
$pageByState = $pageByState ?? [];
$perPage = $perPage ?? 40;
$stateFilter = $stateFilter ?? '';
$careerMatrix = $careerMatrix ?? [];

$meta = [
    'ACTIVO' => ['label' => 'Activos', 'plural' => 'activos', 'icon' => 'bi-check-circle-fill', 'metric' => 'metric-success'],
    'INACTIVO' => ['label' => 'Inactivos', 'plural' => 'inactivos', 'icon' => 'bi-dash-circle-fill', 'metric' => 'metric-info'],
    'EGRESADO' => ['label' => 'Egresados', 'plural' => 'egresados', 'icon' => 'bi-mortarboard-fill', 'metric' => 'metric-primary'],
    'RETIRADO' => ['label' => 'Retirados', 'plural' => 'retirados', 'icon' => 'bi-x-octagon-fill', 'metric' => 'metric-danger'],
];
$total = array_sum($countByState);
ob_start();
?>
<style>
    @media print {

        .navbar,
        .report-actions,
        .app-shell > .alert {
            display: none !important
        }

        body {
            background: #fff !important
        }

        .app-shell {
            padding: 0 !important
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            transform: none !important
        }

        .report-section {
            break-inside: avoid
        }

        .metric-card::before,
        .card-body,
        .metric-card::after {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact
        }
    }
</style>
<div class="page-heading mb-4 flex-wrap">
    <div><span class="eyebrow"><i class="bi bi-bar-chart-line me-1"></i>Reportes</span>
        <h1>Reporte de alumnos por estado</h1>
        <p>Resumen y detalle de estudiantes según su situación académica. <?= e($total) ?> alumno(s) en total.</p>
    </div>
    <div class="report-actions d-flex gap-2">
        <?php if ($stateFilter !== ''): ?><a class="btn btn-outline-secondary" href="<?= e(app_url('reportes')) ?>"><i class="bi bi-arrow-repeat me-1"></i>Ver todos</a><?php endif; ?>
        <button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir reporte</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach (ALUMNO_ESTADOS as $state): $info = $meta[$state] ?? ['label' => $state, 'plural' => strtolower($state), 'icon' => 'bi-circle', 'metric' => 'metric-primary']; ?>
        <div class="col-6 col-lg-3">
            <a class="card metric-card <?= e($info['metric']) ?> h-100 text-decoration-none text-reset" href="<?= e(app_url('reportes?' . http_build_query(['estado' => $state]))) ?>">
                <div class="card-body">
                    <div class="metric-icon"><i class="bi <?= e($info['icon']) ?>"></i></div>
                    <div class="metric-text"><span><?= e($info['label']) ?></span>
                        <h2><?= e($countByState[$state] ?? 0) ?></h2><small><?= e($countByState[$state] ?? 0) ?> alumno(s)</small>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<section class="card section-card report-section mb-4">
    <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-grid-1x2 me-1"></i>Resumen por carrera y estado</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><i class="bi bi-mortarboard me-1"></i>Carrera</th>
                    <?php foreach (ALUMNO_ESTADOS as $state): ?>
                        <th class="text-center"><?= e($meta[$state]['label'] ?? $state) ?></th>
                    <?php endforeach; ?>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($careerMatrix as $career => $tally): $rowTotal = 0; ?>
                    <tr>
                        <td><?= e($career) ?></td>
                        <?php foreach (ALUMNO_ESTADOS as $state): $value = (int) ($tally[$state] ?? 0); $rowTotal += $value; ?>
                            <td class="text-center"><span class="status-badge <?= e(strtolower($state)) ?>"><?= e($value) ?></span></td>
                        <?php endforeach; ?>
                        <td class="text-center fw-bold"><?= e($rowTotal) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$careerMatrix): ?><tr>
                        <td colspan="<?= e(count(ALUMNO_ESTADOS) + 2) ?>" class="text-center text-muted py-4">No hay datos para mostrar.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php foreach (ALUMNO_ESTADOS as $state): $info = $meta[$state] ?? ['label' => $state, 'plural' => strtolower($state), 'icon' => 'bi-circle', 'metric' => 'metric-primary']; ?>
    <?php if ($stateFilter !== '' && $stateFilter !== $state) {
        continue;
    } ?>
    <?php
    $rows = $studentsByState[$state] ?? [];
    $stateTotal = (int) ($countByState[$state] ?? 0);
    $pages = (int) ($pagesByState[$state] ?? 1);
    $currentPage = (int) ($pageByState[$state] ?? 1);
    $pageLink = static function (int $target) use ($state, $stateFilter): string {
        $query = [];
        if ($stateFilter !== '') {
            $query['estado'] = $stateFilter;
        }
        $query['p'] = [$state => $target];
        return app_url('reportes?' . http_build_query($query));
    };
    ?>
    <section class="card section-card report-section mb-4" id="estado-<?= e(strtolower($state)) ?>">
        <div class="card-header">
            <h2 class="h5 mb-0"><i class="bi <?= e($info['icon']) ?> me-1"></i>Alumnos <?= e($info['plural']) ?>
                <span class="badge bg-primary ms-1"><?= e($stateTotal) ?></span>
            </h2>
            <span class="status-badge <?= e(strtolower($state)) ?> ms-auto"><?= e($state) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="bi bi-person me-1"></i>Nombre completo</th>
                        <th><i class="bi bi-hash me-1"></i>Código</th>
                        <th><i class="bi bi-envelope me-1"></i>Correo</th>
                        <th><i class="bi bi-mortarboard me-1"></i>Carrera</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?><tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay alumnos en estado <?= e($info['label']) ?>.</td>
                        </tr><?php else: foreach ($rows as $index => $student): ?><tr>
                                <td><?= e($index + 1) ?></td>
                                <td><?= e(student_full_name($student)) ?></td>
                                <td><?= e($student['codigo_alumno']) ?></td>
                                <td><?= e($student['correo'] ?: '-') ?></td>
                                <td><?= e($student['carrera_nombre'] ?? '-') ?></td>
                                <td class="text-center"><a class="btn btn-sm btn-outline-primary" href="<?= e(app_url('alumnos/' . (int) $student['alumno_id'] . '/carnet')) ?>"><i class="bi bi-person-vcard me-1"></i>Ver carnet</a></td>
                            </tr><?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($stateTotal > 0): ?>
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <span class="text-muted small"><i class="bi bi-list-ol me-1"></i><?= e($stateTotal) ?> alumno(s) · página <?= e($currentPage) ?> de <?= e($pages) ?> · <?= e($perPage) ?> por página</span>
                <?php if ($pages > 1): ?><div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="<?= e($pageLink(max(1, $currentPage - 1))) ?>"><i class="bi bi-chevron-left me-1"></i>Anterior</a>
                        <a class="btn btn-sm btn-outline-secondary <?= $currentPage >= $pages ? 'disabled' : '' ?>" href="<?= e($pageLink(min($pages, $currentPage + 1))) ?>">Siguiente<i class="bi bi-chevron-right ms-1"></i></a>
                    </div><?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
