<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Logro;
use App\Services\CalificacionService;
use App\Services\GamificacionService;
use App\Services\ProgresionService;
use Illuminate\Support\Facades\Auth;

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

        $resumen = $this->progresionService->getResumenProgreso($estudiante);
        $estadisticas = $this->gamificacionService->getEstadisticas($estudiante);
        $calificaciones = $this->calificacionService->getCalificacionesDetalladas($estudiante->id);

        $resumen['puntos_totales'] = $estadisticas['puntos_totales'] ?? ($resumen['puntos_totales'] ?? 0);
        $resumen['nivel_maximo'] = $resumen['nivel_maximo'] ?? ($resumen['nivel_global'] ?? 1);

        $asignaturas = Asignatura::activas()
            ->ordenado()
            ->get();

        $progresoPorAsignatura = [];

        foreach ($asignaturas as $asignatura) {
            $progresoData = $this->progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id);
            $progresoData['porcentaje_completado'] = $progresoData['porcentaje_completado'] ?? ($progresoData['porcentaje'] ?? 0);
            $progresoData['puntos_acumulados'] = $progresoData['puntos_acumulados'] ?? 0;

            $progresoPorAsignatura[] = [
                'asignatura' => $asignatura,
                'progreso' => (object) $progresoData,
                'juegos_completados' => $this->gamificacionService->getJuegosCompletados($estudiante, $asignatura->id),
                'evaluaciones_aprobadas' => $this->calificacionService->getEvaluacionesAprobadas($estudiante, $asignatura->id),
                'evaluaciones_totales' => $this->calificacionService->getEvaluacionesTotales($estudiante, $asignatura->id),
            ];
        }

        $logrosRecientes = $estudiante->logrosEstudiante()
            ->with('logro')
            ->orderBy('fecha_obtenido', 'desc')
            ->limit(8)
            ->get();

        $rankings = $estudiante->rankings()
            ->with('asignatura')
            ->orderBy('puntaje_total', 'desc')
            ->get();

        $logrosDisponibles = Logro::activos()
            ->orderBy('puntos_bonus')
            ->get();

        $logrosDesbloqueados = $estudiante->logrosEstudiante()
            ->pluck('logro_id')
            ->all();

        return view('estudiante.progreso.index', compact(
            'resumen',
            'estadisticas',
            'calificaciones',
            'progresoPorAsignatura',
            'logrosRecientes',
            'rankings',
            'logrosDisponibles',
            'logrosDesbloqueados'
        ));
    }
}
