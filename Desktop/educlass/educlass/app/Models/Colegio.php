<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Colegio - Gestión de instituciones educativas
 */
class Colegio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'colegios';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'logo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación con usuarios del colegio
     */
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con estudiantes
     */
    public function estudiantes()
    {
        return $this->hasMany(User::class)->where('tipo', 'estudiante');
    }

    /**
     * Relación con docentes
     */
    public function docentes()
    {
        return $this->hasMany(User::class)->where('tipo', 'docente');
    }

    /**
     * Scope para colegios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener URL del logo
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/colegios/' . $this->logo);
        }
        return asset('images/default-colegio.png');
    }
}
