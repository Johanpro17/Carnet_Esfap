<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Carnet;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        if (!$usuario->esAdmin()) {
            if ($usuario->alumno_id) {
                return redirect()->route('alumnos.carnet', $usuario->alumno_id);
            }
            abort(403, 'Acceso restringido.');
        }

        $hoy = now()->toDateString();
        $limite = now()->addDays(30)->toDateString();

        $totalAlumnos = Alumno::count();
        $totalCarnets = Carnet::count();

        $activos = Alumno::where('estado', 'ACTIVO')->count();
        $suspendidos = Alumno::where('estado', 'SUSPENDIDO')->count();
        $inactivos = Alumno::where('estado', 'INACTIVO')->count();
        $egresados = Alumno::where('estado', 'EGRESADO')->count();
        $retirados = Alumno::where('estado', 'RETIRADO')->count();

        $porVencer = Alumno::whereHas('carnets.vencimientos', function ($q) use ($hoy, $limite) {
            $q->whereBetween('fecha_vigencia', [$hoy, $limite]);
        })->count();

        $porCarrera = DB::table('alumnos')
            ->join('carreras', 'carreras.carrera_id', '=', 'alumnos.carrera_id')
            ->select('carreras.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('carreras.carrera_id', 'carreras.nombre')
            ->orderByDesc('total')
            ->get();

        $recientes = Alumno::with('carrera')
            ->orderByDesc('fecha_registro')
            ->limit(5)
            ->get();

        $estados = [
            'ACTIVO' => $activos,
            'SUSPENDIDO' => $suspendidos,
            'INACTIVO' => $inactivos,
            'EGRESADO' => $egresados,
            'RETIRADO' => $retirados,
        ];

        return view('dashboard', compact(
            'totalAlumnos',
            'totalCarnets',
            'estados',
            'porVencer',
            'porCarrera',
            'recientes'
        ));
    }
}