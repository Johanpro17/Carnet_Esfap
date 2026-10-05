<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alumnos';

    protected $primaryKey = 'alumno_id';

    protected $fillable = [
        'carrera_id',
        'codigo_alumno',
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'correo',
        'direccion',
        'foto',
        'estado',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_registro' => 'datetime',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'carrera_id');
    }

    public function carnets(): HasMany
    {
        return $this->hasMany(Carnet::class, 'alumno_id', 'alumno_id');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'alumno_id', 'alumno_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }

    public function getFotoUrlAttribute(): ?string
    {
        $foto = $this->foto;
        if ($foto && file_exists(public_path('uploads/' . $foto))) {
            return asset('uploads/' . $foto);
        }
        return null;
    }

    public function carteActual(): ?Carnet
    {
        return $this->carnets()->latest('fecha_emision')->first();
    }
}