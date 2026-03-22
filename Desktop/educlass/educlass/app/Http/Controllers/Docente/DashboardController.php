<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\User;
use App\Models\Tema;
use App\Models\Juego;
use App\Models\Evaluacion;
use App\Models\IntentosJuego;
use App\Models\ResultadosEvaluacion;
use App\Services\CalificacionService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $calificacionService;

    public function __construct(CalificacionService $calificacionService)
    {
        $this->calificacionService = $calificacionService;
    }

    /**
     * Mostrar dashboard del docente
     */
    public function index()
    {
        $docente = Auth::user();

        // Estadísticas generales
        $totalEstudiantes = User::estudiantes()->where('activo', true)->count();
        $totalTemas = Tema::where('docente_creador_id', $docente->id)->count();
        $totalJuegos = Juego::whereHas('tema', function ($q) use ($docente) {
            $q->where('docente_creador_id', $docente->id);
        })->count();
        $totalEvaluaciones = Evaluacion::whereHas('tema', function ($q) use ($docente) {
            $q->where('docente_creador_id', $docente->id);
        })->count();

        // Estadísticas de uso
        $intentosHoy = IntentosJuego::whereDate('fecha_intento', today())->count();
        $evaluacionesHoy = ResultadosEvaluacion::whereDate('fecha_realizacion', today())->count();

        // Asignaturas con estadísticas
        $asignaturas = Asignatura::activas()->get();
        $asignaturasStats = [];

        foreach ($asignaturas as $asignatura) {
            $resumen = $this->calificacionService->getResumenPorAsignatura($asignatura->id);
            $asignaturasStats[] = [
                'asignatura' => $asignatura,
                'resumen' => $resumen,
            ];
        }

        // Últimos estudiantes registrados
        $ultimosEstudiantes = User::estudiantes()
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Temas más completados
        $temasPopulares = Tema::activos()
            ->withCount(['progresoEstudiantes as completados_count' => function ($q) {
                $q->where('estado', 'completado');
            }])
            ->orderByDesc('completados_count')
            ->limit(5)
            ->get();

        return view('docente.dashboard', compact(
            'totalEstudiantes',
            'totalTemas',
            'totalJuegos',
            'totalEvaluaciones',
            'intentosHoy',
            'evaluacionesHoy',
            'asignaturasStats',
            'ultimosEstudiantes',
            'temasPopulares'
        ));
    }
}
