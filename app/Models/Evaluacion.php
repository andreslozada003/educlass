<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Evaluacion - Gestión de evaluaciones/exámenes
 */
class Evaluacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'tema_id',
        'titulo',
        'descripcion',
        'tipo',
        'tiempo_limite_minutos',
        'puntaje_total',
        'intentos_permitidos',
        'umbral_aprobacion',
        'activa',
    ];

    protected $casts = [
        'tiempo_limite_minutos' => 'integer',
        'puntaje_total' => 'integer',
        'intentos_permitidos' => 'integer',
        'umbral_aprobacion' => 'integer',
        'activa' => 'boolean',
    ];

    /**
     * Relación con tema
     */
    public function tema()
    {
        return $this->belongsTo(Tema::class);
    }

    /**
     * Relación con preguntas
     */
    public function preguntas()
    {
        return $this->hasMany(PreguntasEvaluacion::class);
    }

    /**
     * Relación con preguntas ordenadas
     */
    public function preguntasOrdenadas()
    {
        return $this->hasMany(PreguntasEvaluacion::class)->orderBy('orden');
    }

    /**
     * Relación con resultados
     */
    public function resultados()
    {
        return $this->hasMany(ResultadosEvaluacion::class);
    }

    /**
     * Scope para evaluaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Obtener nombre del tipo
     */
    public function getTipoNombreAttribute(): string
    {
        $tipos = [
            'diagnostica' => 'Diagnóstica',
            'formativa' => 'Formativa',
            'sumativa' => 'Sumativa',
        ];
        return $tipos[$this->tipo] ?? 'Evaluación';
    }

    /**
     * Obtener color del tipo
     */
    public function getTipoColorAttribute(): string
    {
        $colores = [
            'diagnostica' => '#8B5CF6',
            'formativa' => '#3B82F6',
            'sumativa' => '#EF4444',
        ];
        return $colores[$this->tipo] ?? '#6B7280';
    }

    /**
     * Obtener total de preguntas
     */
    public function getTotalPreguntasAttribute(): int
    {
        return $this->preguntas()->count();
    }

    /**
     * Calcular puntaje total basado en preguntas
     */
    public function calcularPuntajeTotal(): int
    {
        return $this->preguntas()->sum('puntaje');
    }

    /**
     * Verificar si tiene tiempo límite
     */
    public function getTieneTiempoLimiteAttribute(): bool
    {
        return $this->tiempo_limite_minutos > 0;
    }

    /**
     * Formatear tiempo límite
     */
    public function getTiempoLimiteFormateadoAttribute(): string
    {
        if (!$this->tiene_tiempo_limite) {
            return 'Sin límite';
        }
        return $this->tiempo_limite_minutos . ' min';
    }

    /**
     * Obtener intentos restantes para un estudiante
     */
    public function intentosRestantes(int $estudianteId): int
    {
        $intentosUsados = $this->resultados()
            ->where('estudiante_id', $estudianteId)
            ->count();
        
        return max(0, $this->intentos_permitidos - $intentosUsados);
    }

    /**
     * Verificar si estudiante tiene intentos disponibles
     */
    public function tieneIntentosDisponibles(int $estudianteId): bool
    {
        return $this->intentosRestantes($estudianteId) > 0;
    }

    /**
     * Obtener mejor puntaje de un estudiante
     */
    public function mejorPuntaje(int $estudianteId): ?int
    {
        return $this->resultados()
            ->where('estudiante_id', $estudianteId)
            ->max('puntaje_obtenido');
    }

    /**
     * Verificar si estudiante aprobó
     */
    public function estudianteAprobo(int $estudianteId): bool
    {
        return $this->resultados()
            ->where('estudiante_id', $estudianteId)
            ->where('aprobado', true)
            ->exists();
    }

    /**
     * Obtener último resultado de un estudiante
     */
    public function ultimoResultado(int $estudianteId): ?ResultadosEvaluacion
    {
        return $this->resultados()
            ->where('estudiante_id', $estudianteId)
            ->latest('fecha_realizacion')
            ->first();
    }

    /**
     * Obtener URL para realizar evaluación
     */
    public function getUrlRealizarAttribute(): string
    {
        return route('estudiante.evaluaciones.realizar', $this->id);
    }

    /**
     * Obtener promedio de puntajes
     */
    public function getPromedioPuntajesAttribute(): float
    {
        return $this->resultados()->avg('puntaje_obtenido') ?? 0;
    }

    /**
     * Obtener tasa de aprobación
     */
    public function getTasaAprobacionAttribute(): float
    {
        $total = $this->resultados()->count();
        if ($total === 0) {
            return 0;
        }
        $aprobados = $this->resultados()->where('aprobado', true)->count();
        return round(($aprobados / $total) * 100, 2);
    }
}
