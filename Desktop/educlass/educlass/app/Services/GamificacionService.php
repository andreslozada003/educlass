<?php

namespace App\Services;

use App\Models\User;
use App\Models\Juego;
use App\Models\IntentosJuego;
use App\Models\Logro;
use App\Models\LogrosEstudiante;
use App\Models\Ranking;




/**
 * Service GamificacionService - Gestiona puntajes, niveles, logros y rankings
 */
class GamificacionService
{
    /**
     * Calcular puntaje con bonificaciones
     */
    public function calcularPuntaje(
        int $puntajeBase,
        int $duracionSegundos,
        ?int $tiempoLimiteSegundos = null,
        int $rachaCorrectas = 0
    ): array {
        $bonificacionTiempo = 0;
        $bonificacionRacha = 0;

        // Bonificación por tiempo
        if ($tiempoLimiteSegundos && $tiempoLimiteSegundos > 0) {
            $tiempoRestante = max(0, $tiempoLimiteSegundos - $duracionSegundos);
            $porcentajeRestante = ($tiempoRestante / $tiempoLimiteSegundos) * 100;
            $bonificacionTiempo = (int) ($puntajeBase * ($porcentajeRestante / 100) * 0.2);
        }

        // Bonificación por racha
        if ($rachaCorrectas >= config('gamificacion.puntajes.racha_minima', 3)) {
            $bonificacionRacha = (int) ($puntajeBase * (config('gamificacion.puntajes.racha_bonus', 10) / 100));
        }

        $puntajeFinal = $puntajeBase + $bonificacionTiempo + $bonificacionRacha;

        return [
            'puntaje_base' => $puntajeBase,
            'bonificacion_tiempo' => $bonificacionTiempo,
            'bonificacion_racha' => $bonificacionRacha,
            'puntaje_final' => $puntajeFinal,
        ];
    }

    /**
     * Registrar intento de juego
     */
    public function registrarIntento(
        User $estudiante,
        Juego $juego,
        array $respuestas,
        int $duracionSegundos,
        bool $completado = true
    ): IntentosJuego {
        // Calcular puntaje base
        $puntajeBase = 0;
        $rachaActual = 0;
        $rachaMaxima = 0;

        foreach ($respuestas as $preguntaId => $respuesta) {
            $pregunta = $juego->preguntasActivas->firstWhere('id', $preguntaId);
            if ($pregunta) {
                if ($pregunta->verificarRespuesta($respuesta)) {
                    $puntajeBase += $pregunta->puntaje;
                    $rachaActual++;
                    $rachaMaxima = max($rachaMaxima, $rachaActual);
                } else {
                    $rachaActual = 0;
                }
            }
        }

        // Calcular puntaje final con bonificaciones
        $calculo = $this->calcularPuntaje(
            $puntajeBase,
            $duracionSegundos,
            $juego->tiempo_limite_segundos,
            $rachaMaxima
        );

        // Obtener número de intento
        $numeroIntento = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('juego_id', $juego->id)
            ->count() + 1;

        // Crear registro de intento
        $intento = IntentosJuego::create([
            'estudiante_id' => $estudiante->id,
            'juego_id' => $juego->id,
            'puntaje_obtenido' => $calculo['puntaje_final'],
            'respuestas' => $respuestas,
            'duracion_segundos' => $duracionSegundos,
            'numero_intento' => $numeroIntento,
            'completado' => $completado,
            'fecha_intento' => now(),
        ]);

        // Verificar logros
        $this->verificarLogros($estudiante, 'juego_completado', [
            'juego' => $juego,
            'intento' => $intento,
            'racha_maxima' => $rachaMaxima,
        ]);

        // Actualizar ranking
        $this->actualizarRankingJuegos($estudiante, $juego->tema->asignatura_id);

        return $intento;
    }

