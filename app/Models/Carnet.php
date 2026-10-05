<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carnet extends Model
{
    use HasFactory;

    protected $table = 'carnets';

    protected $primaryKey = 'carnet_id';

    protected $fillable = [
        'alumno_id',
        'codigo',
        'token_qr',
        'periodo',
        'semestre',
        'estado',
        'fecha_emision',
    ];

    protected $casts = [
        'periodo' => 'integer',
        'semestre' => 'integer',
        'fecha_emision' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }

    public function vencimientos(): HasMany
    {
        return $this->hasMany(CarnetVencimiento::class, 'carnet_id', 'carnet_id');
    }

    public function vigenciaActual(): ?string
    {
        return $this->vencimientos()->latest('fecha_vigencia')->value('fecha_vigencia');
    }

    public function estaVencido(): bool
    {
        $vigencia = $this->vigenciaActual();
        return $vigencia !== null && $vigencia < now()->toDateString();
    }
}