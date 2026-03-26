<?php

namespace App\Services;

use App\Models\Juego;
use App\Models\PreguntasJuego;

/**
 * Service JuegoEngineService - Motor de juegos educativos
 */
class JuegoEngineService
{
    /**
     * Generar datos para juego de memoria
     */
    public function generarMemoriaData(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;
        $pares = [];
        $id = 1;

        foreach ($preguntas as $pregunta) {
            // Carta con el enunciado/pregunta
            $pares[] = [
                'id' => $id,
                'pregunta_id' => $pregunta->id,
                'tipo' => 'pregunta',
                'contenido' => $pregunta->enunciado,
                'match_id' => $id,
                'puntaje' => $pregunta->puntaje,
            ];

            // Carta con la respuesta
            $respuesta = is_array($pregunta->respuesta_correcta) 
                ? implode(', ', $pregunta->respuesta_correcta) 
                : $pregunta->respuesta_correcta;

            $pares[] = [
                'id' => $id + 1000, // ID diferente para la carta respuesta
                'pregunta_id' => $pregunta->id,
                'tipo' => 'respuesta',
                'contenido' => $respuesta,
                'match_id' => $id,
                'puntaje' => $pregunta->puntaje,
                'respuesta' => $respuesta,
            ];

            $pares[count($pares) - 2]['respuesta'] = $respuesta;

            $id++;
        }

        // Mezclar las cartas
        shuffle($pares);

        return [
            'cartas' => $pares,
            'total_pares' => count($preguntas),
            'tiempo_memorizacion' => $juego->configuracion['tiempo_memorizacion'] ?? 5,
        ];
    }

    /**
     * Generar datos para juego de arrastrar y soltar
     */
    public function generarArrastrarData(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;
        $categorias = [];
        $elementos = [];

        foreach ($preguntas as $pregunta) {
            $opciones = $pregunta->opciones ?? [];
            
            foreach ($opciones as $opcion) {
                if (!isset($opcion['categoria']) || !isset($opcion['elemento'])) {
                    continue;
                }

                $categoria = $opcion['categoria'];
                $elemento = $opcion['elemento'];

                if (!in_array($categoria, $categorias)) {
                    $categorias[] = $categoria;
                }

                $elementos[] = [
                    'id' => uniqid(),
                    'elemento' => $elemento,
                    'categoria_correcta' => $categoria,
                ];
            }
        }

        // Mezclar elementos
        shuffle($elementos);

        return [
            'categorias' => $categorias,
            'elementos' => $elementos,
        ];
    }

    /**
     * Generar datos para juego de ordenar
     */
    public function generarOrdenarData(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;

        if (optional(optional($juego->tema)->asignatura)->slug === 'ingles') {
            $parejas = [];

            foreach ($preguntas as $pregunta) {
                $respuesta = is_array($pregunta->respuesta_correcta)
                    ? ($pregunta->respuesta_correcta[0] ?? '')
                    : $pregunta->respuesta_correcta;

                $parejas[] = [
                    'id' => $pregunta->id,
                    'palabra' => $pregunta->enunciado,
                    'pareja' => $respuesta,
                    'puntaje' => $pregunta->puntaje,
                ];
            }

            $opciones = $parejas;
            shuffle($opciones);

            return [
                'modo' => 'match_words',
                'parejas' => $parejas,
                'opciones' => $opciones,
                'total_elementos' => count($parejas),
            ];
        }

        $elementos = [];

        foreach ($preguntas as $pregunta) {
            $elementos[] = [
                'id' => $pregunta->id,
                'contenido' => $pregunta->enunciado,
                'orden_correcto' => $pregunta->respuesta_correcta['orden'] ?? $pregunta->orden,
            ];
        }

        // Mezclar elementos
        $elementosMezclados = $elementos;
        shuffle($elementosMezclados);

        return [
            'elementos' => $elementosMezclados,
            'total_elementos' => count($elementos),
        ];
    }

