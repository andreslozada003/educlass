<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo ConfiguracionSistema - Configuración del sistema
 */
class ConfiguracionSistema extends Model
{
    use HasFactory;

    protected $table = 'configuracion_sistema';

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'descripcion',
    ];

    /**
     * Scope por grupo
     */
    public function scopePorGrupo($query, $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    /**
     * Scope por clave
     */
    public function scopePorClave($query, $clave)
    {
        return $query->where('clave', $clave);
    }

    /**
     * Obtener valor con caché
     */
    public static function getValor(string $clave, $default = null)
    {
        $cacheKey = "config.{$clave}";
        
        return Cache::remember($cacheKey, 3600, function () use ($clave, $default) {
            $config = static::where('clave', $clave)->first();
            return $config ? $config->valor : $default;
        });
    }

    /**
     * Establecer valor
     */
    public static function setValor(string $clave, string $valor, string $grupo = 'general', ?string $descripcion = null): self
    {
        $config = static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'grupo' => $grupo,
                'descripcion' => $descripcion,
            ]
        );
        
        // Limpiar caché
        Cache::forget("config.{$clave}");
        
        return $config;
    }

    /**
     * Obtener configuraciones por grupo
     */
    public static function getPorGrupo(string $grupo): array
    {
        return static::porGrupo($grupo)
            ->get()
            ->pluck('valor', 'clave')
            ->toArray();
    }

    /**
     * Obtener período actual
     */
    public static function getPeriodoActual(): int
    {
        return (int) static::getValor('periodo_actual', 1);
    }

    /**
     * Establecer período actual
     */
    public static function setPeriodoActual(int $periodo): void
    {
        static::setValor('periodo_actual', (string) $periodo, 'academico', 'Período académico actual');
    }

    /**
     * Obtener año académico activo
     */
    public static function getAnioAcademico(): int
    {
        return (int) static::getValor('anio_academico_activo', date('Y'));
    }

    /**
     * Establecer año académico
     */
    public static function setAnioAcademico(int $anio): void
    {
        static::setValor('anio_academico_activo', (string) $anio, 'academico', 'Año académico activo');
    }

    /**
     * Obtener intentos default
     */
    public static function getIntentosDefault(): int
    {
        return (int) static::getValor('intentos_default', 5);
    }

    /**
     * Obtener umbral de aprobación
     */
    public static function getUmbralAprobacion(): int
    {
        return (int) static::getValor('umbral_aprobacion_default', 60);
    }

    /**
     * Limpiar toda la caché de configuración
     */
    public static function limpiarCache(): void
    {
        $claves = static::all()->pluck('clave');
        foreach ($claves as $clave) {
            Cache::forget("config.{$clave}");
        }
    }

    /**
     * Cargar configuración inicial
     */
    public static function cargarConfiguracionInicial(): void
    {
        $configuraciones = [
            // Académico
            ['periodo_actual', '1', 'academico', 'Período académico actual'],
            ['anio_academico_activo', date('Y'), 'academico', 'Año académico activo'],
            ['fecha_inicio_periodo_1', date('Y') . '-02-01', 'academico', 'Fecha inicio período 1'],
            ['fecha_fin_periodo_1', date('Y') . '-04-30', 'academico', 'Fecha fin período 1'],
            ['fecha_inicio_periodo_2', date('Y') . '-05-01', 'academico', 'Fecha inicio período 2'],
            ['fecha_fin_periodo_2', date('Y') . '-07-31', 'academico', 'Fecha fin período 2'],
            ['fecha_inicio_periodo_3', date('Y') . '-08-01', 'academico', 'Fecha inicio período 3'],
            ['fecha_fin_periodo_3', date('Y') . '-11-30', 'academico', 'Fecha fin período 3'],
            
            // Gamificación
            ['intentos_default', '5', 'gamificacion', 'Intentos máximos por defecto'],
            ['umbral_aprobacion_default', '60', 'gamificacion', 'Umbral de aprobación por defecto (%)'],
            ['puntaje_base', '100', 'gamificacion', 'Puntaje base por juego'],
            ['bonificacion_tiempo', 'true', 'gamificacion', 'Activar bonificación por tiempo'],
            
            // Sistema
            ['mantenimiento', 'false', 'sistema', 'Modo mantenimiento'],
            ['registro_abierto', 'true', 'sistema', 'Permitir registro de nuevos usuarios'],
            ['ranking_actualizacion_minutos', '5', 'sistema', 'Intervalo de actualización del ranking'],
        ];
        
        foreach ($configuraciones as $config) {
            static::setValor($config[0], $config[1], $config[2], $config[3]);
        }
    }
}
