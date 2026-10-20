<?php
$title = 'Ingresar · Carnets ESFAP';
ob_start();
?>
<div class="login-shell">
    <aside class="login-brand">
        <img class="escudo" src="<?= e(app_url('imagenes/logo_de_institucion.png')) ?>" alt="Escudo ESFAP">
        <h2>ESFAP</h2>
        <p>Escuela Superior de Formación Artística Pública<br>&ldquo;Eduardo Meza Saravia&rdquo;</p>
    </aside>
    <section class="login-form">
        <div class="eyebrow"><i class="bi bi-shield-lock"></i> Sistema de identificación</div>
        <h1>Bienvenido</h1>
        <p class="lead-text">Ingresa tu contraseña para acceder al sistema de carnets.</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(app_url('login')) ?>" autocomplete="on">
            <?= csrf_field() ?>
            <label class="form-label fw-semibold" for="password">Contraseña</label>
            <div class="input-icon mb-2">
                <i class="bi bi-key-fill"></i>
                <input class="form-control form-control-lg" type="password" name="password" id="password" autocomplete="current-password" placeholder="Tu contraseña" required autofocus>
            </div>
            <button class="btn btn-primary btn-lg w-100 mt-4" type="submit"><i class="bi bi-box-arrow-in-right me-2"></i>Ingresar</button>
        </form>
    </section>
</div>
<?php
$content = (string) ob_get_clean();
require __DIR__ . '/public-layout.php';
