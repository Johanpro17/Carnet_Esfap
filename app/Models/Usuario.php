<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'alumno_id',
        'username',
        'password',
        'nombres',
        'apellidos',
        'correo',
        'estado',
        'ultimo_login',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'ultimo_login' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_usuario', 'user_id', 'rol_id')
            ->withPivot('fecha_asignacion');
    }

    public function personal(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'usuario_personal', 'user_id', 'personal_id')
            ->withPivot('fecha_asignacion');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }

    public function tieneRol(string $nombre): bool
    {
        return $this->roles()->where('nombre', $nombre)->exists();
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('ADMIN');
    }
}