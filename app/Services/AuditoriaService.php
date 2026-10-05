<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditoriaService
{
    public static function registrar(string $accion, ?string $tabla = null, $registroId = null, string $descripcion = ''): void
    {
        try {
            $accionId = DB::table('auditoria_acciones')
                ->where('nombre', $accion)
                ->value('accion_id');

            if (!$accionId) {
                return;
            }

            DB::table('auditorias')->insert([
                'user_id' => auth()->id(),
                'accion_id' => $accionId,
                'tabla' => $tabla ?? '',
                'registro_id' => $registroId,
                'fecha' => now()->format('Y-m-d H:i:s'),
                'ip_address' => request()->ip(),
                'descripcion' => $descripcion,
            ]);
        } catch (\Throwable $e) {
            // Silenciar errores de auditoría
        }
    }
}