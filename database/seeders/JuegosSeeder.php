<?php

namespace Database\Seeders;

use App\Models\Juego;
use App\Models\Tema;
use App\Models\PreguntasJuego;
use Illuminate\Database\Seeder;

class JuegosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $temas = Tema::all();

        foreach ($temas as $tema) {
            // Crear un juego tipo quiz para cada tema
            $juego = Juego::create([
                'tema_id' => $tema->id,
                'tipo' => 'quiz',
                'titulo' => 'Quiz: ' . $tema->titulo,
                'descripcion' => 'Pon a prueba tus conocimientos sobre ' . $tema->titulo,
                'configuracion' => [
                    'timer_default' => 300,
                    'mostrar_feedback' => true,
                    'puntos_por_respuesta' => 10,
                ],
                'dificultad' => $tema->dificultad,
                'intentos_maximos' => 5,
                'puntaje_base' => 100,
                'tiempo_limite_segundos' => 300,
                'activo' => true,
            ]);

            // Crear preguntas según el tema
            $this->crearPreguntasParaTema($juego, $tema);
        }
    }

    /**
     * Crear preguntas para un tema
     */
    private function crearPreguntasParaTema(Juego $juego, Tema $tema): void
    {
        $preguntas = $this->getPreguntasPorTema($tema);
        $orden = 1;

        foreach ($preguntas as $preguntaData) {
            PreguntasJuego::create([
                'juego_id' => $juego->id,
                'tipo' => $preguntaData['tipo'],
                'enunciado' => $preguntaData['enunciado'],
                'opciones' => $preguntaData['opciones'] ?? null,
                'respuesta_correcta' => $preguntaData['respuesta_correcta'],
                'puntaje' => 10,
                'orden' => $orden++,
                'activo' => true,
            ]);
        }
    }

    /**
     * Obtener preguntas según el tema
     */
    private function getPreguntasPorTema(Tema $tema): array
    {
        $preguntasPorTema = [
            'Sumas y Restas' => [
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuánto es 5 + 3?',
                    'opciones' => ['7', '8', '9', '10'],
                    'respuesta_correcta' => ['8'],
                ],
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuánto es 10 - 4?',
                    'opciones' => ['5', '6', '7', '8'],
                    'respuesta_correcta' => ['6'],
                ],
                [
                    'tipo' => 'verdadero_falso',
                    'enunciado' => '7 + 2 es igual a 10',
                    'respuesta_correcta' => ['false'],
                ],
            ],
            'Multiplicación' => [
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuánto es 3 × 4?',
                    'opciones' => ['10', '11', '12', '13'],
                    'respuesta_correcta' => ['12'],
                ],
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuánto es 5 × 5?',
                    'opciones' => ['20', '25', '30', '35'],
                    'respuesta_correcta' => ['25'],
                ],
            ],
            'El Alfabeto' => [
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuál es la primera letra del alfabeto?',
                    'opciones' => ['B', 'C', 'A', 'D'],
                    'respuesta_correcta' => ['A'],
                ],
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuántas letras tiene el abecedario español?',
                    'opciones' => ['26', '27', '28', '29'],
                    'respuesta_correcta' => ['27'],
                ],
            ],
            'Saludos Básicos' => [
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cómo se dice "Hola" en inglés?',
                    'opciones' => ['Goodbye', 'Hello', 'Thank you', 'Please'],
                    'respuesta_correcta' => ['Hello'],
                ],
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '"Good morning" significa:',
                    'opciones' => ['Buenas noches', 'Buenos días', 'Buenas tardes', 'Hola'],
                    'respuesta_correcta' => ['Buenos días'],
                ],
            ],
            'Los Seres Vivos' => [
                [
                    'tipo' => 'opcion_multiple',
                    'enunciado' => '¿Cuál de estos es un ser vivo?',
                    'opciones' => ['Piedra', 'Agua', 'Planta', 'Aire'],
                    'respuesta_correcta' => ['Planta'],
                ],
                [
                    'tipo' => 'verdadero_falso',
                    'enunciado' => 'Los seres vivos necesitan agua para vivir.',
                    'respuesta_correcta' => ['true'],
                ],
            ],
        ];

        return $preguntasPorTema[$tema->titulo] ?? [
            [
                'tipo' => 'opcion_multiple',
                'enunciado' => '¿Cuál es el tema principal de esta lección?',
                'opciones' => ['Opción A', 'Opción B', 'Opción C', 'Opción D'],
                'respuesta_correcta' => ['Opción A'],
            ],
            [
                'tipo' => 'verdadero_falso',
                'enunciado' => 'Esta afirmación es verdadera.',
                'respuesta_correcta' => ['true'],
            ],
        ];
    }
}
