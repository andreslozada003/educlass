<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\ProgresoEstudiante;
use App\Services\ProgresionService;
use App\Services\GamificacionService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $progresionService;
    protected $gamificacionService;

    public function __construct(
        ProgresionService $progresionService,
        GamificacionService $gamificacionService
    ) {
        $this->progresionService = $progresionService;
        $this->gamificacionService = $gamificacionService;
    }

    /**
     * Mostrar dashboard del estudiante
     */
    public function index()
    {
        $estudiante = Auth::user();

        // Estadísticas generales
        $resumenProgreso = $this->progresionService->getResumenProgreso($estudiante);
        $estadisticas = $this->gamificacionService->getEstadisticas($estudiante);

        // Progreso por asignatura
        $asignaturas = Asignatura::activas()->ordenado()->get();
        $progresoAsignaturas = [];

        foreach ($asignaturas as $asignatura) {
            $progresoAsignaturas[] = [
                'asignatura' => $asignatura,
                'progreso' => $this->progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id),
            ];
        }

        // Temas recientes
        $temasRecientes = ProgresoEstudiante::where('estudiante_id', $estudiante->id)
            ->with('tema.asignatura')
            ->whereIn('estado', ['en_progreso', 'completado'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Logros recientes
        $logrosRecientes = $estudiante->logrosEstudiante()
            ->with('logro')
            ->orderBy('fecha_obtenido', 'desc')
            ->limit(4)
            ->get();

        // Ranking
        $posicionRanking = $estudiante->rankings()
            ->where('categoria', 'general')
            ->whereNull('asignatura_id')
            ->first();

        return view('estudiante.dashboard', compact(
            'resumenProgreso',
            'estadisticas',
            'progresoAsignaturas',
            'temasRecientes',
            'logrosRecientes',
            'posicionRanking'
        ));
    }
}
