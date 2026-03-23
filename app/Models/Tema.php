<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Tema - Gestión de temas/contenido educativo
 */
class Tema extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'temas';

    protected $fillable = [
        'asignatura_id',
        'titulo',
        'slug',
        'contenido',
        'dificultad',
        'periodo_academico',
        'orden',
        'imagen_destacada',
        'video_url',
        'tiempo_estimado_minutos',
        'activo',
        'docente_creador_id',
    ];

    protected $casts = [
        'dificultad' => 'integer',
        'periodo_academico' => 'integer',
        'orden' => 'integer',
        'tiempo_estimado_minutos' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Relación con asignatura
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    /**
     * Relación con docente creador
     */
    public function docenteCreador()
    {
        return $this->belongsTo(User::class, 'docente_creador_id');
    }

    /**
     * Relación con juegos
     */
    public function juegos()
    {
        return $this->hasMany(Juego::class);
    }

    /**
     * Relación con juegos activos
     */
    public function juegosActivos()
    {
        return $this->hasMany(Juego::class)->where('activo', true);
    }

    /**
     * Relación con evaluaciones
     */
    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class);
    }

    /**
     * Relación con evaluaciones activas
     */
    public function evaluacionesActivas()
    {
        return $this->hasMany(Evaluacion::class)->where('activa', true);
    }

    /**
     * Relación con progreso de estudiantes
     */
    public function progresoEstudiantes()
    {
        return $this->hasMany(ProgresoEstudiante::class);
    }

    /**
     * Scope para temas activos
     */
    public function scopeActivos($query)
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
     * Scope por período académico
     */
    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo_academico', $periodo);
    }

    /**
     * Scope por dificultad
     */
    public function scopePorDificultad($query, $dificultad)
    {
        return $query->where('dificultad', $dificultad);
    }

    /**
     * Obtener nombre del nivel
     */
    public function getNivelNombreAttribute(): string
    {
        $niveles = config('gamificacion.niveles');
        return $niveles[$this->dificultad]['nombre'] ?? 'Desconocido';
    }

    /**
     * Obtener icono del nivel
     */
    public function getNivelIconoAttribute(): string
    {
        $niveles = config('gamificacion.niveles');
        return $niveles[$this->dificultad]['icono'] ?? '⭐';
    }

    /**
     * Obtener color del nivel
     */
    public function getNivelColorAttribute(): string
    {
        $niveles = config('gamificacion.niveles');
        return $niveles[$this->dificultad]['color'] ?? '#3B82F6';
    }

    /**
     * Obtener URL de imagen destacada
     */
    public function getImagenUrlAttribute(): string
    {
        if ($this->imagen_destacada) {
            return asset('storage/' . ltrim($this->imagen_destacada, '/'));
        }
        return asset('images/default-tema.png');
    }

    /**
     * Obtener URL embebible del video cuando el proveedor es soportado.
     */
    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        $url = trim($this->video_url);
        $partes = parse_url($url);

        if (!$partes || empty($partes['host'])) {
            return null;
        }

        $host = strtolower($partes['host']);
        $path = trim($partes['path'] ?? '', '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            return $path ? 'https://www.youtube.com/embed/' . $path : null;
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch' && !empty($partes['query'])) {
                parse_str($partes['query'], $query);
                if (!empty($query['v'])) {
                    return 'https://www.youtube.com/embed/' . $query['v'];
                }
            }

            if (str_starts_with($path, 'embed/')) {
                return 'https://www.youtube.com/' . $path;
            }

            if (str_starts_with($path, 'shorts/')) {
                $videoId = substr($path, strlen('shorts/'));
                return $videoId ? 'https://www.youtube.com/embed/' . $videoId : null;
            }
        }

        return null;
    }

    /**
     * Obtener tema anterior
     */
    public function anterior()
    {
        return static::where('asignatura_id', $this->asignatura_id)
            ->where('orden', '<', $this->orden)
            ->where('activo', true)
            ->orderBy('orden', 'desc')
            ->first();
    }

    /**
     * Obtener tema siguiente
     */
    public function siguiente()
    {
        return static::where('asignatura_id', $this->asignatura_id)
            ->where('orden', '>', $this->orden)
            ->where('activo', true)
            ->orderBy('orden')
            ->first();
    }

    /**
     * Verificar si tiene juego
     */
    public function getTieneJuegoAttribute(): bool
    {
        return $this->juegosActivos()->exists();
    }

    /**
     * Verificar si tiene evaluación
     */
    public function getTieneEvaluacionAttribute(): bool
    {
        return $this->evaluacionesActivas()->exists();
    }

    /**
     * Obtener el primer juego activo
     */
    public function getJuegoPrincipalAttribute(): ?Juego
    {
        return $this->juegosActivos()->first();
    }

    /**
     * Obtener la primera evaluación activa
     */
    public function getEvaluacionPrincipalAttribute(): ?Evaluacion
    {
        return $this->evaluacionesActivas()->first();
    }

    /**
     * Usar slug para generación/resolución de rutas con el modelo.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
