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
                'nombre' => 'Mi Primer Juego',
                'descripcion' => 'Juega por primera vez y descubre lo divertido que es aprender.',
                'icono' => '🎮',
                'criterio' => ['juegos_completados' => 1],
                'color' => '#3B82F6',
                'puntos_bonus' => 40,
                'activo' => true,
            ],
            [
                'nombre' => 'Super Inicio',
                'descripcion' => 'Completa 3 juegos en un mismo dia.',
                'icono' => '🚀',
                'criterio' => ['juegos_en_un_dia' => 3],
                'color' => '#8B5CF6',
                'puntos_bonus' => 80,
                'activo' => true,
            ],
            [
                'nombre' => 'Primera Victoria',
                'descripcion' => 'Aprueba tu primera evaluacion.',
                'icono' => '🏅',
                'criterio' => ['evaluaciones_aprobadas' => 1],
                'color' => '#10B981',
                'puntos_bonus' => 50,
                'activo' => true,
            ],
            [
                'nombre' => 'Primer Paso',
                'descripcion' => 'Completa tu primer tema.',
                'icono' => '👣',
                'criterio' => ['temas_completados' => 1],
                'color' => '#14B8A6',
                'puntos_bonus' => 60,
                'activo' => true,
            ],
            [
                'nombre' => 'Gran Campeon',
                'descripcion' => 'Completa 3 temas y sigue avanzando.',
                'icono' => '🏆',
                'criterio' => ['temas_completados' => 3],
                'color' => '#F59E0B',
                'puntos_bonus' => 120,
                'activo' => true,
            ],
            [
                'nombre' => 'Explorador del Saber',
                'descripcion' => 'Completa al menos un tema en cada asignatura activa.',
                'icono' => '🧭',
                'criterio' => ['temas_por_asignatura' => 1],
                'color' => '#06B6D4',
                'puntos_bonus' => 180,
                'activo' => true,
            ],
            [
                'nombre' => 'Sin Equivocaciones',
                'descripcion' => 'Termina un juego sin cometer errores.',
                'icono' => '✨',
                'criterio' => ['juego_perfecto' => true],
                'color' => '#F97316',
                'puntos_bonus' => 90,
                'activo' => true,
            ],
            [
                'nombre' => 'Ojo de Aguila',
                'descripcion' => 'Logra 5 respuestas correctas seguidas.',
                'icono' => '🦅',
                'criterio' => ['racha_correctas' => 5],
                'color' => '#EF4444',
                'puntos_bonus' => 100,
                'activo' => true,
            ],
            [
                'nombre' => 'Respuestas Correctas Bronce',
                'descripcion' => 'Acumula 10 respuestas correctas.',
                'icono' => '🥉',
                'criterio' => ['respuestas_correctas_total' => 10],
                'color' => '#B45309',
                'puntos_bonus' => 60,
                'activo' => true,
            ],
            [
                'nombre' => 'Respuestas Correctas Plata',
                'descripcion' => 'Acumula 25 respuestas correctas.',
                'icono' => '🥈',
                'criterio' => ['respuestas_correctas_total' => 25],
                'color' => '#6B7280',
                'puntos_bonus' => 120,
                'activo' => true,
            ],
            [
                'nombre' => 'Respuestas Correctas Oro',
                'descripcion' => 'Acumula 50 respuestas correctas.',
                'icono' => '🥇',
                'criterio' => ['respuestas_correctas_total' => 50],
                'color' => '#D97706',
                'puntos_bonus' => 220,
                'activo' => true,
            ],
            [
                'nombre' => 'Aprendo Cada Dia',
                'descripcion' => 'Supera tu mejor puntaje anterior en un juego.',
                'icono' => '📈',
                'criterio' => ['mejora_puntaje' => true],
                'color' => '#22C55E',
                'puntos_bonus' => 70,
                'activo' => true,
            ],
            [
                'nombre' => 'Nunca me Rindo',
                'descripcion' => 'Intentalo al menos 3 veces sin rendirte.',
                'icono' => '💪',
                'criterio' => ['numero_intento_minimo' => 3],
                'color' => '#A855F7',
                'puntos_bonus' => 85,
                'activo' => true,
            ],
            [
                'nombre' => 'Rey de las Sumas',
                'descripcion' => 'Completa tu primer tema de Matematicas.',
                'icono' => '🔢',
                'criterio' => ['temas_completados_asignatura' => 1, 'asignatura_slug' => 'matematicas'],
                'color' => '#2563EB',
                'puntos_bonus' => 90,
                'activo' => true,
            ],
            [
                'nombre' => 'Amigo de las Letras',
                'descripcion' => 'Completa tu primer tema de Lenguaje.',
                'icono' => '📚',
                'criterio' => ['temas_completados_asignatura' => 1, 'asignatura_slug' => 'lenguaje'],
                'color' => '#16A34A',
                'puntos_bonus' => 90,
                'activo' => true,
            ],
            [
                'nombre' => 'Cientifico Curioso',
                'descripcion' => 'Completa tu primer tema de Ciencias.',
                'icono' => '🔬',
                'criterio' => ['temas_completados_asignatura' => 1, 'asignatura_slug' => 'ciencias'],
                'color' => '#F59E0B',
                'puntos_bonus' => 90,
                'activo' => true,
            ],
            [
                'nombre' => 'Velocista',
                'descripcion' => 'Completa un juego en menos de 60 segundos.',
                'icono' => '⚡',
                'criterio' => ['tiempo_maximo_segundos' => 60],
                'color' => '#F97316',
                'puntos_bonus' => 110,
                'activo' => true,
            ],
            [
                'nombre' => 'Racha Perfecta',
                'descripcion' => 'Consigue 3 evaluaciones perfectas seguidas.',
                'icono' => '🔥',
                'criterio' => ['evaluaciones_perfectas_consecutivas' => 3],
                'color' => '#DC2626',
                'puntos_bonus' => 180,
                'activo' => true,
            ],
            [
                'nombre' => 'Tres Dias Aprendiendo',
                'descripcion' => 'Aprende 3 dias seguidos.',
                'icono' => '📅',
                'criterio' => ['dias_consecutivos' => 3],
                'color' => '#0EA5E9',
                'puntos_bonus' => 130,
                'activo' => true,
            ],
            [
                'nombre' => 'Semana de Aprendizaje',
                'descripcion' => 'Aprende durante 7 dias seguidos.',
                'icono' => '🌟',
                'criterio' => ['dias_consecutivos' => 7],
                'color' => '#7C3AED',
                'puntos_bonus' => 260,
                'activo' => true,
            ],
            [
                'nombre' => 'Cazador de Medallas',
                'descripcion' => 'Desbloquea 5 logros.',
                'icono' => '🎖️',
                'criterio' => ['logros_obtenidos' => 5],
                'color' => '#EAB308',
                'puntos_bonus' => 200,
                'activo' => true,
            ],
            [
                'nombre' => 'Top 10',
                'descripcion' => 'Entra al top 10 del ranking general.',
                'icono' => '🏁',
                'criterio' => ['posicion_ranking' => 10],
                'color' => '#F59E0B',
                'puntos_bonus' => 300,
                'activo' => true,
            ],
            [
                'nombre' => 'Campeon del Conocimiento',
                'descripcion' => 'Alcanza el primer lugar del ranking general.',
                'icono' => '👑',
                'criterio' => ['posicion_ranking' => 1],
                'color' => '#D97706',
                'puntos_bonus' => 600,
                'activo' => true,
            ],
        ];

        foreach ($logros as $logro) {
            Logro::updateOrCreate(
                ['nombre' => $logro['nombre']],
                $logro
            );
        }
    }
}
