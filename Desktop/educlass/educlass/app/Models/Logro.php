<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Logro - Badges/Logros del sistema
 */
class Logro extends Model
{
    use HasFactory;

    protected $table = 'logros';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'criterio',
        'color',
        'puntos_bonus',
        'activo',
    ];

    protected $casts = [
        'criterio' => 'array',
        'puntos_bonus' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Relación con estudiantes que tienen el logro
     */
    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'logros_estudiantes', 'logro_id', 'estudiante_id')
            ->withPivot('fecha_obtenido', 'contexto')
            ->withTimestamps();
    }

    /**
     * Relación con logros de estudiantes
     */
    public function logrosEstudiantes()
    {
        return $this->hasMany(LogrosEstudiante::class);
    }

    /**
     * Scope para logros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Verificar si un estudiante tiene este logro
     */
    public function loTiene(int $estudianteId): bool
    {
        return $this->estudiantes()->where('estudiante_id', $estudianteId)->exists();
    }

    /**
     * Obtener estilos CSS
     */
    public function getEstilosAttribute(): array
    {
        return [
            'bg' => "background-color: {$this->color}",
            'text' => "color: {$this->color}",
            'border' => "border-color: {$this->color}",
        ];
    }

    /**
     * Obtener criterio formateado
     */
    public function getCriterioDescripcionAttribute(): string
    {
        $criterio = $this->criterio;
        
        if (isset($criterio['temas_completados'])) {
            return "Completar {$criterio['temas_completados']} tema(s)";
        }
        if (isset($criterio['evaluaciones_perfectas_consecutivas'])) {
            return "Obtener {$criterio['evaluaciones_perfectas_consecutivas']} evaluaciones perfectas seguidas";
        }
        if (isset($criterio['tiempo_maximo_segundos'])) {
            return "Completar un juego en menos de {$criterio['tiempo_maximo_segundos']} segundos";
        }
        if (isset($criterio['intentos_usados'])) {
            return "Usar {$criterio['intentos_usados']} intentos antes de lograrlo";
        }
        if (isset($criterio['asignatura_completada'])) {
            return "Completar una asignatura al 100%";
        }
        if (isset($criterio['posicion_ranking'])) {
            return "Entrar al top {$criterio['posicion_ranking']} del ranking";
        }
        
        return 'Criterio especial';
    }

    /**
     * Verificar si cumple criterio
     */
    public function verificarCriterio(User $estudiante, array $contexto = []): bool
    {
        $criterio = $this->criterio;
        
        // Verificar cada tipo de criterio
        if (isset($criterio['temas_completados'])) {
            $temasCompletados = $estudiante->progresoEstudiante()
                ->where('estado', 'completado')
                ->count();
            return $temasCompletados >= $criterio['temas_completados'];
        }
        
        if (isset($criterio['evaluaciones_perfectas_consecutivas'])) {
            // Lógica para verificar racha de evaluaciones perfectas
            $racha = $contexto['racha_perfecta'] ?? 0;
            return $racha >= $criterio['evaluaciones_perfectas_consecutivas'];
        }
        
        if (isset($criterio['tiempo_maximo_segundos'])) {
            $tiempo = $contexto['tiempo_segundos'] ?? PHP_INT_MAX;
            return $tiempo <= $criterio['tiempo_maximo_segundos'];
        }
        
        if (isset($criterio['intentos_usados'])) {
            $intentos = $contexto['intentos_usados'] ?? 0;
            return $intentos >= $criterio['intentos_usados'];
        }
        
        if (isset($criterio['asignatura_completada'])) {
            // Verificar si completó alguna asignatura al 100%
            return $contexto['asignatura_completada'] ?? false;
        }
        
        if (isset($criterio['posicion_ranking'])) {
            $posicion = $contexto['posicion_ranking'] ?? 999;
            return $posicion <= $criterio['posicion_ranking'];
        }
        
        return false;
    }

    /**
     * Otorgar logro a estudiante
     */
    public function otorgar(int $estudianteId, array $contexto = []): LogrosEstudiante
    {
        return $this->logrosEstudiantes()->create([
            'estudiante_id' => $estudianteId,
            'fecha_obtenido' => now(),
            'contexto' => $contexto,
        ]);
    }
}
