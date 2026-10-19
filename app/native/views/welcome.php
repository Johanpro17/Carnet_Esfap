<?php
$title = 'Carnets ESFAP';
$content = '<section class="hero-card"><img class="hero-logo" src="' . e(app_url('imagenes/logo_de_institucion.png')) . '" alt="ESFAP"><span class="eyebrow"><i class="bi bi-mortarboard-fill"></i>Sistema de carnets</span><h1>ESFAP</h1><p>Consulta, descarga y verifica los carnets de identificaci&oacute;n institucional de la Escuela Superior de Formaci&oacute;n Art&iacute;stica P&uacute;blica.</p><div class="hero-card-actions hero-actions"><a class="btn btn-primary" href="' . e(app_url('login')) . '"><i class="bi bi-box-arrow-in-right"></i>Ingresar</a><a class="btn btn-outline-primary" href="' . e(app_url('verificar')) . '"><i class="bi bi-qr-code-scan"></i>Verificar carnet</a></div></section>';
require __DIR__ . '/public-layout.php';