    /**
     * Verificar y otorgar logros
     */
    public function verificarLogros(User $estudiante, string $evento, array $contexto = []): array
    {
        $logrosObtenidos = [];

        // Obtener logros que el estudiante aún no tiene
        $logrosPendientes = Logro::activos()
            ->whereNotIn('id', function ($query) use ($estudiante) {
                $query->select('logro_id')
                    ->from('logros_estudiantes')
                    ->where('estudiante_id', $estudiante->id);
            })
            ->get();

        foreach ($logrosPendientes as $logro) {
            $cumple = false;

            switch ($evento) {
                case 'tema_completado':
                    $cumple = $this->verificarLogroTemaCompletado($logro, $estudiante);
                    break;

                case 'juego_completado':
                    $cumple = $this->verificarLogroJuego($logro, $estudiante, $contexto);
                    break;

                case 'evaluacion_aprobada':
                    $cumple = $this->verificarLogroEvaluacion($logro, $estudiante, $contexto);
                    break;

                case 'ranking_alcanzado':
                    $cumple = $this->verificarLogroRanking($logro, $estudiante, $contexto);
                    break;
            }

            if ($cumple) {
                $logro->otorgar($estudiante->id, $contexto);
                $logrosObtenidos[] = $logro;
            }
        }

        return $logrosObtenidos;
    }

