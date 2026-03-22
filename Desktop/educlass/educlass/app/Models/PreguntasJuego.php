<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PreguntasJuego - Preguntas para juegos educativos
 */
class PreguntasJuego extends Model
{
    use HasFactory;

    protected $table = 'preguntas_juego';

    protected $fillable = [
        'juego_id',
        'tipo',
        'enunciado',
        'opciones',
        'respuesta_correcta',
        'puntaje',
        'orden',
        'imagen_apoyo',
        'activo',
    ];

    protected $casts = [
        'opciones' => 'array',
        'respuesta_correcta' => 'array',
        'puntaje' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Relación con juego
     */
    public function juego()
    {
        return $this->belongsTo(Juego::class);
    }

    /**
     * Scope para preguntas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
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
        if (is_array($this->respuesta_correcta)) {
            return in_array($respuesta, $this->respuesta_correcta);
        }
        return strtolower(trim($respuesta)) === strtolower(trim($this->respuesta_correcta));
    }

    /**
     * Obtener opciones mezcladas (para quiz)
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
     * Verificar si es de opción múltiple
     */
    public function getEsOpcionMultipleAttribute(): bool
    {
        return $this->tipo === 'opcion_multiple';
    }

    /**
     * Verificar si es verdadero/falso
     */
    public function getEsVerdaderoFalsoAttribute(): bool
    {
        return $this->tipo === 'verdadero_falso';
    }

    /**
     * Verificar si es de emparejamiento
     */
    public function getEsEmparejamientoAttribute(): bool
    {
        return $this->tipo === 'emparejamiento';
    }

    /**
     * Verificar si es de ordenamiento
     */
    public function getEsOrdenamientoAttribute(): bool
    {
        return $this->tipo === 'ordenamiento';
    }
}
