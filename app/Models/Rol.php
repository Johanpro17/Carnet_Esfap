<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'rol_id';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'roles_usuario', 'user_id', 'rol_id')
            ->withPivot('fecha_asignacion');
    }
}