    /**
     * Verificar logro por temas completados
     */
    private function verificarLogroTemaCompletado(Logro $logro, User $estudiante): bool
    {
        $criterio = $logro->criterio;

        if (isset($criterio['temas_completados'])) {
            $temasCompletados = $estudiante->progresoEstudiante()
                ->completados()
                ->count();
            return $temasCompletados >= $criterio['temas_completados'];
        }

        if (isset($criterio['asignatura_completada'])) {
            // Verificar si completó alguna asignatura al 100%
            $asignaturas = \App\Models\Asignatura::activas()->get();
            foreach ($asignaturas as $asignatura) {
                $progresionService = app(ProgresionService::class);
                $progreso = $progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id);
                if ($progreso['porcentaje'] >= 100) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar logro por juego
     */
    private function verificarLogroJuego(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio;

        if (isset($criterio['tiempo_maximo_segundos'])) {
            $intento = $contexto['intento'] ?? null;
            if ($intento) {
                return $intento->duracion_segundos <= $criterio['tiempo_maximo_segundos'];
            }
        }

        if (isset($criterio['intentos_usados'])) {
            $juego = $contexto['juego'] ?? null;
            if ($juego) {
                $intentosUsados = IntentosJuego::where('estudiante_id', $estudiante->id)
                    ->where('juego_id', $juego->id)
                    ->where('completado', true)
                    ->count();
                return $intentosUsados >= $criterio['intentos_usados'];
            }
        }

        return false;
    }

    /**
     * Verificar logro por evaluación
     */
    private function verificarLogroEvaluacion(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio;

        if (isset($criterio['evaluaciones_perfectas_consecutivas'])) {
            // Obtener últimas evaluaciones del estudiante
            $resultados = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
                ->where('aprobado', true)
                ->orderBy('fecha_realizacion', 'desc')
                ->limit($criterio['evaluaciones_perfectas_consecutivas'])
                ->get();

            if ($resultados->count() < $criterio['evaluaciones_perfectas_consecutivas']) {
                return false;
            }

            // Verificar si todas son perfectas (100%)
            foreach ($resultados as $resultado) {
                if ($resultado->porcentaje_obtenido < 100) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Verificar logro por ranking
     */
    private function verificarLogroRanking(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio;

        if (isset($criterio['posicion_ranking'])) {
            $posicion = $contexto['posicion'] ?? 999;
            return $posicion <= $criterio['posicion_ranking'];
        }

        return false;
    }

    /**
     * Actualizar ranking de juegos
     */
    public function actualizarRankingJuegos(User $estudiante, ?int $asignaturaId = null): void
    {
        // Calcular puntaje total de juegos
        $query = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true);

        if ($asignaturaId) {
            $query->whereHas('juego.tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        $puntajeTotal = $query->sum('puntaje_obtenido');

        // Actualizar o crear ranking
        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'juegos',
            ],
            [
                'puntaje_total' => $puntajeTotal,
                'fecha_actualizacion' => now(),
            ]
        );

        // Recalcular posiciones
        Ranking::recalcularRanking('juegos', $asignaturaId);
    }

    /**
     * Actualizar ranking de evaluaciones
     */
    public function actualizarRankingEvaluaciones(User $estudiante, ?int $asignaturaId = null): void
    {
        $query = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true);

        if ($asignaturaId) {
            $query->whereHas('evaluacion.tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        $puntajeTotal = $query->sum('puntaje_obtenido');

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'evaluaciones',
            ],
            [
                'puntaje_total' => $puntajeTotal,
                'fecha_actualizacion' => now(),
            ]
        );

        Ranking::recalcularRanking('evaluaciones', $asignaturaId);
    }

    /**
     * Actualizar ranking de temas completados
     */
    public function actualizarRankingTemas(User $estudiante, ?int $asignaturaId = null): void
    {
        $query = \App\Models\ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->completados();

        if ($asignaturaId) {
            $query->whereHas('tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        $temasCompletados = $query->count();

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'temas',
            ],
            [
                'puntaje_total' => $temasCompletados,
                'fecha_actualizacion' => now(),
            ]
        );

        Ranking::recalcularRanking('temas', $asignaturaId);
    }

    /**
     * Actualizar ranking general
     */
    public function actualizarRankingGeneral(User $estudiante): void
    {
        // Calcular puntaje combinado (juegos + evaluaciones + temas)
        $puntajeJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->sum('puntaje_obtenido');

        $puntajeEvaluaciones = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->sum('puntaje_obtenido');

        $temasCompletados = \App\Models\ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->completados()
            ->count() * 100; // 100 puntos por tema

        $puntajeTotal = $puntajeJuegos + $puntajeEvaluaciones + $temasCompletados;

        // Calcular nivel alcanzado
        $progresionService = app(ProgresionService::class);
        $resumen = $progresionService->getResumenProgreso($estudiante);

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => null,
                'categoria' => 'general',
            ],
            [
                'puntaje_total' => $puntajeTotal,
                'nivel_alcanzado' => $resumen['nivel_global'],
                'fecha_actualizacion' => now(),
            ]
        );

        Ranking::recalcularRanking('general', null);
    }

    /**
     * Obtener estadísticas del estudiante
     */
    public function getEstadisticas(User $estudiante): array
    {
        $totalJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->count();

        $puntajeTotalJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->sum('puntaje_obtenido');

        $totalEvaluaciones = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->count();

        $evaluacionesAprobadas = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->aprobados()
            ->count();
        $puntajeEvaluaciones = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->sum('puntaje_obtenido');
        $temasCompletados = \App\Models\ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->completados()
            ->count();
        $puntosTotales = $puntajeTotalJuegos + $puntajeEvaluaciones + ($temasCompletados * 100);

        $logrosObtenidos = LogrosEstudiante::where('estudiante_id', $estudiante->id)->count();

        $posicionGeneral = Ranking::where('estudiante_id', $estudiante->id)
            ->porCategoria('general')
            ->general()
            ->first()
            ?->posicion ?? 0;

        return [
            'total_juegos' => $totalJuegos,
            'puntaje_total_juegos' => $puntajeTotalJuegos,
            'puntos_totales' => $puntosTotales,
            'total_evaluaciones' => $totalEvaluaciones,
            'evaluaciones_aprobadas' => $evaluacionesAprobadas,
            'tasa_aprobacion' => $totalEvaluaciones > 0 ? round(($evaluacionesAprobadas / $totalEvaluaciones) * 100, 2) : 0,
            'logros_obtenidos' => $logrosObtenidos,
            'posicion_ranking' => $posicionGeneral,
            // Alias de compatibilidad para vistas antiguas
            'juegos_completados' => $totalJuegos,
            'evaluaciones_completadas' => $evaluacionesAprobadas,
            'logros' => $logrosObtenidos,
        ];
    }
        /**
     * Contar los juegos completados por estudiante en una asignatura.
     */
       public function getJuegosCompletados($estudiante, $asignaturaId)
    {
        return \App\Models\IntentosJuego::where('estudiante_id', $estudiante->id) // 👈 columna correcta
            ->whereHas('juego', function ($q) use ($asignaturaId) {
                $q->where('tema_id', $asignaturaId); // ajusta si necesitas filtrar por asignatura
            })
            ->where('completado', true)
            ->count();
    }



}
