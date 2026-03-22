<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

class LogroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logros = [
            [
                'nombre' => 'Primer Paso',
                'descripcion' => 'Completar tu primer tema',
                'icono' => '👣',
                'criterio' => ['temas_completados' => 1],
                'color' => '#10B981',
                'puntos_bonus' => 50,
                'activo' => true,
            ],
            [
                'nombre' => 'Racha Perfecta',
                'descripcion' => 'Obtener 5 evaluaciones perfectas seguidas',
                'icono' => '🔥',
                'criterio' => ['evaluaciones_perfectas_consecutivas' => 5],
                'color' => '#EF4444',
                'puntos_bonus' => 200,
                'activo' => true,
            ],
            [
                'nombre' => 'Velocista',
                'descripcion' => 'Completar un juego en menos de 30 segundos',
                'icono' => '⚡',
                'criterio' => ['tiempo_maximo_segundos' => 30],
                'color' => '#F59E0B',
                'puntos_bonus' => 100,
                'activo' => true,
            ],
            [
                'nombre' => 'Persistente',
                'descripcion' => 'Usar los 5 intentos antes de lograrlo',
                'icono' => '💪',
                'criterio' => ['intentos_usados' => 5],
                'color' => '#8B5CF6',
                'puntos_bonus' => 75,
                'activo' => true,
            ],
            [
                'nombre' => 'Maestro de Matemáticas',
                'descripcion' => 'Completar Matemáticas al 100%',
                'icono' => '📐',
                'criterio' => ['asignatura_completada' => true, 'asignatura_slug' => 'matematicas'],
                'color' => '#3B82F6',
                'puntos_bonus' => 500,
                'activo' => true,
            ],
            [
                'nombre' => 'Maestro de Lenguaje',
                'descripcion' => 'Completar Lenguaje al 100%',
                'icono' => '📖',
                'criterio' => ['asignatura_completada' => true, 'asignatura_slug' => 'lenguaje'],
                'color' => '#10B981',
                'puntos_bonus' => 500,
                'activo' => true,
            ],
            [
                'nombre' => 'Top 10',
                'descripcion' => 'Entrar al top 10 del ranking general',
                'icono' => '🏆',
                'criterio' => ['posicion_ranking' => 10],
                'color' => '#F59E0B',
                'puntos_bonus' => 300,
                'activo' => true,
            ],
            [
                'nombre' => 'Campeón',
                'descripcion' => 'Alcanzar el primer lugar del ranking',
                'icono' => '👑',
                'criterio' => ['posicion_ranking' => 1],
                'color' => '#F59E0B',
                'puntos_bonus' => 1000,
                'activo' => true,
            ],
            [
                'nombre' => 'Explorador',
                'descripcion' => 'Completar al menos un tema en cada asignatura',
                'icono' => '🧭',
                'criterio' => ['temas_por_asignatura' => 1],
                'color' => '#06B6D4',
                'puntos_bonus' => 150,
                'activo' => true,
            ],
            [
                'nombre' => 'Experto',
                'descripcion' => 'Completar 10 temas',
                'icono' => '🎓',
                'criterio' => ['temas_completados' => 10],
                'color' => '#EC4899',
                'puntos_bonus' => 250,
                'activo' => true,
            ],
        ];

        foreach ($logros as $logro) {
            Logro::create($logro);
        }
    }
}
