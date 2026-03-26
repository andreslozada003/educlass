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
     * Relacion con estudiantes que tienen el logro
     */
    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'logros_estudiantes', 'logro_id', 'estudiante_id')
            ->withPivot('fecha_obtenido', 'contexto')
            ->withTimestamps();
    }

    /**
     * Relacion con logros de estudiantes
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
        $criterio = $this->criterio ?? [];
        $asignatura = !empty($criterio['asignatura_slug'])
            ? ' en ' . ucfirst((string) $criterio['asignatura_slug'])
            : '';

        return match (true) {
            isset($criterio['juegos_completados']) => "Completar {$criterio['juegos_completados']} juego(s)",
            isset($criterio['juegos_en_un_dia']) => "Completar {$criterio['juegos_en_un_dia']} juegos en un dia",
            isset($criterio['temas_completados']) => "Completar {$criterio['temas_completados']} tema(s)",
            isset($criterio['temas_por_asignatura']) => "Completar {$criterio['temas_por_asignatura']} tema(s) en cada asignatura",
            isset($criterio['temas_completados_asignatura']) => "Completar {$criterio['temas_completados_asignatura']} tema(s){$asignatura}",
            isset($criterio['evaluaciones_aprobadas']) => "Aprobar {$criterio['evaluaciones_aprobadas']} evaluacion(es)",
            isset($criterio['evaluaciones_aprobadas_asignatura']) => "Aprobar {$criterio['evaluaciones_aprobadas_asignatura']} evaluacion(es){$asignatura}",
            isset($criterio['evaluaciones_perfectas_consecutivas']) => "Obtener {$criterio['evaluaciones_perfectas_consecutivas']} evaluaciones perfectas seguidas",
            isset($criterio['respuestas_correctas_total']) => "Acumular {$criterio['respuestas_correctas_total']} respuestas correctas",
            isset($criterio['racha_correctas']) => "Lograr {$criterio['racha_correctas']} aciertos seguidos",
            isset($criterio['tiempo_maximo_segundos']) => "Completar un juego en menos de {$criterio['tiempo_maximo_segundos']} segundos",
            isset($criterio['numero_intento_minimo']) => "Intentarlo al menos {$criterio['numero_intento_minimo']} veces sin rendirte",
            isset($criterio['mejora_puntaje']) => 'Superar tu mejor puntaje anterior',
            isset($criterio['juego_perfecto']) => 'Terminar un juego sin errores',
            isset($criterio['evaluacion_perfecta']) => 'Obtener 100% en una evaluacion',
            isset($criterio['asignatura_completada']) => "Completar una asignatura al 100%{$asignatura}",
            isset($criterio['nivel_actual_minimo']) => "Alcanzar el nivel {$criterio['nivel_actual_minimo']}{$asignatura}",
            isset($criterio['dias_consecutivos']) => "Aprender durante {$criterio['dias_consecutivos']} dias seguidos",
            isset($criterio['logros_obtenidos']) => "Desbloquear {$criterio['logros_obtenidos']} logros",
            isset($criterio['juego_tipo_completado']) => "Completar un juego tipo {$criterio['juego_tipo_completado']}",
            isset($criterio['posicion_ranking']) => "Entrar al top {$criterio['posicion_ranking']} del ranking",
            default => 'Criterio especial',
        };
    }

    /**
     * Verificar si cumple criterio
     */
    public function verificarCriterio(User $estudiante, array $contexto = []): bool
    {
        $criterio = $this->criterio ?? [];

        if (isset($criterio['temas_completados'])) {
            return $estudiante->progresoEstudiante()
                ->where('estado', 'completado')
                ->count() >= $criterio['temas_completados'];
        }

        if (isset($criterio['posicion_ranking'])) {
            return ($contexto['posicion_ranking'] ?? 999) <= $criterio['posicion_ranking'];
        }

        if (isset($criterio['tiempo_maximo_segundos'])) {
            return ($contexto['tiempo_segundos'] ?? PHP_INT_MAX) <= $criterio['tiempo_maximo_segundos'];
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