    /**
     * Generar sopa de letras
     */
    public function generarSopaLetras(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;
        $palabras = [];

        foreach ($preguntas as $pregunta) {
            $respuesta = is_array($pregunta->respuesta_correcta) 
                ? $pregunta->respuesta_correcta[0] 
                : $pregunta->respuesta_correcta;
            
            $palabras[] = [
                'id' => $pregunta->id,
                'palabra' => strtoupper($respuesta),
                'pista' => $pregunta->enunciado,
                'puntaje' => $pregunta->puntaje,
            ];
        }

        $tamano = $juego->configuracion['tamano_grid'] ?? 15;
        $grid = $this->generarGridSopaLetras($palabras, $tamano);

        return [
            'grid' => $grid,
            'palabras' => $palabras,
            'tamano' => $tamano,
        ];
    }

    /**
     * Generar grid de sopa de letras
     */
    private function generarGridSopaLetras(array $palabras, int $tamano): array
    {
        // Inicializar grid vacío
        $grid = [];
        for ($i = 0; $i < $tamano; $i++) {
            for ($j = 0; $j < $tamano; $j++) {
                $grid[$i][$j] = '';
            }
        }

        // Direcciones: horizontal, vertical, diagonal
        $direcciones = [
            [0, 1],   // Horizontal
            [1, 0],   // Vertical
            [1, 1],   // Diagonal ↘
            [1, -1],  // Diagonal ↙
        ];

        foreach ($palabras as $palabraData) {
            $palabra = $palabraData['palabra'];
            $colocada = false;
            $intentos = 0;

            while (!$colocada && $intentos < 100) {
                $direccion = $direcciones[array_rand($direcciones)];
                $fila = rand(0, $tamano - 1);
                $col = rand(0, $tamano - 1);

                if ($this->puedeColocarPalabra($grid, $palabra, $fila, $col, $direccion, $tamano)) {
                    $this->colocarPalabra($grid, $palabra, $fila, $col, $direccion);
                    $colocada = true;
                }

                $intentos++;
            }
        }

        // Llenar espacios vacíos con letras aleatorias
        for ($i = 0; $i < $tamano; $i++) {
            for ($j = 0; $j < $tamano; $j++) {
                if ($grid[$i][$j] === '') {
                    $grid[$i][$j] = chr(rand(65, 90));
                }
            }
        }

        return $grid;
    }

