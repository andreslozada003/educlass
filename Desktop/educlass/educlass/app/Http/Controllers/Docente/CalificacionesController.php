<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\User;
use App\Models\CalificacionesPeriodo;
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

        $años = CalificacionesPeriodo::selectRaw('YEAR(created_at) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        $asignaturaSeleccionada = null;
        $resumen = null;

        // ✅ Paginator vacío inicial
        $calificaciones = new LengthAwarePaginator([], 0, 10);

        if ($request->filled('asignatura_id')) {

            $asignaturaSeleccionada = Asignatura::findOrFail($request->asignatura_id);
            $periodo = $request->periodo;
            $anio = $request->anio;

            $resumen = $this->calificacionService->getResumenPorAsignatura(
                $asignaturaSeleccionada->id,
                $periodo
            );

            $query = User::estudiantes()
                ->where('activo', true)
                ->with(['calificacionesPeriodo' => function ($q) use ($asignaturaSeleccionada, $periodo, $anio) {

                    $q->where('asignatura_id', $asignaturaSeleccionada->id);

                    if ($periodo) {
                        $q->where('periodo', $periodo);
                    }

                    if ($anio) {
                        $q->whereYear('created_at', $anio);
                    }
                }]);

            if ($request->filled('colegio')) {
                $query->where('colegio_id', $request->colegio);
            }

            // ✅ Aquí sí usamos paginate correctamente
            $estudiantes = $query->orderBy('nombre')->paginate(10);

            // Transformar SIN perder paginación
            $estudiantes->getCollection()->transform(function ($estudiante) use ($asignaturaSeleccionada) {
                return [
                    'estudiante' => $estudiante,
                    'calificaciones' => $estudiante->calificacionesPeriodo,
                    'promedio_anual' => app(CalificacionService::class)
                        ->calcularPromedioAnual(
                            $estudiante->id,
                            $asignaturaSeleccionada->id
                        ),
                ];
            });

            $calificaciones = $estudiantes;
        }

        return view('docente.calificaciones.index', [
            'asignaturas' => $asignaturas,
            'asignaturaSeleccionada' => $asignaturaSeleccionada,
            'calificaciones' => $calificaciones,
            'resumen' => $resumen,
            'años' => $años
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
            $request->asignatura_id,
            $request->periodo,
            $request->colegio
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
