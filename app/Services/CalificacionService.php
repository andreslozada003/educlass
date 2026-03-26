<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asignatura;
use App\Models\IntentosJuego;
use App\Models\ResultadosEvaluacion;
use App\Models\CalificacionesPeriodo;
use App\Models\ConfiguracionSistema;
use App\Models\Evaluacion;

/**
 * Service CalificacionService - Gestiona cálculo de calificaciones y promedios
 */
class CalificacionService
{
    /**
     * Calcular promedio de juegos por asignatura y período
     */
    public function calcularPromedioJuegos(int $estudianteId, int $asignaturaId, int $periodo): float
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();

        $promedio = IntentosJuego::where('intentos_juegos.estudiante_id', $estudianteId)
            ->where('intentos_juegos.completado', true)
            ->join('juegos', 'intentos_juegos.juego_id', '=', 'juegos.id')
            ->join('temas', 'juegos.tema_id', '=', 'temas.id')
            ->where('temas.asignatura_id', $asignaturaId)
            ->where('temas.periodo_academico', $periodo)
            ->whereYear('intentos_juegos.fecha_intento', $anioAcademico)
            ->avg('intentos_juegos.puntaje_obtenido');

        return round($promedio ?? 0, 2);
    }

    /**
     * Calcular promedio de evaluaciones por asignatura y período
     */
    public function calcularPromedioEvaluaciones(int $estudianteId, int $asignaturaId, int $periodo): float
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();

        $promedio = ResultadosEvaluacion::where('resultados_evaluaciones.estudiante_id', $estudianteId)
            ->join('evaluaciones', 'resultados_evaluaciones.evaluacion_id', '=', 'evaluaciones.id')
            ->join('temas', 'evaluaciones.tema_id', '=', 'temas.id')
            ->where('temas.asignatura_id', $asignaturaId)
            ->where('temas.periodo_academico', $periodo)
            ->whereYear('resultados_evaluaciones.fecha_realizacion', $anioAcademico)
            ->avg('resultados_evaluaciones.puntaje_obtenido');

        return round($promedio ?? 0, 2);
    }

    /**
     * Calcular y guardar calificación del período
     */
    public function calcularCalificacionPeriodo(int $estudianteId, int $asignaturaId, int $periodo): CalificacionesPeriodo
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();

        $promedioJuegos = $this->calcularPromedioJuegos($estudianteId, $asignaturaId, $periodo);
        $promedioEvaluaciones = $this->calcularPromedioEvaluaciones($estudianteId, $asignaturaId, $periodo);

        // Fórmula: Juegos 30% + Evaluaciones 70%
        $promedioPonderado = ($promedioJuegos * 0.30) + ($promedioEvaluaciones * 0.70);

        $calificacion = CalificacionesPeriodo::updateOrCreate(
            [
                'estudiante_id' => $estudianteId,
                'asignatura_id' => $asignaturaId,
                'periodo' => $periodo,
                'año_academico' => $anioAcademico,
            ],
            [
                'promedio_juegos' => $promedioJuegos,
                'promedio_evaluaciones' => $promedioEvaluaciones,
                'promedio_ponderado' => round($promedioPonderado, 2),
            ]
        );

        return $calificacion;
    }

    /**
     * Calcular promedio anual por asignatura
     */
    public function calcularPromedioAnual(int $estudianteId, int $asignaturaId): float
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();

        $promedio = CalificacionesPeriodo::where('estudiante_id', $estudianteId)
            ->where('asignatura_id', $asignaturaId)
            ->where('año_academico', $anioAcademico)
            ->avg('promedio_ponderado');

        return round($promedio ?? 0, 2);
    }

    /**
     * Obtener calificaciones detalladas de un estudiante
     */
    public function getCalificacionesDetalladas(int $estudianteId): array
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();
        $asignaturas = Asignatura::activas()->get();
        $resultado = [];

        foreach ($asignaturas as $asignatura) {
            $calificacionesPeriodo = CalificacionesPeriodo::where('estudiante_id', $estudianteId)
                ->where('asignatura_id', $asignatura->id)
                ->where('año_academico', $anioAcademico)
                ->orderBy('periodo')
                ->get();

            $promedioAnual = $this->calcularPromedioAnual($estudianteId, $asignatura->id);

            $resultado[] = [
                'asignatura' => $asignatura,
                'calificaciones_periodo' => $calificacionesPeriodo,
                'promedio_anual' => $promedioAnual,
                'aprobo_anual' => $promedioAnual >= 3.0,
            ];
        }

        return $resultado;
    }

    /**
     * Obtener resumen de calificaciones para docente
     */
    public function getResumenPorAsignatura(int $asignaturaId, ?int $periodo = null, ?int $anio = null): array
    {
        $anioAcademico = $anio ?? ConfiguracionSistema::getAnioAcademico();
        $query = CalificacionesPeriodo::where('asignatura_id', $asignaturaId)
            ->where('año_academico', $anioAcademico);

        if ($periodo) {
            $query->where('periodo', $periodo);
        }

        $calificaciones = $query->get();

        $totalEstudiantes = $calificaciones->count();

        if ($totalEstudiantes === 0) {
            return [
                'total_estudiantes' => 0,
                'promedio_general' => 0,
                'aprobados' => 0,
                'reprobados' => 0,
                'tasa_aprobacion' => 0,
                'promedio_maximo' => 0,
                'promedio_minimo' => 0,
            ];
        }

        $aprobados = $calificaciones->where('aprobo', true)->count();

        return [
            'total_estudiantes' => $totalEstudiantes,
            'promedio_general' => round($calificaciones->avg('promedio_ponderado'), 2),
            'aprobados' => $aprobados,
            'reprobados' => $totalEstudiantes - $aprobados,
            'tasa_aprobacion' => round(($aprobados / $totalEstudiantes) * 100, 2),
            'promedio_maximo' => round($calificaciones->max('promedio_ponderado'), 2),
            'promedio_minimo' => round($calificaciones->min('promedio_ponderado'), 2),
        ];
    }

    /**
     * Convertir puntaje a escala 0-5
     */
    public function puntajeAEscala5(float $puntaje, float $puntajeMaximo = 100): float
    {
        if ($puntajeMaximo <= 0) {
            return 0;
        }

        $porcentaje = ($puntaje / $puntajeMaximo) * 100;

        return match (true) {
            $porcentaje >= 90 => 5.0,
            $porcentaje >= 80 => 4.5,
            $porcentaje >= 70 => 4.0,
            $porcentaje >= 60 => 3.5,
            $porcentaje >= 50 => 3.0,
            $porcentaje >= 40 => 2.5,
            $porcentaje >= 30 => 2.0,
            $porcentaje >= 20 => 1.5,
            default => 1.0,
        };
    }

    /**
     * Convertir escala 0-5 a descripción
     */
    public function escala5ADescripcion(float $nota): string
    {
        return match (true) {
            $nota >= 4.5 => 'Sobresaliente',
            $nota >= 4.0 => 'Notable',
            $nota >= 3.5 => 'Bueno',
            $nota >= 3.0 => 'Suficiente',
            $nota >= 2.0 => 'Regular',
            default => 'Insuficiente',
        };
    }

    /**
     * Obtener color según nota
     */
    public function getColorNota(float $nota): string
    {
        return match (true) {
            $nota >= 4.5 => '#10B981', // Verde
            $nota >= 4.0 => '#3B82F6', // Azul
            $nota >= 3.5 => '#F59E0B', // Amarillo
            $nota >= 3.0 => '#F97316', // Naranja
            default => '#EF4444', // Rojo
        };
    }

    /**
     * Generar boletín de calificaciones
     */
    public function generarBoletin(int $estudianteId): array
    {
        $estudiante = User::find($estudianteId);
        $calificaciones = $this->getCalificacionesDetalladas($estudianteId);
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();

        $promedioGeneral = 0;
        $totalAsignaturas = count($calificaciones);

        foreach ($calificaciones as $cal) {
            $promedioGeneral += $cal['promedio_anual'];
        }

        $promedioGeneral = $totalAsignaturas > 0 ? round($promedioGeneral / $totalAsignaturas, 2) : 0;

        return [
            'estudiante' => $estudiante,
            'anio_academico' => $anioAcademico,
            'calificaciones' => $calificaciones,
            'promedio_general' => $promedioGeneral,
            'desempeno_general' => $this->escala5ADescripcion($promedioGeneral),
            'aprobo_general' => $promedioGeneral >= 3.0,
            'fecha_generacion' => now(),
        ];
    }

    /**
     * Recalcular todas las calificaciones de un estudiante
     */
    public function recalcularCalificaciones(int $estudianteId): void
    {
        $asignaturas = Asignatura::activas()->get();
        $periodos = [1, 2, 3];

        foreach ($asignaturas as $asignatura) {
            foreach ($periodos as $periodo) {
                $this->calcularCalificacionPeriodo($estudianteId, $asignatura->id, $periodo);
            }
        }
    }

    /**
     * Obtener mejores estudiantes por asignatura
     */
    public function getMejoresEstudiantes(int $asignaturaId, int $limite = 10, ?int $periodo = null): array
    {
        $anioAcademico = ConfiguracionSistema::getAnioAcademico();
        $query = CalificacionesPeriodo::where('asignatura_id', $asignaturaId)
            ->where('año_academico', $anioAcademico)
            ->with('estudiante');

        if ($periodo) {
            $query->where('periodo', $periodo);
        } else {
            // Si no se especifica período, usar el promedio de todos los períodos
            $query->selectRaw('estudiante_id, AVG(promedio_ponderado) as promedio_general')
                ->groupBy('estudiante_id')
                ->orderByDesc('promedio_general');
        }

        return $query->limit($limite)->get()->toArray();
    }
     /**
     * Cantidad de evaluaciones aprobadas por estudiante en una asignatura.
     */
      

public function getEvaluacionesAprobadas($estudiante, $asignaturaId)
{
    return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
        ->where('aprobado', true)
        ->whereHas('evaluacion.tema', function ($q) use ($asignaturaId) {
            $q->where('asignatura_id', $asignaturaId);
        })
        ->count();

    return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
        ->where('aprobado', true)
        ->whereHas('evaluacion.tema', function ($q) use ($asignaturaId) {
            $q->where('tema_id', $asignaturaId); // ajusta según tu relación con asignatura
        })
        ->get()
        ->filter(function ($resultado) {
            // Compara el puntaje obtenido con el umbral de aprobación
            return $resultado->puntaje_obtenido >= $resultado->evaluacion->umbral_aprobacion;
        })
        ->count();
}

public function getEvaluacionesTotales($estudiante, $asignaturaId)
{
    return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
        ->whereHas('evaluacion.tema', function ($q) use ($asignaturaId) {
            $q->where('asignatura_id', $asignaturaId);
        })
        ->count();

    return ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
        ->whereHas('evaluacion', function ($q) use ($asignaturaId) {
            $q->where('asignatura_id', $asignaturaId);
        })
        ->count();
}




}
