<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo IntentosJuego - Registro de intentos en juegos
 */
class IntentosJuego extends Model
{
    use HasFactory;

    protected $table = 'intentos_juegos';

    protected $fillable = [
        'estudiante_id',
        'juego_id',
        'puntaje_obtenido',
        'respuestas',
        'duracion_segundos',
        'numero_intento',
        'completado',
        'fecha_intento',
    ];

    protected $casts = [
        'respuestas' => 'array',
        'puntaje_obtenido' => 'integer',
        'duracion_segundos' => 'integer',
        'numero_intento' => 'integer',
        'completado' => 'boolean',
        'fecha_intento' => 'datetime',
    ];

    /**
     * Relación con estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con juego
     */
     public function juego()
    {
        return $this->belongsTo(\App\Models\Juego::class, 'juego_id');
    }

    /**
     * Scope por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope por juego
     */
    public function scopePorJuego($query, $juegoId)
    {
        return $query->where('juego_id', $juegoId);
    }

    /**
     * Scope para intentos completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('completado', true);
    }

    /**
     * Scope para mejores puntajes
     */
    public function scopeMejoresPuntajes($query)
    {
        return $query->orderBy('puntaje_obtenido', 'desc');
    }

    /**
     * Obtener duración formateada
     */
    public function getDuracionFormateadaAttribute(): string
    {
        $minutos = floor($this->duracion_segundos / 60);
        $segundos = $this->duracion_segundos % 60;
        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    /**
     * Calcular bonificación por tiempo
     */
    public function calcularBonificacionTiempo(int $tiempoLimiteSegundos): int
    {
        if ($tiempoLimiteSegundos <= 0) {
            return 0;
        }
        $tiempoRestante = max(0, $tiempoLimiteSegundos - $this->duracion_segundos);
        $porcentajeRestante = ($tiempoRestante / $tiempoLimiteSegundos) * 100;
        return (int) ($this->puntaje_obtenido * ($porcentajeRestante / 100) * 0.2);
    }

    /**
     * Obtener puntaje final con bonificaciones
     */
    public function getPuntajeFinalAttribute(): int
    {
        $bonificacion = 0;
        if ($this->juego && $this->juego->tiene_tiempo_limite) {
            $bonificacion = $this->calcularBonificacionTiempo($this->juego->tiempo_limite_segundos);
        }
        return $this->puntaje_obtenido + $bonificacion;
    }

    /**
     * Verificar si es el mejor intento del estudiante
     */
    public function getEsMejorIntentoAttribute(): bool
    {
        $mejorPuntaje = static::where('estudiante_id', $this->estudiante_id)
            ->where('juego_id', $this->juego_id)
            ->where('completado', true)
            ->max('puntaje_obtenido');
        
        return $this->puntaje_obtenido >= $mejorPuntaje;
    }

    /**
     * Obtener respuestas correctas
     */
    public function getRespuestasCorrectasAttribute(): int
    {
        if (!$this->respuestas || !$this->juego) {
            return 0;
        }
        
        $correctas = 0;
        $preguntas = $this->juego->preguntasActivas;
        
        foreach ($this->respuestas as $preguntaId => $respuesta) {
            $pregunta = $preguntas->firstWhere('id', $preguntaId);
            if ($pregunta && $pregunta->verificarRespuesta($respuesta)) {
                $correctas++;
            }
        }
        
        return $correctas;
    }

    /**
     * Obtener total de respuestas
     */
    public function getTotalRespuestasAttribute(): int
    {
        return count($this->respuestas ?? []);
    }

    /**
     * Obtener porcentaje de aciertos
     */
    public function getPorcentajeAciertosAttribute(): float
    {
        $total = $this->total_respuestas;
        if ($total === 0) {
            return 0;
        }
        return round(($this->respuestas_correctas / $total) * 100, 2);
    }
    
}
