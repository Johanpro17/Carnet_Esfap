<?php
$user = $user ?? current_user();
$student = $student ?? null;
$careers = $careers ?? [];
$currentPeriod = $currentPeriod ?? (int) date('Y');
$currentSemester = $currentSemester ?? ((int) date('n') >= 8 ? 2 : 1);
$admin = is_admin($user);
$studentId = isset($student['alumno_id']) ? (int) $student['alumno_id'] : null;
$returnPath = $admin ? 'alumnos' : ($studentId !== null ? 'alumnos/' . $studentId . '/carnet' : 'login');
$title = $student ? 'Editar alumno' : 'Crear carnet';
$value = static fn(string $key, mixed $default = ''): string => (string) ($student[$key] ?? $default ?? '');
$action = $studentId !== null ? 'alumnos/' . $studentId : 'alumnos';
ob_start();
?>
<div class="page-heading mb-4">
    <div><span class="eyebrow"><?= $student ? 'Edición' : 'Registro' ?></span>
        <h1><?= $student ? 'Editar alumno' : 'Crear nuevo carnet' ?></h1>
        <p><?= $student ? 'Actualiza los datos del estudiante.' : 'Registra al estudiante y genera su carnet con QR.' ?></p>
    </div><a href="<?= e(app_url($returnPath)) ?>" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
</div>
<div class="card form-card">
    <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-person-lines-fill me-1"></i>Datos del estudiante</h2>
    </div>
    <div class="card-body p-4">
        <form method="post" action="<?= e(app_url($action)) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($student): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>
            <div class="row g-3">
                <?php foreach (['nombres' => 'Nombres', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno'] as $key => $label): ?><div class="col-md-4"><label class="form-label" for="<?= e($key) ?>"><i class="bi bi-person me-1"></i><?= e($label) ?><?= $key !== 'apellido_materno' ? ' *' : '' ?></label><input id="<?= e($key) ?>" class="form-control" name="<?= e($key) ?>" value="<?= e($value($key)) ?>" <?= $admin ? '' : 'disabled readonly' ?> <?= $key !== 'apellido_materno' && $admin ? 'required' : '' ?>></div><?php endforeach; ?>
                <div class="col-md-4"><label class="form-label" for="codigo_alumno"><i class="bi bi-hash me-1"></i>Código de alumno *</label><input id="codigo_alumno" class="form-control" name="codigo_alumno" value="<?= e($value('codigo_alumno', $nextCode ?? '')) ?>" <?= $admin ? 'required' : 'disabled readonly' ?>></div>
                <div class="col-md-4"><label class="form-label" for="fecha_nacimiento"><i class="bi bi-calendar-event me-1"></i>Fecha de nacimiento<?= $admin ? ' *' : '' ?></label><input id="fecha_nacimiento" class="form-control" type="date" name="fecha_nacimiento" value="<?= e($value('fecha_nacimiento')) ?>" <?= $admin ? 'required' : 'disabled readonly' ?>></div>
                <div class="col-md-6"><label class="form-label" for="carrera_id"><i class="bi bi-mortarboard me-1"></i>Carrera *</label><select id="carrera_id" class="form-select" name="carrera_id" <?= $admin ? 'required' : 'disabled' ?>>
                        <option value="">Selecciona una carrera</option><?php foreach ($careers as $career): ?><option value="<?= e($career['carrera_id']) ?>" <?= (string) ($student['carrera_id'] ?? '') === (string) $career['carrera_id'] ? 'selected' : '' ?>><?= e($career['nombre']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-6"><label class="form-label" for="correo"><i class="bi bi-envelope me-1"></i>Correo</label><input id="correo" class="form-control" type="email" name="correo" value="<?= e($value('correo')) ?>" <?= $admin ? '' : 'disabled readonly' ?>></div>
                <div class="col-md-4"><label class="form-label" for="sexo"><i class="bi bi-gender-ambiguous me-1"></i>Sexo</label><select id="sexo" class="form-select" name="sexo" <?= $admin ? '' : 'disabled' ?>>
                        <option value="">-</option><?php foreach (['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'] as $key => $label): ?><option value="<?= e($key) ?>" <?= ($student['sexo'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-4"><label class="form-label" for="estado"><i class="bi bi-activity me-1"></i>Estado</label><select id="estado" class="form-select" name="estado" <?= $admin ? '' : 'disabled' ?>><?php foreach (ALUMNO_ESTADOS as $state): ?><option value="<?= e($state) ?>" <?= ($student['estado'] ?? 'ACTIVO') === $state ? 'selected' : '' ?>><?= e($state) ?></option><?php endforeach; ?></select></div>
                <?php if (!$student): ?><div class="col-md-4"><label class="form-label" for="periodo"><i class="bi bi-calendar-range me-1"></i>Periodo académico</label><input id="periodo" class="form-control" type="number" name="periodo" min="2000" max="2100" value="<?= e($currentPeriod) ?>" required></div>
                    <div class="col-md-4"><label class="form-label" for="semestre"><i class="bi bi-calendar3-week me-1"></i>Semestre</label><select id="semestre" class="form-select" name="semestre">
                            <option value="1" <?= $currentSemester === 1 ? 'selected' : '' ?>>I</option>
                            <option value="2" <?= $currentSemester === 2 ? 'selected' : '' ?>>II</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label" for="fecha_vigencia"><i class="bi bi-calendar-check me-1"></i>Vigencia</label><input id="fecha_vigencia" class="form-control" type="date" name="fecha_vigencia" value="<?= e(date('Y-m-d', strtotime('+5 years'))) ?>" required></div><?php endif; ?>
                <?php if ($admin): ?><div class="col-12"><label class="form-label" for="foto"><i class="bi bi-camera me-1"></i>Foto (JPG/PNG/GIF, máximo 5 MB)</label><input id="foto" class="form-control" type="file" name="foto" accept="image/jpeg,image/png,image/gif" <?= $student ? '' : 'required' ?>></div><?php endif; ?>
            </div>
            <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi <?= $student ? 'bi-check-circle' : 'bi-person-plus' ?> me-1"></i><?= $student ? 'Guardar cambios' : 'Generar carnet' ?></button><a class="btn btn-outline-secondary" href="<?= e(app_url($returnPath)) ?>"><i class="bi bi-x-circle me-1"></i>Cancelar</a></div>
        </form>
    </div>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/layout.php';
