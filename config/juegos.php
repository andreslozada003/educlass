<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tipos de Juegos Disponibles
    |--------------------------------------------------------------------------
    */
    'tipos' => [
        'quiz' => [
            'nombre' => 'Quiz Interactivo',
            'descripcion' => 'Preguntas de opcion multiple con tiempo',
            'icono' => '❓',
            'color' => '#3B82F6',
            'configuracion' => [
                'timer_default' => 300,
                'mostrar_feedback' => true,
                'puntos_por_respuesta' => 10,
            ],
        ],
        'memoria' => [
            'nombre' => 'Memoria',
            'descripcion' => 'Encuentra los pares de cartas',
            'icono' => '🧠',
            'color' => '#8B5CF6',
            'configuracion' => [
                'pares_default' => 8,
                'tiempo_memorizacion' => 5,
                'puntos_por_par' => 15,
            ],
        ],
        'arrastrar' => [
            'nombre' => 'Arrastrar y Soltar',
            'descripcion' => 'Clasifica elementos en categorias',
            'icono' => '✋',
            'color' => '#10B981',
            'configuracion' => [
                'categorias_minimas' => 2,
                'elementos_por_categoria' => 4,
                'puntos_por_elemento' => 5,
            ],
        ],
        'completar' => [
            'nombre' => 'Completar',
            'descripcion' => 'Completa palabras o frases',
            'icono' => '✏️',
            'color' => '#F59E0B',
            'configuracion' => [
                'pistas_disponibles' => 2,
                'penalizacion_pista' => 5,
                'puntos_por_letra' => 2,
            ],
        ],
        'ordenar' => [
            'nombre' => 'Ordenar',
            'descripcion' => 'Ordena elementos en secuencia',
            'icono' => '🔢',
            'color' => '#EC4899',
            'configuracion' => [
                'elementos_minimos' => 4,
                'puntos_por_posicion' => 5,
            ],
        ],
        'sopa' => [
            'nombre' => 'Sopa de Letras',
            'descripcion' => 'Encuentra palabras escondidas',
            'icono' => '🔤',
            'color' => '#EF4444',
            'configuracion' => [
                'tamano_grid' => 15,
                'palabras_minimas' => 5,
                'puntos_por_palabra' => 10,
            ],
        ],
        'clasificar' => [
            'nombre' => 'Clasificacion Rapida',
            'descripcion' => 'Clasifica elementos que caen',
            'icono' => '⚡',
            'color' => '#06B6D4',
            'configuracion' => [
                'velocidad_inicial' => 1,
                'incremento_velocidad' => 0.2,
                'puntos_por_acierto' => 5,
            ],
        ],
        'matematica_aventura' => [
            'nombre' => 'Matematica Aventura',
            'descripcion' => 'Resuelve operaciones para cruzar puentes, abrir cofres o vencer obstaculos',
            'icono' => '🧮',
            'color' => '#0F766E',
            'configuracion' => [
                'operacion_principal' => 'mixto',
                'objetivo_aventura' => 'puente',
                'recompensa_principal' => 'monedas',
                'monedas_por_acierto' => 15,
                'energia_por_acierto' => 10,
                'mostrar_feedback' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuracion General de Juegos
    |--------------------------------------------------------------------------
    */
    'configuracion' => [
        'intentos_maximos' => env('EDUCLASS_INTENTOS_DEFAULT', 5),
        'timer_default' => env('JUEGOS_TIMER_DEFAULT', 300),
        'mostrar_resultados' => true,
        'permitir_repetir' => true,
        'guardar_mejor_puntaje' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bonificaciones
    |--------------------------------------------------------------------------
    */
    'bonificaciones' => [
        'por_tiempo' => true,
        'por_racha' => true,
        'racha_minima' => 3,
        'multiplicador_racha' => 1.5,
    ],
];
