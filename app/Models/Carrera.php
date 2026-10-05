<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    protected $table = 'carreras';

    protected $primaryKey = 'carrera_id';

    protected $fillable = ['nombre', 'estado'];

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'carrera_id', 'carrera_id');
    }
}