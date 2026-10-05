<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioPersonal extends Model
{
    protected $table = 'usuario_personal';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['user_id', 'personal_id'];
}