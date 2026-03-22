<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo User - Gestión de usuarios (estudiantes, docentes, admin)
 * 
 * @property int $id
 * @property string $tipo
 * @property string $nombre
 * @property int|null $colegio_id
 * @property string $email
 * @property string|null $telefono
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $reset_token
 * @property string|null $reset_expira
 * @property string|null $avatar
 * @property bool $activo
 * @property string|null $ultimo_acceso
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'tipo',
        'nombre',
        'colegio_id',
        'email',
        'telefono',
        'email_verified_at',
        'password',
        'reset_token',
        'reset_expira',
        'avatar',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'reset_expira' => 'datetime',
        'ultimo_acceso' => 'datetime',
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Relación con el colegio
     */
    public function colegio()
    {
        return $this->belongsTo(Colegio::class);
    }

    /**
     * Relación con progreso de estudiante
     */
    public function progresoEstudiante()
    {
        return $this->hasMany(ProgresoEstudiante::class, 'estudiante_id');
    }

    /**
     * Relación con intentos de juegos
     */
    public function intentosJuegos()
    {
        return $this->hasMany(IntentosJuego::class, 'estudiante_id');
    }

    /**
     * Relación con resultados de evaluaciones
     */
    public function resultadosEvaluaciones()
    {
        return $this->hasMany(ResultadosEvaluacion::class, 'estudiante_id');
    }

    /**
     * Relación con calificaciones por período
     */
    public function calificacionesPeriodo()
    {
        return $this->hasMany(CalificacionesPeriodo::class, 'estudiante_id');
    }

    /**
     * Relación con rankings
     */
    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'estudiante_id');
    }

    /**
     * Relación con logros obtenidos
     */
    public function logrosEstudiante()
    {
        return $this->hasMany(LogrosEstudiante::class, 'estudiante_id');
    }

    /**
     * Relación con logros a través de tabla pivote
     */
    public function logros()
    {
        return $this->belongsToMany(Logro::class, 'logros_estudiantes', 'estudiante_id', 'logro_id')
            ->withPivot('fecha_obtenido', 'contexto')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar estudiantes
     */
    public function scopeEstudiantes($query)
    {
        return $query->where('tipo', 'estudiante');
    }

    /**
     * Scope para filtrar docentes
     */
    public function scopeDocentes($query)
    {
        return $query->where('tipo', 'docente');
    }

    /**
     * Verificar si es estudiante
     */
    public function esEstudiante(): bool
    {
        return $this->tipo === 'estudiante';
    }

    /**
     * Verificar si es docente
     */
    public function esDocente(): bool
    {
        return $this->tipo === 'docente';
    }

    /**
     * Verificar si es admin
     */
    public function esAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    /**
     * Obtener URL del avatar
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Obtener iniciales del nombre
     */
    public function getInicialesAttribute(): string
    {
        $palabras = explode(' ', $this->nombre);
        $iniciales = '';
        foreach (array_slice($palabras, 0, 2) as $palabra) {
            $iniciales .= strtoupper(substr($palabra, 0, 1));
        }
        return $iniciales;
    }

    

    
}
