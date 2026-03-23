<?php

namespace Database\Seeders;

use App\Models\Evaluacion;
use App\Models\Tema;
use App\Models\PreguntasEvaluacion;
use Illuminate\Database\Seeder;

class EvaluacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $temas = Tema::all();

        foreach ($temas as $tema) {
            // Crear una evaluación para cada tema
            $evaluacion = Evaluacion::create([
                'tema_id' => $tema->id,
                'titulo' => 'Evaluación: ' . $tema->titulo,
                'descripcion' => 'Evaluación de conocimientos sobre ' . $tema->titulo,
                'tipo' => 'formativa',
                'tiempo_limite_minutos' => 20,
                'intentos_permitidos' => 3,
                'umbral_aprobacion' => 60,
                'puntaje_total' => 0,
                'activa' => true,
            ]);

            // Crear preguntas
            $this->crearPreguntasEvaluacion($evaluacion, $tema);
        }
    }

    /**
     * Crear preguntas para evaluación
     */
    private function crearPreguntasEvaluacion(Evaluacion $evaluacion, Tema $tema): void
    {
        $preguntas = $this->getPreguntasEvaluacionPorTema($tema);
        $orden = 1;
        $puntajeTotal = 0;

        foreach ($preguntas as $preguntaData) {
            $puntaje = $preguntaData['puntaje'] ?? 10;
            $puntajeTotal += $puntaje;

            PreguntasEvaluacion::create([
                'evaluacion_id' => $evaluacion->id,
                'tipo' => $preguntaData['tipo'],
                'enunciado' => $preguntaData['enunciado'],
                'opciones' => $preguntaData['opciones'] ?? null,
                'respuesta_correcta' => $preguntaData['respuesta_correcta'],
                'puntaje' => $puntaje,
                'orden' => $orden++,
            ]);
        }

        // Actualizar puntaje total
        $evaluacion->puntaje_total = $puntajeTotal;
        $evaluacion->save();
    }

    /**
     * Obtener preguntas de evaluación según el tema
     */
    private function getPreguntasEvaluacionPorTema(Tema $tema): array
    {
        $preguntasPorTema = [
            'Sumas y Restas' => [
                [
                    'tipo' => 'multiple',
                    'enunciado' => 'Resuelve: 8 + 5 = ?',
                    'opciones' => ['12', '13', '14', '15'],
                    'respuesta_correcta' => '13',
                    'puntaje' => 10,
                ],
                [
                    'tipo' => 'multiple',
                    'enunciado' => 'Resuelve: 15 - 7 = ?',
                    'opciones' => ['6', '7', '8', '9'],
                    'respuesta_correcta' => '8',
                    'puntaje' => 10,
                ],
                [
                    'tipo' => 'vf',
                    'enunciado' => 'La suma de 4 + 6 es 11.',
                    'respuesta_correcta' => 'false',
                    'puntaje' => 5,
                ],
                [
                    'tipo' => 'corta',
                    'enunciado' => 'Escribe el resultado: 20 - 8 = ?',
                    'respuesta_correcta' => '12',
                    'puntaje' => 10,
                ],
            ],
            'El Alfabeto' => [
                [
                    'tipo' => 'multiple',
                    'enunciado' => '¿Qué letra viene después de la M?',
                    'opciones' => ['N', 'Ñ', 'O', 'L'],
                    'respuesta_correcta' => 'N',
                    'puntaje' => 10,
                ],
                [
                    'tipo' => 'corta',
                    'enunciado' => 'Escribe las cinco vocales:',
                    'respuesta_correcta' => 'a,e,i,o,u',
                    'puntaje' => 15,
                ],
            ],
            'Saludos Básicos' => [
                [
                    'tipo' => 'multiple',
                    'enunciado' => '"Good night" se usa para:',
                    'opciones' => ['Saludar por la mañana', 'Despedirse por la noche', 'Saludar por la tarde', 'Agradecer'],
                    'respuesta_correcta' => 'Despedirse por la noche',
                    'puntaje' => 10,
                ],
                [
                    'tipo' => 'corta',
                    'enunciado' => 'Escribe "gracias" en inglés:',
                    'respuesta_correcta' => 'thank you',
                    'puntaje' => 10,
                ],
            ],
            'Los Seres Vivos' => [
                [
                    'tipo' => 'multiple',
                    'enunciado' => '¿Cuál es una característica de los seres vivos?',
                    'opciones' => ['Son de metal', 'Nacen y crecen', 'No necesitan agua', 'No se mueven'],
                    'respuesta_correcta' => 'Nacen y crecen',
                    'puntaje' => 10,
                ],
                [
                    'tipo' => 'vf',
                    'enunciado' => 'Las plantas son seres vivos.',
                    'respuesta_correcta' => 'true',
                    'puntaje' => 5,
                ],
            ],
        ];

        return $preguntasPorTema[$tema->titulo] ?? [
            [
                'tipo' => 'multiple',
                'enunciado' => 'Pregunta de ejemplo sobre ' . $tema->titulo,
                'opciones' => ['A', 'B', 'C', 'D'],
                'respuesta_correcta' => 'A',
                'puntaje' => 10,
            ],
        ];
    }
}
