<?php

namespace Database\Seeders;

use App\Models\Tema;
use App\Models\Asignatura;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $docente = User::docentes()->first();
        $asignaturas = Asignatura::all();

        $temasData = [
            'matematicas' => [
                [
                    'titulo' => 'Sumas y Restas',
                    'contenido' => '<h2>Sumas y Restas Básicas</h2><p>Aprende a sumar y restar números de una cifra.</p><p>La suma es juntar cantidades y la resta es quitar.</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Multiplicación',
                    'contenido' => '<h2>Tablas de Multiplicar</h2><p>Aprende las tablas del 1 al 5.</p><p>La multiplicación es sumar varias veces el mismo número.</p>',
                    'dificultad' => 2,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'División',
                    'contenido' => '<h2>División Básica</h2><p>Aprende a dividir números pequeños.</p><p>La división es repartir en partes iguales.</p>',
                    'dificultad' => 2,
                    'periodo' => 2,
                ],
                [
                    'titulo' => 'Fracciones',
                    'contenido' => '<h2>Introducción a Fracciones</h2><p>Aprende qué son las fracciones y cómo representarlas.</p>',
                    'dificultad' => 3,
                    'periodo' => 2,
                ],
            ],
            'lenguaje' => [
                [
                    'titulo' => 'El Alfabeto',
                    'contenido' => '<h2>Conociendo las Letras</h2><p>Aprende el abecedario y el sonido de cada letra.</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Vocales y Consonantes',
                    'contenido' => '<h2>Diferencia entre Vocales y Consonantes</h2><p>Las vocales son: A, E, I, O, U. El resto son consonantes.</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Sílabas',
                    'contenido' => '<h2>Formando Sílabas</h2><p>Aprende a unir consonantes con vocales para formar sílabas.</p>',
                    'dificultad' => 2,
                    'periodo' => 2,
                ],
                [
                    'titulo' => 'Palabras Agudas',
                    'contenido' => '<h2>Acentuación de Palabras Agudas</h2><p>Las palabras agudas se acentúan en la última sílaba.</p>',
                    'dificultad' => 3,
                    'periodo' => 3,
                ],
            ],
            'ingles' => [
                [
                    'titulo' => 'Saludos Básicos',
                    'contenido' => '<h2>Greetings</h2><p>Hello, Hi, Good morning, Good afternoon, Good night.</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Números del 1-20',
                    'contenido' => '<h2>Numbers 1-20</h2><p>One, Two, Three... Twenty</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Colores',
                    'contenido' => '<h2>Colors</h2><p>Red, Blue, Green, Yellow, Black, White...</p>',
                    'dificultad' => 2,
                    'periodo' => 2,
                ],
                [
                    'titulo' => 'Mi Familia',
                    'contenido' => '<h2>My Family</h2><p>Mother, Father, Sister, Brother, Grandparents...</p>',
                    'dificultad' => 2,
                    'periodo' => 2,
                ],
            ],
            'ciencias' => [
                [
                    'titulo' => 'Los Seres Vivos',
                    'contenido' => '<h2>Características de los Seres Vivos</h2><p>Nacen, crecen, se reproducen y mueren.</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'El Cuerpo Humano',
                    'contenido' => '<h2>Partes del Cuerpo</h2><p>Cabeza, brazos, piernas, manos, pies...</p>',
                    'dificultad' => 1,
                    'periodo' => 1,
                ],
                [
                    'titulo' => 'Los Animales',
                    'contenido' => '<h2>Clasificación de Animales</h2><p>Vertebrados e invertebrados. Mamíferos, aves, reptiles...</p>',
                    'dificultad' => 2,
                    'periodo' => 2,
                ],
                [
                    'titulo' => 'El Sistema Solar',
                    'contenido' => '<h2>Planetas del Sistema Solar</h2><p>Mercurio, Venus, Tierra, Marte, Júpiter...</p>',
                    'dificultad' => 3,
                    'periodo' => 3,
                ],
            ],
        ];

        foreach ($asignaturas as $asignatura) {
            $temas = $temasData[$asignatura->slug] ?? [];
            $orden = 1;

            foreach ($temas as $temaData) {
                Tema::create([
                    'asignatura_id' => $asignatura->id,
                    'titulo' => $temaData['titulo'],
                    'slug' => \Illuminate\Support\Str::slug($temaData['titulo']) . '-' . $asignatura->slug,
                    'contenido' => $temaData['contenido'],
                    'dificultad' => $temaData['dificultad'],
                    'periodo_academico' => $temaData['periodo'],
                    'orden' => $orden++,
                    'tiempo_estimado_minutos' => 15,
                    'activo' => true,
                    'docente_creador_id' => $docente->id,
                ]);
            }
        }
    }
}
