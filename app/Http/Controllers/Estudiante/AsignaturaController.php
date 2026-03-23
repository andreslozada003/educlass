<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\ProgresoEstudiante;
use App\Services\ProgresionService;
use Illuminate\Support\Facades\Auth;

class AsignaturaController extends Controller
{
    protected $progresionService;

    public function __construct(ProgresionService $progresionService)
    {
        $this->progresionService = $progresionService;
    }

    /**
     * Listar asignaturas disponibles
     */
    public function index()
    {
        $estudiante = Auth::user();
        $asignaturas = Asignatura::activas()
            ->ordenado()
            ->with('temasActivos')
            ->get();

        $progresos = [];
        foreach ($asignaturas as $asignatura) {
            $progreso = $this->progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id);
            $progreso['porcentaje_completado'] = $progreso['porcentaje'] ?? 0;
            $progreso['puntos_acumulados'] = $progreso['puntos_acumulados'] ?? 0;
            $progresos[$asignatura->id] = (object) $progreso;
        }

        return view('estudiante.asignaturas.index', compact('asignaturas', 'progresos'));
    }

    /**
     * Ver detalle de asignatura
     */
    public function show($slug)
    {
        $estudiante = Auth::user();
        $asignaturaQuery = Asignatura::query();
        if (is_numeric($slug)) {
            $asignaturaQuery->where('id', (int) $slug);
        } else {
            $asignaturaQuery->where('slug', $slug);
        }
        $asignatura = $asignaturaQuery->firstOrFail();

        // Sincronizar progreso de temas activos de esta asignatura para evitar bloqueos falsos.
        $temasActivos = $asignatura->temasActivos()->orderBy('orden')->get();
        foreach ($temasActivos as $temaItem) {
            $progresoTema = ProgresoEstudiante::firstOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'tema_id' => $temaItem->id,
                ],
                [
                    'estado' => 'bloqueado',
                    'porcentaje_lectura' => 0,
                ]
            );

            if ($progresoTema->estado === 'bloqueado' && $this->progresionService->completoTemaAnterior($estudiante, $temaItem)) {
                $progresoTema->marcarDisponible();
            }
        }

        $progresoData = $this->progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id);
        $progresoData['porcentaje_completado'] = $progresoData['porcentaje'] ?? 0;
        $progresoData['puntos_acumulados'] = $progresoData['puntos_acumulados'] ?? 0;
        $progreso = (object) $progresoData;

        // Temas de la asignatura con progreso del estudiante
        $temas = $asignatura->temasActivos()
            ->with(['progresoEstudiantes' => function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            }])
            ->withCount(['juegosActivos as juegos_activos_count', 'evaluacionesActivas as evaluaciones_activas_count'])
            ->orderBy('dificultad')
            ->orderBy('orden')
            ->get();

        return view('estudiante.asignaturas.show', compact(
            'asignatura',
            'progreso',
            'temas'
        ));
    }
}
