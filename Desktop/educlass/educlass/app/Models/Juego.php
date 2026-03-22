<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Juego - Gestión de juegos educativos
 */
class Juego extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'juegos';

    protected $fillable = [
        'tema_id',
        'tipo',
        'titulo',
        'descripcion',
        'configuracion',
        'dificultad',
        'intentos_maximos',
        'puntaje_base',
        'tiempo_limite_segundos',
        'activo',
    ];

    protected $casts = [
        'configuracion' => 'array',
        'dificultad' => 'integer',
        'intentos_maximos' => 'integer',
        'puntaje_base' => 'integer',
        'tiempo_limite_segundos' => 'integer',
        'activo' => 'boolean',
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
        return $this->hasMany(PreguntasJuego::class);
    }

    /**
     * Relación con preguntas activas ordenadas
     */
    public function preguntasActivas()
    {
        return $this->hasMany(PreguntasJuego::class)
            ->where('activo', true)
            ->orderBy('orden');
    }

    /**
     * Relación con intentos de estudiantes
     */
    public function intentos()
    {
        return $this->hasMany(IntentosJuego::class );
    }

    /**
     * Scope para juegos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Obtener configuración del tipo de juego
     */
    public function getTipoConfigAttribute(): array
    {
        return config("juegos.tipos.{$this->tipo}", []);
    }

    /**
     * Obtener nombre del tipo
     */
    public function getTipoNombreAttribute(): string
    {
        return $this->tipo_config['nombre'] ?? 'Juego';
    }

    /**
     * Obtener icono del tipo
     */
    public function getTipoIconoAttribute(): string
    {
        return $this->tipo_config['icono'] ?? '🎮';
    }

    /**
     * Obtener color del tipo
     */
    public function getTipoColorAttribute(): string
    {
        return $this->tipo_config['color'] ?? '#3B82F6';
    }

    /**
     * Obtener total de preguntas
     */
    public function getTotalPreguntasAttribute(): int
    {
        return $this->preguntasActivas()->count();
    }

    /**
     * Obtener puntaje máximo posible
     */
    public function getPuntajeMaximoAttribute(): int
    {
        return $this->preguntasActivas()->sum('puntaje');
    }

    /**
     * Verificar si tiene tiempo límite
     */
    public function getTieneTiempoLimiteAttribute(): bool
    {
        return !is_null($this->tiempo_limite_segundos) && $this->tiempo_limite_segundos > 0;
    }

    /**
     * Formatear tiempo límite
     */
    public function getTiempoLimiteFormateadoAttribute(): string
    {
        if (!$this->tiene_tiempo_limite) {
            return 'Sin límite';
        }
        $minutos = floor($this->tiempo_limite_segundos / 60);
        $segundos = $this->tiempo_limite_segundos % 60;
        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    /**
     * Obtener intentos restantes para un estudiante
     */
    public function intentosRestantes(int $estudianteId): int
    {
        $intentosUsados = $this->intentos()
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->count();
        
        return max(0, $this->intentos_maximos - $intentosUsados);
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
        return $this->intentos()
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->max('puntaje_obtenido');
    }

    /**
     * Obtener URL para jugar
     */
    public function getUrlJugarAttribute(): string
    {
        return route('estudiante.juegos.jugar', $this->id);
    }

    /**
     * Obtener URL de preview (para docentes)
     */
    public function getUrlPreviewAttribute(): string
    {
        return route('docente.juegos.preview', $this->id);
    }
}
