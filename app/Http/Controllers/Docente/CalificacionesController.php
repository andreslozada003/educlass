<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\CalificacionesPeriodo;
use App\Models\User;
use App\Services\CalificacionService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CalificacionesController extends Controller
{
    protected $calificacionService;
    protected $exportService;

    public function __construct(
        CalificacionService $calificacionService,
        ExportService $exportService
    ) {
        $this->calificacionService = $calificacionService;
        $this->exportService = $exportService;
    }

    /**
     * Listar calificaciones por asignatura
     */
    public function index(Request $request)
    {
        $asignaturas = Asignatura::activas()->get();

        $anios = CalificacionesPeriodo::query()
            ->select('año_academico as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        $asignaturaSeleccionada = null;
        $resumen = [
            'total_estudiantes' => 0,
            'promedio_general' => 0,
            'aprobados' => 0,
            'reprobados' => 0,
            'tasa_aprobacion' => 0,
            'promedio_maximo' => 0,
            'promedio_minimo' => 0,
        ];

        $calificaciones = new LengthAwarePaginator(
            collect(),
            0,
            10,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        if ($request->filled('asignatura_id')) {
            $asignaturaSeleccionada = Asignatura::findOrFail($request->asignatura_id);
            $periodo = $request->filled('periodo') ? (int) $request->periodo : null;
            $anio = $request->filled('anio') ? (int) $request->anio : null;

            $resumen = $this->calificacionService->getResumenPorAsignatura(
                $asignaturaSeleccionada->id,
                $periodo,
                $anio
            );

            $query = CalificacionesPeriodo::query()
                ->with(['estudiante', 'asignatura'])
                ->where('asignatura_id', $asignaturaSeleccionada->id)
                ->whereHas('estudiante', function ($estudianteQuery) use ($request) {
                    $estudianteQuery
                        ->where('tipo', 'estudiante')
                        ->where('activo', true);

                    if ($request->filled('colegio')) {
                        $estudianteQuery->where('colegio_id', $request->colegio);
                    }
                });

            if ($periodo !== null) {
                $query->where('periodo', $periodo);
            }

            if ($anio !== null) {
                $query->porAnio($anio);
            }

            $calificaciones = $query
                ->join('users', 'calificaciones_periodo.estudiante_id', '=', 'users.id')
                ->orderBy('users.nombre')
                ->orderBy('calificaciones_periodo.periodo')
                ->select('calificaciones_periodo.*')
                ->paginate(10)
                ->withQueryString();
        }

        return view('docente.calificaciones.index', [
            'asignaturas' => $asignaturas,
            'asignaturaSeleccionada' => $asignaturaSeleccionada,
            'calificaciones' => $calificaciones,
            'resumen' => $resumen,
            'anios' => $anios,
        ]);
    }

    /**
     * Ver detalle de calificaciones de un estudiante
     */
    public function showEstudiante($estudianteId)
    {
        $estudiante = User::estudiantes()
            ->with('colegio')
            ->findOrFail($estudianteId);

        $calificaciones = $this->calificacionService
            ->getCalificacionesDetalladas($estudianteId);

        $promedioGeneral = 0;
        $totalAsignaturas = count($calificaciones);

        foreach ($calificaciones as $cal) {
            $promedioGeneral += $cal['promedio_anual'];
        }

        $promedioGeneral = $totalAsignaturas > 0
            ? round($promedioGeneral / $totalAsignaturas, 2)
            : 0;

        return view('docente.calificaciones.estudiante', compact(
            'estudiante',
            'calificaciones',
            'promedioGeneral'
        ));
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'asignatura_id' => 'required|exists:asignaturas,id',
        ]);

        return $this->exportService->exportarCalificacionesExcel(
            (int) $request->asignatura_id,
            $request->filled('periodo') ? (int) $request->periodo : null,
            $request->filled('colegio') ? (int) $request->colegio : null
        );
    }

    public function generarBoletin($estudianteId)
    {
        return $this->exportService->generarBoletinPDF($estudianteId);
    }

    public function recalcular(Request $request)
    {
        $request->validate([
            'asignatura_id' => 'required|exists:asignaturas,id',
        ]);

        $asignatura = Asignatura::findOrFail($request->asignatura_id);

        $estudiantes = User::estudiantes()
            ->where('activo', true)
            ->get();

        foreach ($estudiantes as $estudiante) {
            foreach ([1, 2, 3, 4] as $periodo) {
                $this->calificacionService->calcularCalificacionPeriodo(
                    $estudiante->id,
                    $asignatura->id,
                    $periodo
                );
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Calificaciones recalculadas exitosamente.');
    }
}
