<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CalificacionesPeriodo - Calificaciones por período académico
 */
class CalificacionesPeriodo extends Model
{
    use HasFactory;

    protected $table = 'calificaciones_periodo';

    protected $fillable = [
        'estudiante_id',
        'asignatura_id',
        'periodo',
        'promedio_juegos',
        'promedio_evaluaciones',
        'promedio_ponderado',
        'año_academico',
    ];

    protected $casts = [
        'periodo' => 'integer',
        'promedio_juegos' => 'decimal:2',
        'promedio_evaluaciones' => 'decimal:2',
        'promedio_ponderado' => 'decimal:2',
        'año_academico' => 'integer',
    ];

    /**
     * Relación con estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con asignatura
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    /**
     * Scope por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope por asignatura
     */
    public function scopePorAsignatura($query, $asignaturaId)
    {
        return $query->where('asignatura_id', $asignaturaId);
    }

    /**
     * Scope por período
     */
    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    /**
     * Scope por año académico
     */
    public function scopePorAnio($query, $anio)
    {
        return $query->where('año_academico', $anio);
    }

    /**
     * Calcular promedio ponderado automáticamente
     */
    public function calcularPromedioPonderado(): float
    {
        // Fórmula: Juegos 30% + Evaluaciones 70%
        $ponderado = ($this->promedio_juegos * 0.30) + ($this->promedio_evaluaciones * 0.70);
        return round($ponderado, 2);
    }

    /**
     * Actualizar y guardar promedio ponderado
     */
    public function actualizarPromedioPonderado(): void
    {
        $this->promedio_ponderado = $this->calcularPromedioPonderado();
        $this->save();
    }

    /**
     * Obtener nombre del período
     */
    public function getPeriodoNombreAttribute(): string
    {
        return "Período {$this->periodo}";
    }

    /**
     * Obtener clase CSS según promedio
     */
    public function getPromedioClaseAttribute(): string
    {
        $promedio = $this->promedio_ponderado;
        if ($promedio >= 4.5) return 'bg-green-100 text-green-700';
        if ($promedio >= 4.0) return 'bg-blue-100 text-blue-700';
        if ($promedio >= 3.5) return 'bg-yellow-100 text-yellow-700';
        if ($promedio >= 3.0) return 'bg-orange-100 text-orange-700';
        return 'bg-red-100 text-red-700';
    }

    /**
     * Verificar si aprobó el período
     */
    public function getAproboAttribute(): bool
    {
        return $this->promedio_ponderado >= 3.0;
    }

    /**
     * Obtener desempeño
     */
    public function getDesempenoAttribute(): string
    {
        $promedio = $this->promedio_ponderado;
        if ($promedio >= 4.5) return 'Sobresaliente';
        if ($promedio >= 4.0) return 'Notable';
        if ($promedio >= 3.5) return 'Bueno';
        if ($promedio >= 3.0) return 'Suficiente';
        return 'Insuficiente';
    }

    /**
     * Boot - Calcular promedio antes de guardar
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            $model->promedio_ponderado = $model->calcularPromedioPonderado();
        });
    }
}
