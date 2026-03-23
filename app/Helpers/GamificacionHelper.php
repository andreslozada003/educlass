<?php

use App\Models\User;
use App\Models\Asignatura;

if (!function_exists('get_nivel_data')) {
    /**
     * Obtener datos de un nivel
     */
    function get_nivel_data(int $nivel): array
    {
        return config("gamificacion.niveles.{$nivel}", [
            'nombre' => 'Desconocido',
            'icono' => '❓',
            'color' => '#6B7280',
            'descripcion' => 'Nivel no definido',
        ]);
    }
}

if (!function_exists('get_asignatura_color')) {
    /**
     * Obtener color de asignatura
     */
    function get_asignatura_color(string $asignaturaSlug): string
    {
        $colores = [
            'matematicas' => '#3B82F6',
            'lenguaje' => '#10B981',
            'ingles' => '#EF4444',
            'ciencias' => '#F59E0B',
        ];

        return $colores[$asignaturaSlug] ?? '#6B7280';
    }
}

if (!function_exists('format_tiempo')) {
    /**
     * Formatear tiempo en segundos a formato legible
     */
    function format_tiempo(int $segundos): string
    {
        $minutos = floor($segundos / 60);
        $secs = $segundos % 60;

        if ($minutos > 0) {
            return "{$minutos}m {$secs}s";
        }

        return "{$secs}s";
    }
}

if (!function_exists('format_duracion')) {
    /**
     * Formatear duración en formato mm:ss
     */
    function format_duracion(int $segundos): string
    {
        $minutos = floor($segundos / 60);
        $secs = $segundos % 60;
        return sprintf('%02d:%02d', $minutos, $secs);
    }
}

if (!function_exists('get_estado_badge')) {
    /**
     * Obtener badge HTML para estado
     */
    function get_estado_badge(string $estado): string
    {
        $badges = [
            'bloqueado' => '<span class="badge badge-gray">🔒 Bloqueado</span>',
            'disponible' => '<span class="badge badge-blue">🔓 Disponible</span>',
            'en_progreso' => '<span class="badge badge-yellow">📖 En Progreso</span>',
            'completado' => '<span class="badge badge-green">✅ Completado</span>',
            'aprobado' => '<span class="badge badge-green">✅ Aprobado</span>',
            'reprobado' => '<span class="badge badge-red">❌ Reprobado</span>',
        ];

        return $badges[$estado] ?? '<span class="badge badge-gray">Desconocido</span>';
    }
}

if (!function_exists('get_medalla')) {
    /**
     * Obtener medalla según posición
     */
    function get_medalla(int $posicion): string
    {
        return match ($posicion) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '🏅',
        };
    }
}

if (!function_exists('calcular_porcentaje')) {
    /**
     * Calcular porcentaje
     */
    function calcular_porcentaje(float $valor, float $total): float
    {
        if ($total <= 0) {
            return 0;
        }
        return round(($valor / $total) * 100, 2);
    }
}

if (!function_exists('get_barra_progreso')) {
    /**
     * Generar HTML de barra de progreso
     */
    function get_barra_progreso(float $porcentaje, string $color = null, string $altura = 'h-2'): string
    {
        $color = $color ?? match (true) {
            $porcentaje >= 80 => 'bg-green-500',
            $porcentaje >= 60 => 'bg-blue-500',
            $porcentaje >= 40 => 'bg-yellow-500',
            $porcentaje >= 20 => 'bg-orange-500',
            default => 'bg-red-500',
        };

        return <<<HTML
        <div class="w-full bg-gray-200 rounded-full {$altura}">
            <div class="{$color} {$altura} rounded-full transition-all duration-500" style="width: {$porcentage}%"></div>
        </div>
        HTML;
    }
}

if (!function_exists('get_tipo_juego_icono')) {
    /**
     * Obtener icono para tipo de juego
     */
    function get_tipo_juego_icono(string $tipo): string
    {
        $iconos = [
            'quiz' => '❓',
            'memoria' => '🧠',
            'arrastrar' => '✋',
            'completar' => '✏️',
            'ordenar' => '🔢',
            'sopa' => '🔤',
            'clasificar' => '⚡',
        ];

        return $iconos[$tipo] ?? '🎮';
    }
}

