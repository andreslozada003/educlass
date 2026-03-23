<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordRecoveryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'nombre_estudiante',
        'nombre_normalizado',
        'docente_id',
        'estado',
        'mensaje_docente',
        'solicitado_en',
        'respondido_en',
    ];

    protected $casts = [
        'solicitado_en' => 'datetime',
        'respondido_en' => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}

