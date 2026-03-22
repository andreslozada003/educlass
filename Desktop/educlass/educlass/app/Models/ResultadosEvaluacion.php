<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResultadosEvaluacion - Resultados de evaluaciones
 */
class ResultadosEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'resultados_evaluaciones';

    protected $fillable = [
        'estudiante_id',
        'evaluacion_id',
        'puntaje_obtenido',
        'respuestas',
        'tiempo_empleado_minutos',
        'aprobado',
        'fecha_realizacion',
        'ip_address',
    ];

    protected $casts = [
        'respuestas' => 'array',
        'puntaje_obtenido' => 'integer',
        'tiempo_empleado_minutos' => 'integer',
        'aprobado' => 'boolean',
        'fecha_realizacion' => 'datetime',
    ];

    /**
     * Relación con estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con evaluación
     */
    public function evaluacion()
    {
         return $this->belongsTo(\App\Models\Evaluacion::class, 'evaluacion_id');
    }

    /**
     * Scope por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope por evaluación
     */
    public function scopePorEvaluacion($query, $evaluacionId)
    {
        return $query->where('evaluacion_id', $evaluacionId);
    }

    /**
     * Scope para aprobados
     */
    public function scopeAprobados($query)
    {
        return $query->where('aprobado', true);
    }

    /**
     * Scope para reprobados
     */
    public function scopeReprobados($query)
    {
        return $query->where('aprobado', false);
    }

    /**
     * Scope ordenado por fecha
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha_realizacion', 'desc');
    }

    /**
     * Obtener tiempo formateado
     */
    public function getTiempoFormateadoAttribute(): string
    {
        return $this->tiempo_empleado_minutos . ' min';
    }

    /**
     * Obtener porcentaje obtenido
     */
    public function getPorcentajeObtenidoAttribute(): float
    {
        if (!$this->evaluacion || $this->evaluacion->puntaje_total === 0) {
            return 0;
        }
        return round(($this->puntaje_obtenido / $this->evaluacion->puntaje_total) * 100, 2);
    }

    /**
     * Obtener respuestas correctas
     */
    public function getRespuestasCorrectasAttribute(): int
    {
        if (!$this->respuestas || !$this->evaluacion) {
            return 0;
        }
        
        $correctas = 0;
        $preguntas = $this->evaluacion->preguntasOrdenadas;
        
        foreach ($this->respuestas as $preguntaId => $respuesta) {
            $pregunta = $preguntas->firstWhere('id', $preguntaId);
            if ($pregunta && $pregunta->verificarRespuesta($respuesta)) {
                $correctas++;
            }
        }
        
        return $correctas;
    }

    /**
     * Obtener total de preguntas respondidas
     */
    public function getTotalRespondidasAttribute(): int
    {
        return count($this->respuestas ?? []);
    }

    /**
     * Obtener clase CSS según resultado
     */
    public function getResultadoClaseAttribute(): string
    {
        return $this->aprobado 
            ? 'bg-green-100 text-green-700 border-green-300' 
            : 'bg-red-100 text-red-700 border-red-300';
    }

    /**
     * Obtener icono según resultado
     */
    public function getResultadoIconoAttribute(): string
    {
        return $this->aprobado ? '✅' : '❌';
    }

    /**
     * Obtener mensaje según resultado
     */
    public function getResultadoMensajeAttribute(): string
    {
        if ($this->aprobado) {
            return '¡Felicitaciones! Has aprobado la evaluación.';
        }
        return 'No has alcanzado el puntaje mínimo de aprobación.';
    }

    /**
     * Calcular nota en escala 0-5
     */
    public function getNotaEscala5Attribute(): float
    {
        $porcentaje = $this->porcentaje_obtenido;
        if ($porcentaje >= 90) return 5.0;
        if ($porcentaje >= 80) return 4.5;
        if ($porcentaje >= 70) return 4.0;
        if ($porcentaje >= 60) return 3.5;
        if ($porcentaje >= 50) return 3.0;
        if ($porcentaje >= 40) return 2.5;
        if ($porcentaje >= 30) return 2.0;
        return 1.0;
    }

    /**
     * Obtener desempeño
     */
    public function getDesempenoAttribute(): string
    {
        $porcentaje = $this->porcentaje_obtenido;
        if ($porcentaje >= 90) return 'Sobresaliente';
        if ($porcentaje >= 80) return 'Notable';
        if ($porcentaje >= 70) return 'Bueno';
        if ($porcentaje >= 60) return 'Suficiente';
        if ($porcentaje >= 50) return 'Regular';
        return 'Insuficiente';
    }
}
