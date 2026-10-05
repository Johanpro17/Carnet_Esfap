<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarPropio
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        abort_unless($user, 403, 'Sesión no iniciada.');

        if (!$user->esAdmin()) {
            $alumno = $request->route('alumno');

            abort_unless(
                $alumno && $alumno->alumno_id === $user->alumno_id,
                403,
                'No tiene permiso para acceder a este carnet.'
            );
        }

        return $next($request);
    }
}