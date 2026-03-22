<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'tipo' => 'estudiante',
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
            'telefono' => fake()->optional()->phoneNumber(),
            'colegio_id' => null,
            'activo' => true,
        ];
    }

    public function estudiante(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'estudiante',
        ]);
    }

    public function docente(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'docente',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'admin',
        ]);
    }
}
