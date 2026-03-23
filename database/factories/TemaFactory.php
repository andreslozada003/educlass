<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemaFactory extends Factory
{
    protected $model = \App\Models\Tema::class;

    public function definition(): array
    {
        $titulo = fake()->sentence(4);
        
        return [
            'asignatura_id' => \App\Models\Asignatura::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo . '-' . fake()->unique()->numberBetween(1, 10000)),
            'contenido' => '<p>' . fake()->paragraphs(3, true) . '</p>',
            'dificultad' => fake()->numberBetween(1, 4),
            'periodo_academico' => fake()->numberBetween(1, 3),
            'orden' => fake()->numberBetween(1, 20),
            'imagen_destacada' => null,
            'video_url' => null,
            'tiempo_estimado_minutos' => fake()->numberBetween(10, 60),
            'activo' => true,
            'docente_creador_id' => \App\Models\User::factory()->docente(),
        ];
    }
}
