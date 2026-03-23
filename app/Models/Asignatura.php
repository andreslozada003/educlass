<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Asignatura - Gestión de asignaturas educativas
 */
class Asignatura extends Model
{
    use HasFactory;

    protected $table = 'asignaturas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'slug',
        'icono',
        'color_primario',
        'color_secundario',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Relación con temas
     */
    public function temas()
    {
        return $this->hasMany(Tema::class);
    }

    /**
     * Relación con temas activos
     */
    public function temasActivos()
    {
        return $this->hasMany(Tema::class)->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación con calificaciones
     */
    public function calificacionesPeriodo()
    {
        return $this->hasMany(CalificacionesPeriodo::class);
    }

    /**
     * Relación con rankings
     */
    public function rankings()
    {
        return $this->hasMany(Ranking::class);
    }

    /**
     * Scope para asignaturas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Obtener estilos CSS dinámicos
     */
    public function getEstilosAttribute(): array
    {
        return [
            'bg' => "background-color: {$this->color_primario}",
            'text' => "color: {$this->color_primario}",
            'border' => "border-color: {$this->color_primario}",
            'bg_light' => "background-color: {$this->color_secundario}",
        ];
    }

    /**
     * Obtener total de temas
     */
    public function getTotalTemasAttribute(): int
    {
        return $this->temas()->count();
    }

    /**
     * Obtener URL amigable
     */
    public function getUrlAttribute(): string
    {
        return route('estudiante.asignaturas.show', $this->slug);
    }

    /**
     * Usar slug para generación/resolución de rutas con el modelo.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
