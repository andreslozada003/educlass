<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PreguntasEvaluacion - Preguntas para evaluaciones
 */
class PreguntasEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'preguntas_evaluacion';

    protected $fillable = [
        'evaluacion_id',
        'tipo',
        'enunciado',
        'opciones',
        'respuesta_correcta',
        'puntaje',
        'orden',
        'imagen_apoyo',
    ];

    protected $casts = [
        'opciones' => 'array',
        'puntaje' => 'integer',
        'orden' => 'integer',
    ];

    /**
     * Relación con evaluación
     */
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Verificar respuesta
     */
    public function verificarRespuesta($respuesta): bool
    {
        $respuestaCorrecta = $this->respuesta_correcta;
        
        switch ($this->tipo) {
            case 'multiple':
            case 'vf':
                return $respuesta === $respuestaCorrecta;
            
            case 'corta':
                $respuestasPermitidas = is_array($respuestaCorrecta) 
                    ? $respuestaCorrecta 
                    : [$respuestaCorrecta];
                return in_array(strtolower(trim($respuesta)), array_map('strtolower', array_map('trim', $respuestasPermitidas)));
            
            case 'emparejamiento':
                if (!is_array($respuesta) || !is_array($respuestaCorrecta)) {
                    return false;
                }
                return $respuesta == $respuestaCorrecta;
            
            default:
                return false;
        }
    }

    /**
     * Obtener opciones mezcladas
     */
    public function getOpcionesMezcladasAttribute(): array
    {
        if (!$this->opciones) {
            return [];
        }
        $opciones = $this->opciones;
        shuffle($opciones);
        return $opciones;
    }

    /**
     * Obtener nombre del tipo
     */
    public function getTipoNombreAttribute(): string
    {
        $tipos = [
            'multiple' => 'Opción Múltiple',
            'vf' => 'Verdadero/Falso',
            'corta' => 'Respuesta Corta',
            'emparejamiento' => 'Emparejamiento',
        ];
        return $tipos[$this->tipo] ?? 'Pregunta';
    }

    /**
     * Verificar si es de opción múltiple
     */
    public function getEsOpcionMultipleAttribute(): bool
    {
        return $this->tipo === 'multiple';
    }

    /**
     * Verificar si es verdadero/falso
     */
    public function getEsVerdaderoFalsoAttribute(): bool
    {
        return $this->tipo === 'vf';
    }

    /**
     * Verificar si es respuesta corta
     */
    public function getEsRespuestaCortaAttribute(): bool
    {
        return $this->tipo === 'corta';
    }

    /**
     * Verificar si es de emparejamiento
     */
    public function getEsEmparejamientoAttribute(): bool
    {
        return $this->tipo === 'emparejamiento';
    }

    /**
     * Obtener URL de imagen de apoyo
     */
    public function getImagenUrlAttribute(): ?string
    {
        if ($this->imagen_apoyo) {
            return asset('storage/preguntas/' . $this->imagen_apoyo);
        }
        return null;
    }
}
