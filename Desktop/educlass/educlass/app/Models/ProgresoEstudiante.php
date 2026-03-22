<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ProgresoEstudiante - Seguimiento de progreso de estudiantes
 */
class ProgresoEstudiante extends Model
{
    use HasFactory;

    protected $table = 'progreso_estudiantes';

    protected $fillable = [
        'estudiante_id',
        'tema_id',
        'estado',
        'porcentaje_lectura',
        'fecha_inicio',
        'fecha_completado',
    ];

    protected $casts = [
        'porcentaje_lectura' => 'integer',
        'fecha_inicio' => 'datetime',
        'fecha_completado' => 'datetime',
    ];

    /**
     * Relación con estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con tema
     */
    public function tema()
    {
        return $this->belongsTo(Tema::class);
    }

    /**
     * Scope por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope por tema
     */
    public function scopePorTema($query, $temaId)
    {
        return $query->where('tema_id', $temaId);
    }

    /**
     * Scope por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para temas completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para temas disponibles o en progreso
     */
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['disponible', 'en_progreso', 'completado']);
    }

    /**
     * Verificar si está bloqueado
     */
    public function getEstaBloqueadoAttribute(): bool
    {
        return $this->estado === 'bloqueado';
    }

    /**
     * Verificar si está disponible
     */
    public function getEstaDisponibleAttribute(): bool
    {
        return $this->estado === 'disponible';
    }

    /**
     * Verificar si está en progreso
     */
    public function getEstaEnProgresoAttribute(): bool
    {
        return $this->estado === 'en_progreso';
    }

    /**
     * Verificar si está completado
     */
    public function getEstaCompletadoAttribute(): bool
    {
        return $this->estado === 'completado';
    }

    /**
     * Marcar como disponible
     */
    public function marcarDisponible(): void
    {
        $this->estado = 'disponible';
        $this->save();
    }

    /**
     * Marcar como en progreso
     */
    public function marcarEnProgreso(): void
    {
        if ($this->estado === 'bloqueado') {
            $this->estado = 'disponible';
        }
        if ($this->estado === 'disponible') {
            $this->estado = 'en_progreso';
            $this->fecha_inicio = now();
        }
        $this->save();
    }

    /**
     * Marcar como completado
     */
    public function marcarCompletado(): void
    {
        $this->estado = 'completado';
        $this->porcentaje_lectura = 100;
        $this->fecha_completado = now();
        $this->save();
    }

    /**
     * Actualizar porcentaje de lectura
     */
    public function actualizarLectura(int $porcentaje): void
    {
        $this->porcentaje_lectura = min(100, max(0, $porcentaje));
        if ($this->porcentaje_lectura >= 80 && $this->estado === 'en_progreso') {
            // Mantener en progreso hasta que complete el juego y evaluación
        }
        $this->save();
    }

    /**
     * Verificar si cumple con lectura mínima
     */
    public function getCumpleLecturaMinimaAttribute(): bool
    {
        return $this->porcentaje_lectura >= config('gamificacion.progresion.umbral_lectura', 80);
    }

    /**
     * Obtener tiempo dedicado en minutos
     */
    public function getTiempoDedicadoMinutosAttribute(): int
    {
        if (!$this->fecha_inicio) {
            return 0;
        }
        $fin = $this->fecha_completado ?? now();
        return ceil($this->fecha_inicio->diffInMinutes($fin));
    }

    /**
     * Obtener clase CSS según estado
     */
    public function getEstadoClaseAttribute(): string
    {
        $clases = [
            'bloqueado' => 'bg-gray-200 text-gray-500',
            'disponible' => 'bg-blue-100 text-blue-700',
            'en_progreso' => 'bg-yellow-100 text-yellow-700',
            'completado' => 'bg-green-100 text-green-700',
        ];
        return $clases[$this->estado] ?? 'bg-gray-100';
    }

    /**
     * Obtener icono según estado
     */
    public function getEstadoIconoAttribute(): string
    {
        $iconos = [
            'bloqueado' => '🔒',
            'disponible' => '🔓',
            'en_progreso' => '📖',
            'completado' => '✅',
        ];
        return $iconos[$this->estado] ?? '❓';
    }
}
