<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo LogrosEstudiante - Logros obtenidos por estudiantes
 */
class LogrosEstudiante extends Model
{
    use HasFactory;

    protected $table = 'logros_estudiantes';

    protected $fillable = [
        'estudiante_id',
        'logro_id',
        'fecha_obtenido',
        'contexto',
    ];

    protected $casts = [
        'contexto' => 'array',
        'fecha_obtenido' => 'datetime',
    ];

    /**
     * Relación con estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con logro
     */
    public function logro()
    {
        return $this->belongsTo(Logro::class);
    }

    /**
     * Scope por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope por logro
     */
    public function scopePorLogro($query, $logroId)
    {
        return $query->where('logro_id', $logroId);
    }

    /**
     * Scope ordenado por fecha
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha_obtenido', 'desc');
    }

    /**
     * Obtener tiempo transcurrido desde que se obtuvo
     */
    public function getTiempoTranscurridoAttribute(): string
    {
        return $this->fecha_obtenido->diffForHumans();
    }

    /**
     * Obtener fecha formateada
     */
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha_obtenido->format('d/m/Y H:i');
    }

    /**
     * Obtener detalles del contexto
     */
    public function getDetallesContextoAttribute(): array
    {
        $contexto = $this->contexto ?? [];
        $detalles = [];
        
        foreach ($contexto as $key => $value) {
            $detalles[] = [
                'clave' => $key,
                'valor' => $value,
            ];
        }
        
        return $detalles;
    }

    /**
     * Obtener mensaje de felicitación
     */
    public function getMensajeFelicitacionAttribute(): string
    {
        $mensajes = [
            '¡Increíble! Has desbloqueado el logro: ' . $this->logro->nombre,
            '¡Felicidades! Has conseguido: ' . $this->logro->nombre,
            '¡Logro desbloqueado! ' . $this->logro->nombre,
            '¡Eres un campeón! Has obtenido: ' . $this->logro->nombre,
        ];
        
        return $mensajes[array_rand($mensajes)];
    }

    /**
     * Obtener imagen para compartir
     */
    public function getImagenCompartirAttribute(): string
    {
        // Retornar URL de imagen generada para compartir en redes
        return route('estudiante.logros.compartir', $this->id);
    }

    /**
     * Boot - Disparar evento al crear
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($logroEstudiante) {
            // Aquí se puede disparar un evento para notificaciones
            // event(new LogroDesbloqueado($logroEstudiante));
        });
    }
}
