<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])
    ->middleware('guest');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])
    ->name('logout');

Route::get('/qr/{token}', [App\Http\Controllers\QrController::class, 'png'])
    ->name('qr.png');

Route::get('/verificar', function (Illuminate\Http\Request $request) {
    $qr = trim((string) $request->query('qr'));
    $token = $qr;

    if (preg_match('#/verificar/([a-f0-9]+)#i', $qr, $m)) {
        $token = $m[1];
    }

    if ($token !== '' && App\Http\Controllers\CarnetController::porToken($token)) {
        return redirect()->route('carnet.verificar', ['token' => $token]);
    }

    return view('verificar')->withErrors(['qr' => 'Código no válido. Verifique el enlace o código escaneado.']);
})->name('verificar');

Route::get('/verificar/{token}', [App\Http\Controllers\CarnetController::class, 'show'])
    ->name('carnet.verificar');
Route::get('/verificar/{token}/pdf', [App\Http\Controllers\CarnetController::class, 'pdf'])
    ->name('carnet.pdf');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/alumnos', [App\Http\Controllers\AlumnoController::class, 'index'])
            ->name('alumnos.index');
        Route::get('/alumnos/crear', [App\Http\Controllers\AlumnoController::class, 'create'])
            ->name('alumnos.create');
        Route::post('/alumnos', [App\Http\Controllers\AlumnoController::class, 'store'])
            ->name('alumnos.store');
        Route::delete('/alumnos/{alumno}', [App\Http\Controllers\AlumnoController::class, 'destroy'])
            ->name('alumnos.destroy');
        Route::post('/alumnos/{alumno}/renovar', [App\Http\Controllers\AlumnoController::class, 'renovarCarnet'])
            ->name('alumnos.renovar');
    });

    Route::middleware('propio')->group(function () {
        Route::get('/alumnos/{alumno}/editar', [App\Http\Controllers\AlumnoController::class, 'edit'])
            ->name('alumnos.edit');
        Route::put('/alumnos/{alumno}', [App\Http\Controllers\AlumnoController::class, 'update'])
            ->name('alumnos.update');
        Route::get('/alumnos/{alumno}/carnet', [App\Http\Controllers\AlumnoController::class, 'carnet'])
            ->name('alumnos.carnet');
    });
});
