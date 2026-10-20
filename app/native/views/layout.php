<?php
$user = current_user();
$statusMessage = flash('status');
$errorMessage = flash('error');
$displayName = trim(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? '')) ?: ($user['correo'] ?? 'Usuario');
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Carnets ESFAP') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('css/style.css')) ?>">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= e(app_url('dashboard')) ?>"><i class="bi bi-mortarboard-fill me-2"></i>Carnet ESFAP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Abrir menú"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if (is_admin($user)): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('dashboard')) ?>"><i class="bi bi-speedometer2 me-1"></i>Panel</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('alumnos')) ?>"><i class="bi bi-people-fill me-1"></i>Alumnos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('alumnos/crear')) ?>"><i class="bi bi-plus-circle me-1"></i>Crear carnet</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('reportes')) ?>"><i class="bi bi-bar-chart-line me-1"></i>Reportes</a></li>
                    <?php elseif (!empty($user['alumno_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('alumnos/' . (int) $user['alumno_id'] . '/carnet')) ?>"><i class="bi bi-person-vcard me-1"></i>Mi carnet</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('alumnos/' . (int) $user['alumno_id'] . '/editar')) ?>"><i class="bi bi-person-lines-fill me-1"></i>Mis datos</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(app_url('verificar')) ?>"><i class="bi bi-qr-code-scan me-1"></i>Verificar QR</a></li>
                    <li class="nav-item"><span class="nav-link"><i class="bi bi-person-circle me-1"></i><?= e($displayName) ?></span></li>
                    <li class="nav-item">
                        <form method="post" action="<?= e(app_url('logout')) ?>" class="m-0">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-light btn-sm" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="app-shell">
        <?php if ($statusMessage): ?><div class="alert alert-success" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert alert-danger" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>