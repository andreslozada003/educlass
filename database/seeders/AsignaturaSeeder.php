<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use Illuminate\Database\Seeder;

class AsignaturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $asignaturas = [
            [
                'nombre' => 'Matemáticas',
                'descripcion' => 'Aprende matemáticas de forma divertida con juegos interactivos',
                'slug' => 'matematicas',
                'icono' => '🔢',
                'color_primario' => '#3B82F6',
                'color_secundario' => '#93C5FD',
                'orden' => 1,
                'activa' => true,
            ],
            [
                'nombre' => 'Lenguaje',
                'descripcion' => 'Mejora tus habilidades de lectura, escritura y comprensión',
                'slug' => 'lenguaje',
                'icono' => '📚',
                'color_primario' => '#10B981',
                'color_secundario' => '#6EE7B7',
                'orden' => 2,
                'activa' => true,
            ],
            [
                'nombre' => 'Inglés',
                'descripcion' => 'Aprende inglés de manera interactiva y divertida',
                'slug' => 'ingles',
                'icono' => '🌍',
                'color_primario' => '#EF4444',
                'color_secundario' => '#FCA5A5',
                'orden' => 3,
                'activa' => true,
            ],
            [
                'nombre' => 'Ciencias',
                'descripcion' => 'Explora el mundo de la ciencia con experimentos virtuales',
                'slug' => 'ciencias',
                'icono' => '🔬',
                'color_primario' => '#F59E0B',
                'color_secundario' => '#FCD34D',
                'orden' => 4,
                'activa' => true,
            ],
        ];

        foreach ($asignaturas as $asignatura) {
            Asignatura::create($asignatura);
        }
    }
}