if (!function_exists('get_tipo_evaluacion_color')) {
    /**
     * Obtener color para tipo de evaluación
     */
    function get_tipo_evaluacion_color(string $tipo): string
    {
        $colores = [
            'diagnostica' => '#8B5CF6',
            'formativa' => '#3B82F6',
            'sumativa' => '#EF4444',
        ];

        return $colores[$tipo] ?? '#6B7280';
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncar texto
     */
    function truncate_text(string $texto, int $longitud = 100, string $sufijo = '...'): string
    {
        if (strlen($texto) <= $longitud) {
            return $texto;
        }

        return substr($texto, 0, $longitud) . $sufijo;
    }
}

if (!function_exists('get_iniciales')) {
    /**
     * Obtener iniciales de un nombre
     */
    function get_iniciales(string $nombre): string
    {
        $palabras = explode(' ', trim($nombre));
        $iniciales = '';

        foreach (array_slice($palabras, 0, 2) as $palabra) {
            $iniciales .= strtoupper(substr($palabra, 0, 1));
        }

        return $iniciales;
    }
}

if (!function_exists('get_color_avatar')) {
    /**
     * Generar color de avatar basado en nombre
     */
    function get_color_avatar(string $nombre): string
    {
        $colores = [
            '#EF4444', '#F97316', '#F59E0B', '#84CC16',
            '#10B981', '#06B6D4', '#3B82F6', '#8B5CF6',
            '#EC4899', '#F43F5E',
        ];

        $hash = crc32($nombre);
        return $colores[abs($hash) % count($colores)];
    }
}

if (!function_exists('es_periodo_activo')) {
    /**
     * Verificar si un período está activo
     */
    function es_periodo_activo(int $periodo): bool
    {
        $periodoActual = \App\Models\ConfiguracionSistema::getPeriodoActual();
        return $periodo === $periodoActual;
    }
}

if (!function_exists('get_periodo_nombre')) {
    /**
     * Obtener nombre del período
     */
    function get_periodo_nombre(int $periodo): string
    {
        return "Período {$periodo}";
    }
}

if (!function_exists('format_fecha')) {
    /**
     * Formatear fecha
     */
    function format_fecha($fecha, string $formato = 'd/m/Y'): string
    {
        if (!$fecha) {
            return 'N/A';
        }

        if (is_string($fecha)) {
            $fecha = \Carbon\Carbon::parse($fecha);
        }

        return $fecha->format($formato);
    }
}

if (!function_exists('format_fecha_hora')) {
    /**
     * Formatear fecha y hora
     */
    function format_fecha_hora($fecha): string
    {
        return format_fecha($fecha, 'd/m/Y H:i');
    }
}

if (!function_exists('get_tiempo_relativo')) {
    /**
     * Obtener tiempo relativo
     */
    function get_tiempo_relativo($fecha): string
    {
        if (!$fecha) {
            return 'Nunca';
        }

        if (is_string($fecha)) {
            $fecha = \Carbon\Carbon::parse($fecha);
        }

        return $fecha->diffForHumans();
    }
}

if (!function_exists('generar_slug')) {
    /**
     * Generar slug a partir de texto
     */
    function generar_slug(string $texto): string
    {
        $slug = strtolower(trim($texto));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}

if (!function_exists('get_clase_estado')) {
    /**
     * Obtener clase CSS según estado
     */
    function get_clase_estado(string $estado): string
    {
        $clases = [
            'bloqueado' => 'bg-gray-100 text-gray-500 border-gray-200',
            'disponible' => 'bg-blue-50 text-blue-700 border-blue-200',
            'en_progreso' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'completado' => 'bg-green-50 text-green-700 border-green-200',
            'aprobado' => 'bg-green-50 text-green-700 border-green-200',
            'reprobado' => 'bg-red-50 text-red-700 border-red-200',
            'activo' => 'bg-green-50 text-green-700 border-green-200',
            'inactivo' => 'bg-red-50 text-red-700 border-red-200',
        ];

        return $clases[$estado] ?? 'bg-gray-50 text-gray-700 border-gray-200';
    }
}
