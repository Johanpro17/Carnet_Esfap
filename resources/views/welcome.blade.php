<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Carnets ESFAP') }}</title>
    <style>
        :root {
            --primary: #5a0c70;
            --primary-light: #7b2d92;
            --primary-dark: #3d0650;
            --tint: #ecd9f2;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary), var(--primary-light));
            color: #fff;
        }
        .card {
            background: #fff;
            color: #2a2a2a;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,.35);
        }
        .badge {
            display: inline-block;
            background: var(--tint);
            color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 999px;
            letter-spacing: .5px;
        }
        h1 { margin: 16px 0 8px; color: var(--primary-dark); }
        p { margin: 0 0 24px; color: #666; font-size: 15px; }
        .btn {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn:hover { background: var(--primary-light); }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">SISTEMA DE CARNETS</span>
        <h1>ESFAP</h1>
        <p>El sistema está listo y conectado a su base de datos.</p>
        <a class="btn" href="/login">Ingresar</a>
    </div>
    @include('partials.historial')
</body>
</html>