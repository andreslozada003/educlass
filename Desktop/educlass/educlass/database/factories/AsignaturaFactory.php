<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AsignaturaFactory extends Factory
{
    protected $model = \App\Models\Asignatura::class;

    public function definition(): array
    {
        $nombre = fake()->randomElement(['Matemáticas', 'Lenguaje', 'Inglés', 'Ciencias', 'Historia', 'Geografía']);
        
        return [
            'nombre' => $nombre,
            'descripcion' => fake()->sentence(),
            'slug' => Str::slug($nombre . '-' . fake()->unique()->numberBetween(1, 1000)),
            'icono' => fake()->randomElement(['📚', '🔢', '🌍', '🔬', '📜', '🗺️']),
            'color_primario' => fake()->hexColor(),
            'color_secundario' => fake()->hexColor(),
            'orden' => fake()->numberBetween(1, 10),
            'activa' => true,
        ];
    }
}
