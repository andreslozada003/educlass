<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ColegioFactory extends Factory
{
    protected $model = \App\Models\Colegio::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company() . ' School',
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'logo' => null,
            'activo' => true,
        ];
    }
}
