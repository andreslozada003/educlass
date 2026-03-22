<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Niveles
    |--------------------------------------------------------------------------
    */
    'niveles' => [
        1 => [
            'nombre' => 'Básico',
            'icono' => '⭐',
            'color' => '#10B981',
            'descripcion' => 'Nivel inicial de aprendizaje',
        ],
        2 => [
            'nombre' => 'Intermedio',
            'icono' => '⭐⭐',
            'color' => '#3B82F6',
            'descripcion' => 'Nivel de consolidación de conocimientos',
        ],
        3 => [
            'nombre' => 'Avanzado',
            'icono' => '⭐⭐⭐',
            'color' => '#8B5CF6',
            'descripcion' => 'Nivel de dominio avanzado',
        ],
        4 => [
            'nombre' => 'Experto',
            'icono' => '⭐⭐⭐⭐',
            'color' => '#F59E0B',
            'descripcion' => 'Nivel de excelencia y maestría',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Puntajes
    |--------------------------------------------------------------------------
    */
    'puntajes' => [
        'base' => env('GAMIFICACION_PUNTOS_BASE', 100),
        'bonificacion_tiempo' => env('GAMIFICACION_BONIFICACION_TIEMPO', true),
        'racha_bonus' => env('GAMIFICACION_RACHA_BONUS', 10),
        'penalizacion_error' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Progresión
    |--------------------------------------------------------------------------
    */
    'progresion' => [
        'secuencial_obligatoria' => true,
        'umbral_lectura' => 80, // Porcentaje mínimo de lectura
        'intentos_default' => env('EDUCLASS_INTENTOS_DEFAULT', 5),
        'umbral_aprobacion' => env('EDUCLASS_UMBRAL_APROBACION', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logros/Badges
    |--------------------------------------------------------------------------
    */
    'logros' => [
        'primer_paso' => [
            'nombre' => 'Primer Paso',
            'descripcion' => 'Completar tu primer tema',
            'icono' => '👣',
            'color' => '#10B981',
            'criterio' => ['temas_completados' => 1],
        ],
        'racha_perfecta' => [
            'nombre' => 'Racha Perfecta',
            'descripcion' => '5 evaluaciones perfectas seguidas',
            'icono' => '🔥',
            'color' => '#EF4444',
            'criterio' => ['evaluaciones_perfectas_consecutivas' => 5],
        ],
        'velocista' => [
            'nombre' => 'Velocista',
            'descripcion' => 'Completar un juego en menos de 30 segundos',
            'icono' => '⚡',
            'color' => '#F59E0B',
            'criterio' => ['tiempo_maximo_segundos' => 30],
        ],
        'persistente' => [
            'nombre' => 'Persistente',
            'descripcion' => 'Usar los 5 intentos antes de lograrlo',
            'icono' => '💪',
            'color' => '#8B5CF6',
            'criterio' => ['intentos_usados' => 5],
        ],
        'maestro' => [
            'nombre' => 'Maestro',
            'descripcion' => 'Completar una asignatura al 100%',
            'icono' => '🎓',
            'color' => '#EC4899',
            'criterio' => ['asignatura_completada' => true],
        ],
        'top_10' => [
            'nombre' => 'Top 10',
            'descripcion' => 'Entrar al ranking general',
            'icono' => '🏆',
            'color' => '#F59E0B',
            'criterio' => ['posicion_ranking' => 10],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Ranking
    |--------------------------------------------------------------------------
    */
    'ranking' => [
        'actualizacion_minutos' => 5,
        'categorias' => ['juegos', 'evaluaciones', 'temas', 'general'],
        'top_limit' => 10,
    ],
];
