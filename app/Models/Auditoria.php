<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $primaryKey = 'auditoria_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'accion_id',
        'tabla',
        'registro_id',
        'fecha',
        'ip_address',
        'descripcion',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'user_id');
    }

    public function accion(): BelongsTo
    {
        return $this->belongsTo(AuditoriaAccion::class, 'accion_id', 'accion_id');
    }
}