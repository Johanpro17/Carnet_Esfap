<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolUsuario extends Model
{
    protected $table = 'roles_usuario';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['user_id', 'rol_id'];
}