    /**
     * Verificar si se puede colocar una palabra
     */
    private function puedeColocarPalabra(array $grid, string $palabra, int $fila, int $col, array $direccion, int $tamano): bool
    {
        $longitud = strlen($palabra);

        for ($i = 0; $i < $longitud; $i++) {
            $nuevaFila = $fila + ($i * $direccion[0]);
            $nuevaCol = $col + ($i * $direccion[1]);

            if ($nuevaFila < 0 || $nuevaFila >= $tamano || $nuevaCol < 0 || $nuevaCol >= $tamano) {
                return false;
            }

            if ($grid[$nuevaFila][$nuevaCol] !== '' && $grid[$nuevaFila][$nuevaCol] !== $palabra[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Colocar palabra en el grid
     */
    private function colocarPalabra(array &$grid, string $palabra, int $fila, int $col, array $direccion): void
    {
        $longitud = strlen($palabra);

        for ($i = 0; $i < $longitud; $i++) {
            $nuevaFila = $fila + ($i * $direccion[0]);
            $nuevaCol = $col + ($i * $direccion[1]);
            $grid[$nuevaFila][$nuevaCol] = $palabra[$i];
        }
    }

    /**
     * Generar datos para juego de completar
     */
    public function generarCompletarData(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;
        $frases = [];

        foreach ($preguntas as $pregunta) {
            $respuesta = is_array($pregunta->respuesta_correcta) 
                ? $pregunta->respuesta_correcta[0] 
                : $pregunta->respuesta_correcta;

            $frases[] = [
                'id' => $pregunta->id,
                'enunciado' => $pregunta->enunciado,
                'respuesta' => $respuesta,
                'puntaje' => $pregunta->puntaje,
                'longitud' => strlen($respuesta),
                'pistas' => $this->generarPistas($respuesta),
            ];
        }

        return [
            'frases' => $frases,
            'pistas_disponibles' => $juego->configuracion['pistas_disponibles'] ?? 2,
            'penalizacion_pista' => $juego->configuracion['penalizacion_pista'] ?? 5,
        ];
    }

    /**
     * Generar pistas para una palabra
     */
    private function generarPistas(string $palabra): array
    {
        $pistas = [];
        $longitud = strlen($palabra);
        $letrasMostradas = max(1, (int)($longitud * 0.3)); // Mostrar 30% de letras

        for ($i = 0; $i < $letrasMostradas; $i++) {
            $posicion = rand(0, $longitud - 1);
            $pistas[] = [
                'posicion' => $posicion,
                'letra' => $palabra[$posicion],
            ];
        }

        return $pistas;
    }

    /**
     * Generar datos para juego de clasificación rápida
     */
    public function generarClasificacionData(Juego $juego): array
    {
        $preguntas = $juego->preguntasActivas;
        $categorias = [];
        $items = [];

        foreach ($preguntas as $pregunta) {
            $opciones = $pregunta->opciones ?? [];
            
            foreach ($opciones as $opcion) {
                if (!isset($opcion['categoria']) || !isset($opcion['elemento'])) {
                    continue;
                }

                $categoria = $opcion['categoria'];
                $elemento = $opcion['elemento'];

                if (!isset($categorias[$categoria])) {
                    $categorias[$categoria] = [
                        'nombre' => $categoria,
                        'color' => $this->generarColorCategoria($categoria),
                    ];
                }

                $items[] = [
                    'id' => uniqid(),
                    'elemento' => $elemento,
                    'categoria' => $categoria,
                ];
            }
        }

        // Mezclar items
        shuffle($items);

        return [
            'categorias' => array_values($categorias),
            'items' => $items,
            'velocidad_inicial' => $juego->configuracion['velocidad_inicial'] ?? 1,
            'incremento_velocidad' => $juego->configuracion['incremento_velocidad'] ?? 0.2,
        ];
    }

    /**
     * Generar color para categoría
     */
    private function generarColorCategoria(string $categoria): string
    {
        $colores = [
            '#EF4444', '#F97316', '#F59E0B', '#84CC16',
            '#10B981', '#06B6D4', '#3B82F6', '#8B5CF6',
            '#EC4899', '#F43F5E',
        ];

        $hash = crc32($categoria);
        return $colores[abs($hash) % count($colores)];
    }

    /**
     * Validar respuestas de un juego
     */
    public function validarRespuestas(Juego $juego, array $respuestasUsuario): array
    {
        $preguntas = $juego->preguntasActivas;
        $resultados = [];
        $puntajeTotal = 0;
        $correctas = 0;

        foreach ($preguntas as $pregunta) {
            $respuestaUsuario = $respuestasUsuario[$pregunta->id] ?? null;
            $esCorrecta = false;

            if ($respuestaUsuario !== null) {
                $esCorrecta = $pregunta->verificarRespuesta($respuestaUsuario);
            }

            if ($esCorrecta) {
                $puntajeTotal += $pregunta->puntaje;
                $correctas++;
            }

            $resultados[$pregunta->id] = [
                'correcta' => $esCorrecta,
                'puntaje' => $esCorrecta ? $pregunta->puntaje : 0,
                'respuesta_correcta' => $pregunta->respuesta_correcta,
                'respuesta_usuario' => $respuestaUsuario,
            ];
        }

        return [
            'resultados' => $resultados,
            'puntaje_total' => $puntajeTotal,
            'correctas' => $correctas,
            'total_preguntas' => $preguntas->count(),
        ];
    }

    /**
     * Calcular bonificación por tiempo
     */
    public function calcularBonificacionTiempo(int $puntajeBase, int $duracionSegundos, ?int $tiempoLimiteSegundos): int
    {
        if (!$tiempoLimiteSegundos || $tiempoLimiteSegundos <= 0) {
            return 0;
        }

        $tiempoRestante = max(0, $tiempoLimiteSegundos - $duracionSegundos);
        $porcentajeRestante = ($tiempoRestante / $tiempoLimiteSegundos) * 100;

        return (int) ($puntajeBase * ($porcentajeRestante / 100) * 0.2);
    }
}
