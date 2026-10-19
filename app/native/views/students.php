<?php
$title = 'Lista de alumnos';
$students = $students ?? [];
$careers = $careers ?? [];
$search = $search ?? '';
$careerId = $careerId ?? '';
$state = $state ?? '';
$expiring = $expiring ?? false;
$page = $page ?? 1;
$perPage = $perPage ?? 50;
$total = $total ?? 0;
$query = $_GET;
$pageUrl = static function (int $page) use ($query): string {
    $query['page'] = $page;
    return app_url('alumnos?' . http_build_query($query));
};
$pages = max(1, (int) ceil($total / $perPage));
$expiringUrl = app_url('alumnos?' . http_build_query(array_merge($query, ['proximos' => 1, 'page' => 1])));
ob_start();
?>
<div class="page-heading mb-4 flex-wrap">
    <div><span class="eyebrow">Estudiantes</span>
        <h1>Lista de alumnos</h1>
        <p>Gestión de estudiantes, carnets y estados académicos.</p>
    </div><a href="<?= e(app_url('alumnos/crear')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo carnet</a>
</div>
<form method="get" action="<?= e(app_url('alumnos')) ?>" class="row g-2 mb-3">
    <div class="col-md-5"><label class="visually-hidden" for="search">Buscar</label><input id="search" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Nombre, DNI o código"></div>
    <div class="col-md-3"><select name="carrera" class="form-select">
            <option value="">Todas las carreras</option><?php foreach ($careers as $career): ?><option value="<?= e($career['carrera_id']) ?>" <?= (string) $careerId === (string) $career['carrera_id'] ? 'selected' : '' ?>><?= e($career['nombre']) ?></option><?php endforeach; ?>
        </select></div>
    <div class="col-md-2"><select name="estado" class="form-select">
            <option value="">Todos los estados</option><?php foreach (ALUMNO_ESTADOS as $availableState): ?><option <?= $state === $availableState ? 'selected' : '' ?>><?= e($availableState) ?></option><?php endforeach; ?>
        </select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Buscar</button></div>
    <?php if ($expiring): ?><input type="hidden" name="proximos" value="1"><?php endif; ?>
</form>
<div class="d-flex gap-2 mb-3"><a class="btn btn-sm <?= $expiring ? 'btn-warning' : 'btn-outline-warning' ?>" href="<?= e($expiringUrl) ?>"><i class="bi bi-clock-history me-1"></i>Por vencer en 30 días</a><a class="btn btn-sm btn-outline-secondary" href="<?= e(app_url('alumnos')) ?>"><i class="bi bi-x-circle me-1"></i>Limpiar filtros</a></div>
<div class="card section-card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th><i class="bi bi-person me-1"></i>Nombre</th>
                    <th><i class="bi bi-hash me-1"></i>Código</th>
                    <th><i class="bi bi-person-vcard me-1"></i>DNI</th>
                    <th><i class="bi bi-mortarboard me-1"></i>Carrera</th>
                    <th><i class="bi bi-activity me-1"></i>Estado</th>
                    <th><i class="bi bi-gear me-1"></i>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?><tr>
                        <td><?= e(student_full_name($student)) ?></td>
                        <td><?= e($student['codigo_alumno']) ?></td>
                        <td><?= e($student['dni']) ?></td>
                        <td><?= e($student['carrera_nombre'] ?? '-') ?></td>
                        <td><span class="status-badge <?= e(strtolower($student['estado'])) ?>"><?= e($student['estado']) ?></span></td>
                        <td>
                            <div class="btn-group btn-group-sm"><a class="btn btn-outline-primary" title="Ver carnet" href="<?= e(app_url('alumnos/' . (int) $student['alumno_id'] . '/carnet')) ?>"><i class="bi bi-person-vcard me-1"></i>Carnet</a><a class="btn btn-outline-secondary" title="Editar" href="<?= e(app_url('alumnos/' . (int) $student['alumno_id'] . '/editar')) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a><?php if (!empty($student['token_qr'])): ?><a class="btn btn-outline-success" title="Imprimir o guardar PDF" target="_blank" href="<?= e(app_url('verificar/' . rawurlencode($student['token_qr']) . '/pdf')) ?>"><i class="bi bi-printer me-1"></i>Imprimir</a><?php endif; ?><form method="post" action="<?= e(app_url('alumnos/' . (int) $student['alumno_id'])) ?>" onsubmit="return confirm('¿Eliminar este alumno?')"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i>Eliminar</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>
                <?php if (!$students): ?><tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay alumnos con esos filtros.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3"><span class="text-muted"><?= e($total) ?> alumno(s) · página <?= e($page) ?> de <?= e($pages) ?></span>
    <div class="btn-group"><a class="btn btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($pageUrl(max(1, $page - 1))) ?>"><i class="bi bi-chevron-left me-1"></i>Anterior</a><a class="btn btn-outline-secondary <?= $page >= $pages ? 'disabled' : '' ?>" href="<?= e($pageUrl(min($pages, $page + 1))) ?>">Siguiente<i class="bi bi-chevron-right ms-1"></i></a></div>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
