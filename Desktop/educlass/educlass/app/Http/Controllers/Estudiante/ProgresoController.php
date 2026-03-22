<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\ProgresionService;
use App\Services\GamificacionService;
use App\Services\CalificacionService;
use Illuminate\Support\Facades\Auth;
use App\Models\Asignatura;
use App\Models\User;

class ProgresoController extends Controller
{
    protected $progresionService;
    protected $gamificacionService;
    protected $calificacionService;

    public function __construct(
        ProgresionService $progresionService,
        GamificacionService $gamificacionService,
        CalificacionService $calificacionService
    ) {
        $this->progresionService = $progresionService;
        $this->gamificacionService = $gamificacionService;
        $this->calificacionService = $calificacionService;
    }

    /**
     * Mostrar progreso general
     */
    public function index()
    {
        $estudiante = Auth::user();

        // 🔹 Resumen general
        $resumen = $this->progresionService->getResumenProgreso($estudiante);

        // 🔹 Estadísticas de gamificación
        $estadisticas = $this->gamificacionService->getEstadisticas($estudiante);

        // 🔹 Calificaciones detalladas
        $calificaciones = $this->calificacionService->getCalificacionesDetalladas($estudiante->id);

        // 🔹 Progreso por asignatura
        $asignaturas = Asignatura::activas()->get();
        $progresoPorAsignatura = [];

        foreach ($asignaturas as $asignatura) {

            $progreso = $this->progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id);

            $juegosCompletados = $this->gamificacionService->getJuegosCompletados($estudiante, $asignatura->id);
            $evaluacionesAprobadas = $this->calificacionService->getEvaluacionesAprobadas($estudiante, $asignatura->id);
            $evaluacionesTotales = $this->calificacionService->getEvaluacionesTotales($estudiante, $asignatura->id);

            $progresoPorAsignatura[] = [
                'asignatura' => $asignatura,
                'progreso' => $progreso,
                'juegos_completados' => $juegosCompletados,
                'evaluaciones_aprobadas' => $evaluacionesAprobadas,
                'evaluaciones_totales' => $evaluacionesTotales,
            ];
        }

        // 🔹 Logros recientes
        $logrosRecientes = $estudiante->logrosEstudiante()
            ->with('logro')
            ->orderBy('fecha_obtenido', 'desc')
            ->get();

        // 🔹 Ranking
        $rankings = $estudiante->rankings()
            ->with('asignatura')
            ->orderBy('categoria')
            ->get();

        // 🔹 Retorno a la vista con todas las variables
        return view('estudiante.progreso.index', compact(
            'resumen',
            'estadisticas',
            'calificaciones',
            'progresoPorAsignatura',
            'logrosRecientes',
            'rankings'
        ));
    }
}
