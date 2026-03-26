<?php

namespace App\Services;

use App\Models\Asignatura;
use App\Models\IntentosJuego;
use App\Models\Juego;
use App\Models\Logro;
use App\Models\LogrosEstudiante;
use App\Models\ProgresoEstudiante;
use App\Models\Ranking;
use App\Models\ResultadosEvaluacion;
use App\Models\User;
use Carbon\Carbon;

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

        if ($tiempoLimiteSegundos && $tiempoLimiteSegundos > 0) {
            $tiempoRestante = max(0, $tiempoLimiteSegundos - $duracionSegundos);
            $porcentajeRestante = ($tiempoRestante / $tiempoLimiteSegundos) * 100;
            $bonificacionTiempo = (int) ($puntajeBase * ($porcentajeRestante / 100) * 0.2);
        }

        if ($rachaCorrectas >= config('gamificacion.puntajes.racha_minima', 3)) {
            $bonificacionRacha = (int) ($puntajeBase * (config('gamificacion.puntajes.racha_bonus', 10) / 100));
        }

        return [
            'puntaje_base' => $puntajeBase,
            'bonificacion_tiempo' => $bonificacionTiempo,
            'bonificacion_racha' => $bonificacionRacha,
            'puntaje_final' => $puntajeBase + $bonificacionTiempo + $bonificacionRacha,
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
        $puntajeBase = 0;
        $rachaActual = 0;
        $rachaMaxima = 0;

        foreach ($respuestas as $preguntaId => $respuesta) {
            $pregunta = $juego->preguntasActivas->firstWhere('id', $preguntaId);

            if (!$pregunta) {
                continue;
            }

            if ($pregunta->verificarRespuesta($respuesta)) {
                $puntajeBase += $pregunta->puntaje;
                $rachaActual++;
                $rachaMaxima = max($rachaMaxima, $rachaActual);
            } else {
                $rachaActual = 0;
            }
        }

        $calculo = $this->calcularPuntaje(
            $puntajeBase,
            $duracionSegundos,
            $juego->tiempo_limite_segundos,
            $rachaMaxima
        );

        $numeroIntento = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('juego_id', $juego->id)
            ->count() + 1;

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

        $intento->setAttribute('racha_maxima', $rachaMaxima);

        $this->actualizarRankingJuegos($estudiante, $juego->tema->asignatura_id);
        $this->actualizarRankingGeneral($estudiante);

        return $intento->load('juego.preguntasActivas', 'juego.tema.asignatura');
    }

    /**
     * Verificar y otorgar logros
     */
    public function verificarLogros(User $estudiante, string $evento, array $contexto = []): array
    {
        $logrosObtenidos = [];

        $logrosPendientes = Logro::activos()
            ->whereNotIn('id', function ($query) use ($estudiante) {
                $query->select('logro_id')
                    ->from('logros_estudiantes')
                    ->where('estudiante_id', $estudiante->id);
            })
            ->get();

        foreach ($logrosPendientes as $logro) {
            $cumple = $this->verificarLogroUniversal($logro, $estudiante, $contexto);

            if (!$cumple) {
                $cumple = match ($evento) {
                    'tema_completado' => $this->verificarLogroTemaCompletado($logro, $estudiante, $contexto),
                    'juego_completado' => $this->verificarLogroJuego($logro, $estudiante, $contexto),
                    'evaluacion_aprobada' => $this->verificarLogroEvaluacion($logro, $estudiante, $contexto),
                    'ranking_alcanzado' => $this->verificarLogroRanking($logro, $estudiante, $contexto),
                    default => false,
                };
            }

            if ($cumple) {
                $logro->otorgar($estudiante->id, $contexto);
                $logrosObtenidos[] = $logro;
            }
        }

        return $logrosObtenidos;
    }

    /**
     * Verificaciones globales reutilizables.
     */
    private function verificarLogroUniversal(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio ?? [];

        if (isset($criterio['logros_obtenidos'])) {
            return LogrosEstudiante::where('estudiante_id', $estudiante->id)->count() >= $criterio['logros_obtenidos'];
        }

        if (isset($criterio['dias_consecutivos'])) {
            return $this->getDiasConsecutivosAprendizaje($estudiante) >= $criterio['dias_consecutivos'];
        }

        if (isset($criterio['respuestas_correctas_total'])) {
            return $this->getTotalRespuestasCorrectas($estudiante) >= $criterio['respuestas_correctas_total'];
        }

        return false;
    }

    /**
     * Verificar logro por temas completados.
     */
    private function verificarLogroTemaCompletado(Logro $logro, User $estudiante, array $contexto = []): bool
    {
        $criterio = $logro->criterio ?? [];

        if (isset($criterio['temas_completados'])) {
            $temasCompletados = $estudiante->progresoEstudiante()->completados()->count();
            return $temasCompletados >= $criterio['temas_completados'];
        }

        if (isset($criterio['temas_por_asignatura'])) {
            $temasRequeridos = (int) $criterio['temas_por_asignatura'];
            $asignaturasActivas = Asignatura::activas()->pluck('id');

            if ($asignaturasActivas->isEmpty()) {
                return false;
            }

            $conteoPorAsignatura = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
                ->where('estado', 'completado')
                ->join('temas', 'progreso_estudiantes.tema_id', '=', 'temas.id')
                ->whereIn('temas.asignatura_id', $asignaturasActivas)
                ->selectRaw('temas.asignatura_id, COUNT(*) as total')
                ->groupBy('temas.asignatura_id')
                ->pluck('total', 'temas.asignatura_id');

            foreach ($asignaturasActivas as $asignaturaId) {
                if ((int) ($conteoPorAsignatura[$asignaturaId] ?? 0) < $temasRequeridos) {
                    return false;
                }
            }

            return true;
        }

        if (isset($criterio['temas_completados_asignatura']) && !empty($criterio['asignatura_slug'])) {
            return $this->getTemasCompletadosPorAsignatura($estudiante, $criterio['asignatura_slug']) >= $criterio['temas_completados_asignatura'];
        }

        if (isset($criterio['asignatura_completada'])) {
            if (!empty($criterio['asignatura_slug'])) {
                $progreso = $this->getProgresoAsignaturaPorSlug($estudiante, $criterio['asignatura_slug']);
                return ($progreso['porcentaje'] ?? 0) >= 100;
            }

            foreach (Asignatura::activas()->get() as $asignatura) {
                $progreso = app(ProgresionService::class)->getProgresoPorAsignatura($estudiante, $asignatura->id);
                if (($progreso['porcentaje'] ?? 0) >= 100) {
                    return true;
                }
            }
        }

        if (isset($criterio['nivel_actual_minimo'])) {
            if (!empty($criterio['asignatura_slug'])) {
                $progreso = $this->getProgresoAsignaturaPorSlug($estudiante, $criterio['asignatura_slug']);
                return ($progreso['nivel_actual'] ?? 1) >= $criterio['nivel_actual_minimo'];
            }

            foreach (Asignatura::activas()->get() as $asignatura) {
                $progreso = app(ProgresionService::class)->getProgresoPorAsignatura($estudiante, $asignatura->id);
                if (($progreso['nivel_actual'] ?? 1) >= $criterio['nivel_actual_minimo']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar logro por juego.
     */
    private function verificarLogroJuego(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio ?? [];
        $intento = $contexto['intento'] ?? null;
        $juego = $contexto['juego'] ?? optional($intento)->juego;

        if (isset($criterio['juegos_completados'])) {
            return IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->count() >= $criterio['juegos_completados'];
        }

        if (isset($criterio['juegos_en_un_dia'])) {
            $fecha = optional($intento?->fecha_intento)->toDateString() ?? now()->toDateString();

            return IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->whereDate('fecha_intento', $fecha)
                ->count() >= $criterio['juegos_en_un_dia'];
        }

        if (isset($criterio['tiempo_maximo_segundos']) && $intento) {
            return $intento->duracion_segundos <= $criterio['tiempo_maximo_segundos'];
        }

        if (isset($criterio['numero_intento_minimo']) && $intento) {
            return ($intento->numero_intento ?? 1) >= $criterio['numero_intento_minimo'];
        }

        if (!empty($criterio['mejora_puntaje']) && $intento) {
            $mejorPrevio = IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('juego_id', $intento->juego_id)
                ->where('id', '!=', $intento->id)
                ->where('completado', true)
                ->max('puntaje_obtenido');

            return !is_null($mejorPrevio) && $intento->puntaje_obtenido > $mejorPrevio;
        }

        if (!empty($criterio['juego_perfecto']) && $intento) {
            return $this->esIntentoPerfecto($intento);
        }

        if (isset($criterio['racha_correctas'])) {
            return ($contexto['racha_maxima'] ?? 0) >= $criterio['racha_correctas'];
        }

        if (isset($criterio['juego_tipo_completado']) && $juego) {
            return $juego->tipo === $criterio['juego_tipo_completado'];
        }

        if (isset($criterio['juegos_completados_asignatura']) && !empty($criterio['asignatura_slug'])) {
            $asignatura = Asignatura::where('slug', $criterio['asignatura_slug'])->first();

            if (!$asignatura) {
                return false;
            }

            return IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->whereHas('juego.tema', function ($query) use ($asignatura) {
                    $query->where('asignatura_id', $asignatura->id);
                })
                ->count() >= $criterio['juegos_completados_asignatura'];
        }

        if (isset($criterio['intentos_usados']) && $juego) {
            return IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('juego_id', $juego->id)
                ->where('completado', true)
                ->count() >= $criterio['intentos_usados'];
        }

        return false;
    }

    /**
     * Verificar logro por evaluacion.
     */
    private function verificarLogroEvaluacion(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio ?? [];
        $resultado = $contexto['resultado'] ?? null;

        if (isset($criterio['evaluaciones_perfectas_consecutivas'])) {
            $resultados = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
                ->where('aprobado', true)
                ->orderBy('fecha_realizacion', 'desc')
                ->limit($criterio['evaluaciones_perfectas_consecutivas'])
                ->get();

            if ($resultados->count() < $criterio['evaluaciones_perfectas_consecutivas']) {
                return false;
            }

            foreach ($resultados as $item) {
                if ($item->porcentaje_obtenido < 100) {
                    return false;
                }
            }

            return true;
        }

        if (isset($criterio['evaluaciones_aprobadas'])) {
            return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
                ->where('aprobado', true)
                ->count() >= $criterio['evaluaciones_aprobadas'];
        }

        if (!empty($criterio['evaluacion_perfecta']) && $resultado) {
            return $resultado->porcentaje_obtenido >= 100;
        }

        if (isset($criterio['evaluaciones_aprobadas_asignatura']) && !empty($criterio['asignatura_slug'])) {
            $asignatura = Asignatura::where('slug', $criterio['asignatura_slug'])->first();

            if (!$asignatura) {
                return false;
            }

            return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
                ->where('aprobado', true)
                ->whereHas('evaluacion.tema', function ($query) use ($asignatura) {
                    $query->where('asignatura_id', $asignatura->id);
                })
                ->count() >= $criterio['evaluaciones_aprobadas_asignatura'];
        }

        return false;
    }

    /**
     * Verificar logro por ranking.
     */
    private function verificarLogroRanking(Logro $logro, User $estudiante, array $contexto): bool
    {
        $criterio = $logro->criterio ?? [];

        if (isset($criterio['posicion_ranking'])) {
            $posicion = $contexto['posicion'] ?? Ranking::where('estudiante_id', $estudiante->id)
                ->whereNull('asignatura_id')
                ->where('categoria', 'general')
                ->value('posicion');

            return (int) ($posicion ?? 999) <= $criterio['posicion_ranking'];
        }

        return false;
    }

    /**
     * Actualizar ranking de juegos
     */
    public function actualizarRankingJuegos(User $estudiante, ?int $asignaturaId = null): void
    {
        $query = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true);

        if ($asignaturaId) {
            $query->whereHas('juego.tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'juegos',
            ],
            [
                'puntaje_total' => $query->sum('puntaje_obtenido'),
                'fecha_actualizacion' => now(),
            ]
        );

        Ranking::recalcularRanking('juegos', $asignaturaId);
    }

    /**
     * Actualizar ranking de evaluaciones
     */
    public function actualizarRankingEvaluaciones(User $estudiante, ?int $asignaturaId = null): void
    {
        $query = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true);

        if ($asignaturaId) {
            $query->whereHas('evaluacion.tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'evaluaciones',
            ],
            [
                'puntaje_total' => $query->sum('puntaje_obtenido'),
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
        $query = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->completados();

        if ($asignaturaId) {
            $query->whereHas('tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            });
        }

        Ranking::updateOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'asignatura_id' => $asignaturaId,
                'categoria' => 'temas',
            ],
            [
                'puntaje_total' => $query->count(),
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
        $puntajeJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->sum('puntaje_obtenido');

        $puntajeEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->sum('puntaje_obtenido');

        $temasCompletados = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->completados()
            ->count() * 100;

        $puntajeTotal = $puntajeJuegos + $puntajeEvaluaciones + $temasCompletados;
        $resumen = app(ProgresionService::class)->getResumenProgreso($estudiante);

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

        $rankingGeneral = Ranking::where('estudiante_id', $estudiante->id)
            ->whereNull('asignatura_id')
            ->where('categoria', 'general')
            ->first();

        if ($rankingGeneral) {
            $this->verificarLogros($estudiante, 'ranking_alcanzado', [
                'posicion' => $rankingGeneral->posicion,
                'ranking' => $rankingGeneral,
            ]);
        }
    }

    /**
     * Obtener estadisticas del estudiante
     */
    public function getEstadisticas(User $estudiante): array
    {
        $totalJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->count();

        $puntajeTotalJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->sum('puntaje_obtenido');

        $totalEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)->count();
        $evaluacionesAprobadas = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->aprobados()
            ->count();

        $puntajeEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->sum('puntaje_obtenido');

        $temasCompletados = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
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
        return IntentosJuego::where('estudiante_id', $estudiante->id)
            ->whereHas('juego.tema', function ($q) use ($asignaturaId) {
                $q->where('asignatura_id', $asignaturaId);
            })
            ->where('completado', true)
            ->count();
    }

    /**
     * Calcular total de respuestas correctas del estudiante.
     */
    private function getTotalRespuestasCorrectas(User $estudiante): int
    {
        $correctasJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->with(['juego.preguntasActivas'])
            ->get()
            ->sum(function ($intento) {
                return $intento->respuestas_correctas;
            });

        $correctasEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->with(['evaluacion.preguntasOrdenadas'])
            ->get()
            ->sum(function ($resultado) {
                return $resultado->respuestas_correctas;
            });

        return $correctasJuegos + $correctasEvaluaciones;
    }

    /**
     * Calcular racha actual de dias consecutivos con actividad.
     */
    private function getDiasConsecutivosAprendizaje(User $estudiante): int
    {
        $fechasJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->pluck('fecha_intento')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString());

        $fechasEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->pluck('fecha_realizacion')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString());

        $fechas = $fechasJuegos
            ->merge($fechasEvaluaciones)
            ->unique()
            ->sortDesc()
            ->values();

        if ($fechas->isEmpty()) {
            return 0;
        }

        $racha = 1;
        $anterior = Carbon::parse($fechas->first());

        for ($i = 1; $i < $fechas->count(); $i++) {
            $actual = Carbon::parse($fechas[$i]);
            $diferencia = $anterior->diffInDays($actual);

            if ($diferencia === 1) {
                $racha++;
                $anterior = $actual;
                continue;
            }

            if ($diferencia > 1) {
                break;
            }
        }

        return $racha;
    }

    /**
     * Obtener progreso de una asignatura por slug.
     */
    private function getProgresoAsignaturaPorSlug(User $estudiante, string $slug): ?array
    {
        $asignatura = Asignatura::where('slug', $slug)->first();

        if (!$asignatura) {
            return null;
        }

        return app(ProgresionService::class)->getProgresoPorAsignatura($estudiante, $asignatura->id);
    }

    /**
     * Obtener temas completados por asignatura.
     */
    private function getTemasCompletadosPorAsignatura(User $estudiante, string $slug): int
    {
        $asignatura = Asignatura::where('slug', $slug)->first();

        if (!$asignatura) {
            return 0;
        }

        return ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->where('estado', 'completado')
            ->whereHas('tema', function ($query) use ($asignatura) {
                $query->where('asignatura_id', $asignatura->id);
            })
            ->count();
    }

    /**
     * Determinar si un intento fue perfecto.
     */
    private function esIntentoPerfecto(IntentosJuego $intento): bool
    {
        if (!$intento->relationLoaded('juego')) {
            $intento->load('juego.preguntasActivas');
        }

        return $intento->total_respuestas > 0 && $intento->respuestas_correctas === $intento->total_respuestas;
    }
}
