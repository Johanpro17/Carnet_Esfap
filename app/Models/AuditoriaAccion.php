<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditoriaAccion extends Model
{
    use HasFactory;

    protected $table = 'auditoria_acciones';

    protected $primaryKey = 'accion_id';

    protected $fillable = ['nombre', 'descripcion'];

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'accion_id', 'accion_id');
    }
}