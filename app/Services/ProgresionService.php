<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tema;
use App\Models\ProgresoEstudiante;
use App\Models\Juego;
use App\Models\Evaluacion;
use App\Models\IntentosJuego;
use App\Models\ResultadosEvaluacion;

/**
 * Service ProgresionService - Gestiona la lógica de progresión de estudiantes
 */
class ProgresionService
{
    /**
     * Inicializar progreso para un nuevo estudiante
     */
    public function inicializarProgreso(User $estudiante): void
    {
        if (!$estudiante->esEstudiante()) {
            return;
        }

        // Obtener todos los temas activos
        $temas = Tema::activos()->get();

        foreach ($temas as $tema) {
            // El primer tema de cada asignatura queda disponible
            $esPrimero = Tema::where('asignatura_id', $tema->asignatura_id)
                ->where('activo', true)
                ->orderBy('orden')
                ->first()
                ?->id === $tema->id;

            ProgresoEstudiante::create([
                'estudiante_id' => $estudiante->id,
                'tema_id' => $tema->id,
                'estado' => $esPrimero ? 'disponible' : 'bloqueado',
                'porcentaje_lectura' => 0,
            ]);
        }
    }

    /**
     * Verificar si un estudiante puede acceder a un tema
     */
    public function puedeAccederTema(User $estudiante, Tema $tema): bool
    {
        if (!$estudiante->esEstudiante()) {
            return false;
        }

        // Si el tema se creó después de registrar al estudiante, puede no existir progreso.
        // Lo creamos bloqueado y evaluamos si debe quedar disponible.
        $progreso = ProgresoEstudiante::firstOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_id' => $tema->id,
            ],
            [
                'estado' => 'bloqueado',
                'porcentaje_lectura' => 0,
            ]
        );

        // Desbloquear automáticamente si ya cumple prerequisito (tema anterior completado).
        if ($progreso->esta_bloqueado && $this->completoTemaAnterior($estudiante, $tema)) {
            $progreso->marcarDisponible();
        }

        return in_array($progreso->estado, ['disponible', 'en_progreso', 'completado']);
    }

    /**
     * Verificar si estudiante completó tema anterior
     */
    public function completoTemaAnterior(User $estudiante, Tema $tema): bool
    {
        $temaAnterior = $tema->anterior();

        if (!$temaAnterior) {
            return true; // Es el primer tema
        }

        $progresoAnterior = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->where('tema_id', $temaAnterior->id)
            ->first();

        return $progresoAnterior && $progresoAnterior->esta_completado;
    }

    /**
     * Desbloquear siguiente tema
     */
    public function desbloquearSiguienteTema(User $estudiante, Tema $temaActual): ?Tema
    {
        $siguienteTema = $temaActual->siguiente();

        if (!$siguienteTema) {
            return null; // No hay siguiente tema
        }

        $progresoSiguiente = ProgresoEstudiante::firstOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_id' => $siguienteTema->id,
            ],
            [
                'estado' => 'bloqueado',
                'porcentaje_lectura' => 0,
            ]
        );

        if ($progresoSiguiente && $progresoSiguiente->esta_bloqueado) {
            $progresoSiguiente->marcarDisponible();
            return $siguienteTema;
        }

        return null;
    }

    /**
     * Registrar inicio de lectura de tema
     */
    public function iniciarLectura(User $estudiante, Tema $tema): ProgresoEstudiante
    {
        $progreso = ProgresoEstudiante::firstOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_id' => $tema->id,
            ],
            [
                'estado' => 'disponible',
                'porcentaje_lectura' => 0,
            ]
        );

        $progreso->marcarEnProgreso();

        return $progreso;
    }

    /**
     * Actualizar porcentaje de lectura
     */
    public function actualizarLectura(User $estudiante, Tema $tema, int $porcentaje): ProgresoEstudiante
    {
        $progreso = ProgresoEstudiante::firstOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'tema_id' => $tema->id,
            ],
            [
                'estado' => 'en_progreso',
                'porcentaje_lectura' => 0,
            ]
        );

        $progreso->actualizarLectura($porcentaje);

        return $progreso;
    }

    /**
     * Verificar si puede realizar juego
     */
    public function puedeRealizarJuego(User $estudiante, Juego $juego): array
    {
        $tema = $juego->tema;
        $progreso = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->where('tema_id', $tema->id)
            ->first();

        if (!$progreso) {
            return [
                'puede' => false,
                'razon' => 'No tienes acceso a este tema',
            ];
        }

        if ($progreso->esta_bloqueado) {
            return [
                'puede' => false,
                'razon' => 'Este tema está bloqueado. Completa el tema anterior primero.',
            ];
        }

        if (!$progreso->cumple_lectura_minima) {
            return [
                'puede' => false,
                'razon' => 'Debes leer al menos el 80% del tema antes de realizar el juego.',
            ];
        }

        if (!$juego->tieneIntentosDisponibles($estudiante->id)) {
            return [
                'puede' => false,
                'razon' => 'Has agotado tus intentos para este juego.',
            ];
        }

        return [
            'puede' => true,
            'razon' => null,
        ];
    }

    /**
     * Verificar si puede realizar evaluación
     */
    public function puedeRealizarEvaluacion(User $estudiante, Evaluacion $evaluacion): array
    {
        $tema = $evaluacion->tema;
        $progreso = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->where('tema_id', $tema->id)
            ->first();

        if (!$progreso) {
            return [
                'puede' => false,
                'razon' => 'No tienes acceso a este tema',
            ];
        }

        if ($progreso->esta_bloqueado) {
            return [
                'puede' => false,
                'razon' => 'Este tema está bloqueado. Completa el tema anterior primero.',
            ];
        }

        if (!$progreso->cumple_lectura_minima) {
            return [
                'puede' => false,
                'razon' => 'Debes leer al menos el 80% del tema antes de realizar la evaluación.',
            ];
        }

        // Verificar si completó el juego del tema
        $juego = $tema->juego_principal;
        if ($juego) {
            $completoJuego = \App\Models\IntentosJuego::where('estudiante_id', $estudiante->id)
                ->where('juego_id', $juego->id)
                ->where('completado', true)
                ->exists();

            if (!$completoJuego) {
                return [
                    'puede' => false,
                    'razon' => 'Debes completar el juego del tema antes de realizar la evaluación.',
                ];
            }
        }

        if (!$evaluacion->tieneIntentosDisponibles($estudiante->id)) {
            return [
                'puede' => false,
                'razon' => 'Has agotado tus intentos para esta evaluación.',
            ];
        }

        return [
            'puede' => true,
            'razon' => null,
        ];
    }

    /**
     * Completar tema
     */
    public function completarTema(User $estudiante, Tema $tema): array
    {
        $progreso = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->where('tema_id', $tema->id)
            ->first();

        if (!$progreso) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontró progreso para este tema',
            ];
        }

        $progreso->marcarCompletado();

        // Desbloquear siguiente tema
        $siguienteTema = $this->desbloquearSiguienteTema($estudiante, $tema);
        $gamificacionService = app(\App\Services\GamificacionService::class);
        $gamificacionService->actualizarRankingTemas($estudiante, $tema->asignatura_id);
        $gamificacionService->actualizarRankingGeneral($estudiante);
        $gamificacionService->verificarLogros($estudiante, 'tema_completado', [
            'tema' => $tema,
            'progreso' => $progreso,
        ]);

        return [
            'exito' => true,
            'mensaje' => '¡Tema completado exitosamente!',
            'siguiente_tema' => $siguienteTema,
            'siguiente_tema_nombre' => $siguienteTema?->titulo,
        ];
    }

    /**
     * Obtener progreso por asignatura
     */
    public function getProgresoPorAsignatura(User $estudiante, int $asignaturaId): array
    {
        $temas = Tema::where('asignatura_id', $asignaturaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $totalTemas = $temas->count();
        $temasCompletados = 0;
        $temasEnProgreso = 0;

        foreach ($temas as $tema) {
            $progreso = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
                ->where('tema_id', $tema->id)
                ->first();

            if ($progreso) {
                if ($progreso->esta_completado) {
                    $temasCompletados++;
                } elseif ($progreso->esta_en_progreso) {
                    $temasEnProgreso++;
                }
            }
        }

        $porcentaje = $totalTemas > 0 ? round(($temasCompletados / $totalTemas) * 100, 2) : 0;
        $puntajeJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->whereHas('juego.tema', function ($query) use ($asignaturaId) {
                $query->where('asignatura_id', $asignaturaId);
            })
            ->sum('puntaje_obtenido');

        $puntajeEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->whereHas('evaluacion.tema', function ($query) use ($asignaturaId) {
                $query->where('asignatura_id', $asignaturaId);
            })
            ->sum('puntaje_obtenido');

        $puntosAcumulados = $puntajeJuegos + $puntajeEvaluaciones + ($temasCompletados * 100);

        return [
            'total_temas' => $totalTemas,
            'temas_completados' => $temasCompletados,
            'temas_en_progreso' => $temasEnProgreso,
            'porcentaje' => $porcentaje,
            'porcentaje_completado' => $porcentaje,
            'nivel_actual' => $this->calcularNivelActual($porcentaje),
            'puntos_acumulados' => $puntosAcumulados,
        ];
    }

    /**
     * Calcular nivel actual basado en porcentaje
     */
    public function calcularNivelActual(float $porcentaje): int
    {
        if ($porcentaje >= 75) return 4;
        if ($porcentaje >= 50) return 3;
        if ($porcentaje >= 25) return 2;
        return 1;
    }

    /**
 * Obtener resumen de progreso general
 */
public function getResumenProgreso(User $estudiante): array
{
    $totalAsignaturas = \App\Models\Asignatura::where('activa', true)->count();

    $totalTemas = Tema::activos()->count();

    $temasCompletados = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
        ->completados()
        ->count();

    $temasEnProgreso = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
        ->where('estado', 'en_progreso')
        ->count();

    $porcentajeGeneral = $totalTemas > 0
        ? round(($temasCompletados / $totalTemas) * 100, 2)
        : 0;

    $puntajeJuegos = IntentosJuego::where('estudiante_id', $estudiante->id)
        ->where('completado', true)
        ->sum('puntaje_obtenido');

    $puntajeEvaluaciones = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
        ->where('aprobado', true)
        ->sum('puntaje_obtenido');

    $puntosTotales = $puntajeJuegos + $puntajeEvaluaciones + ($temasCompletados * 100);

    return [

        // 🔹 Para progreso/index.blade.php
        'asignaturas' => $totalAsignaturas,
        'completadas' => $temasCompletados,
        'en_progreso' => $temasEnProgreso,

        // 🔹 Para dashboard.blade.php
        'total_temas' => $totalTemas,
        'temas_completados' => $temasCompletados,

        // 🔹 Común
        'porcentaje_general' => $porcentajeGeneral,
        'nivel_global' => $this->calcularNivelActual($porcentajeGeneral),
        'puntos_totales' => $puntosTotales,
        'nivel_maximo' => $this->calcularNivelActual($porcentajeGeneral),
    ];
}



    /**
     * Reiniciar progreso de estudiante (para testing)
     */
    public function reiniciarProgreso(User $estudiante): void
    {
        ProgresoEstudiante::where('estudiante_id', $estudiante->id)->delete();
        \App\Models\IntentosJuego::where('estudiante_id', $estudiante->id)->delete();
        \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)->delete();
        
        $this->inicializarProgreso($estudiante);
    }
}
