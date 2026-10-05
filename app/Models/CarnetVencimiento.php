<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarnetVencimiento extends Model
{
    use HasFactory;

    protected $table = 'carnet_vencimientos';

    protected $primaryKey = 'carnet_vencimiento_id';

    public $timestamps = false;

    protected $fillable = ['carnet_id', 'fecha_vigencia', 'fecha_emision', 'fecha_registro'];

    protected $casts = [
        'fecha_vigencia' => 'date',
        'fecha_emision' => 'date',
    ];

    public function carnet(): BelongsTo
    {
        return $this->belongsTo(Carnet::class, 'carnet_id', 'carnet_id');
    }
}