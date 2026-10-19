<?php $content = $content ?? ''; ?>
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

<body class="bg-light">
    <div class="public-top">
        <div class="container py-2">
            <a href="<?= e(app_url()) ?>" class="text-white text-decoration-none d-flex align-items-center gap-2">
                <img src="<?= e(app_url('imagenes/logo_de_institucion.png')) ?>" width="38" height="38" class="rounded-2" alt="ESFAP">
                <span><strong>ESFAP</strong><br><small>Carnet de identificación institucional</small></span>
            </a>
        </div>
    </div>
    <main class="container py-4"><?= $content ?></main>
    <footer class="text-center text-muted py-3 small">Escuela Superior de Formación Artística Pública · Verificación de carnets</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>