<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Ranking - Tablas de clasificación
 */
class Ranking extends Model
{
    use HasFactory;

    protected $table = 'rankings';

    protected $fillable = [
        'estudiante_id',
        'asignatura_id',
        'categoria',
        'posicion',
        'puntaje_total',
        'nivel_alcanzado',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntaje_total' => 'integer',
        'nivel_alcanzado' => 'integer',
        'fecha_actualizacion' => 'datetime',
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
     * Scope por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope top ranking
     */
    public function scopeTop($query, $limite = 10)
    {
        return $query->orderBy('posicion')->limit($limite);
    }

    /**
     * Scope general (sin asignatura específica)
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('asignatura_id');
    }

    /**
     * Obtener nombre de la categoría
     */
    public function getCategoriaNombreAttribute(): string
    {
        $categorias = [
            'juegos' => 'Juegos',
            'evaluaciones' => 'Evaluaciones',
            'temas' => 'Temas Completados',
            'general' => 'General',
        ];
        return $categorias[$this->categoria] ?? 'Ranking';
    }

    /**
     * Obtener icono de la categoría
     */
    public function getCategoriaIconoAttribute(): string
    {
        $iconos = [
            'juegos' => '🎮',
            'evaluaciones' => '📝',
            'temas' => '📚',
            'general' => '🏆',
        ];
        return $iconos[$this->categoria] ?? '📊';
    }

    /**
     * Obtener medalla según posición
     */
    public function getMedallaAttribute(): string
    {
        return match ($this->posicion) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '🏅',
        };
    }

    /**
     * Verificar si está en top 3
     */
    public function getEnPodioAttribute(): bool
    {
        return $this->posicion <= 3;
    }

    /**
     * Obtener nombre del nivel
     */
    public function getNivelNombreAttribute(): string
    {
        $niveles = config('gamificacion.niveles');
        return $niveles[$this->nivel_alcanzado]['nombre'] ?? 'Desconocido';
    }

    /**
     * Obtener icono del nivel
     */
    public function getNivelIconoAttribute(): string
    {
        $niveles = config('gamificacion.niveles');
        return $niveles[$this->nivel_alcanzado]['icono'] ?? '⭐';
    }

    /**
     * Actualizar posición
     */
    public function actualizarPosicion(int $nuevaPosicion): void
    {
        $this->posicion = $nuevaPosicion;
        $this->fecha_actualizacion = now();
        $this->save();
    }

    /**
     * Actualizar puntaje
     */
    public function actualizarPuntaje(int $nuevoPuntaje): void
    {
        $this->puntaje_total = $nuevoPuntaje;
        $this->fecha_actualizacion = now();
        $this->save();
    }

    /**
     * Recalcular ranking para una categoría
     */
    public static function recalcularRanking(string $categoria, ?int $asignaturaId = null): void
    {
        $query = static::porCategoria($categoria);
        
        if ($asignaturaId) {
            $query->porAsignatura($asignaturaId);
        } else {
            $query->general();
        }
        
        $rankings = $query->orderBy('puntaje_total', 'desc')->get();
        
        foreach ($rankings as $index => $ranking) {
            $ranking->actualizarPosicion($index + 1);
        }
    }
}
