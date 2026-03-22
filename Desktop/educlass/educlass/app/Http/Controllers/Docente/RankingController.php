<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\IntentosJuego;
use App\Models\Ranking;
use App\Models\ResultadosEvaluacion;
use App\Models\User;
use App\Services\ExportService;
use App\Services\GamificacionService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    protected $exportService;
    protected $gamificacionService;

    public function __construct(ExportService $exportService, GamificacionService $gamificacionService)
    {
        $this->exportService = $exportService;
        $this->gamificacionService = $gamificacionService;
    }

    /**
     * Mostrar rankings
     */
    public function index(Request $request)
    {
        $categoria = $request->categoria ?? 'general';
        $asignaturaId = $request->asignatura_id ?? $request->asignatura;
        $nivel = $request->nivel;

        $query = Ranking::with(['estudiante.colegio', 'asignatura'])
            ->where('categoria', $categoria)
            ->where('puntaje_total', '>', 0);

        if ($categoria === 'general') {
            $query->whereNull('asignatura_id');
        } elseif ($asignaturaId) {
            $query->where('asignatura_id', $asignaturaId);
        }

        if ($nivel) {
            $query->where('nivel_alcanzado', $nivel);
        }

        // Si no hay datos, recalcular ranking para poblar la tabla.
        if (!(clone $query)->exists()) {
            $this->recalcularRankings();
        }

        $rankings = $query->orderBy('posicion')->paginate(10);
        $rankings->getCollection()->transform(function ($ranking) use ($asignaturaId) {
            $targetAsignaturaId = $ranking->asignatura_id ?: $asignaturaId;

            $juegosQuery = IntentosJuego::where('estudiante_id', $ranking->estudiante_id)
                ->where('completado', true);
            $evaluacionesQuery = ResultadosEvaluacion::where('estudiante_id', $ranking->estudiante_id)
                ->where('aprobado', true);

            if ($targetAsignaturaId) {
                $juegosQuery->whereHas('juego.tema', function ($q) use ($targetAsignaturaId) {
                    $q->where('asignatura_id', $targetAsignaturaId);
                });
                $evaluacionesQuery->whereHas('evaluacion.tema', function ($q) use ($targetAsignaturaId) {
                    $q->where('asignatura_id', $targetAsignaturaId);
                });
            }

            $ranking->juegos_completados = $juegosQuery->count();
            $ranking->evaluaciones_completadas = $evaluacionesQuery->count();

            return $ranking;
        });
        $topEstudiantes = $rankings->take(3)->values();


        $asignaturas = Asignatura::activas()->get();
        $categorias = [
            'general' => 'General',
            'juegos' => 'Juegos',
            'evaluaciones' => 'Evaluaciones',
            'temas' => 'Temas Completados',
        ];

        return view('docente.rankings.index', compact(
            'rankings',
            'topEstudiantes',
            'asignaturas',
            'categorias',
            'categoria',
            'asignaturaId'
        ));

    }
 
    /**
     * Actualizar rankings
     */
    public function actualizar()
    {
        $this->recalcularRankings();

        return redirect()->back()->with('success', 'Rankings actualizados exitosamente.');
    }

    private function recalcularRankings(): void
    {
        $estudiantes = User::estudiantes()->where('activo', true)->get();
        $asignaturas = Asignatura::activas()->get();

        foreach ($estudiantes as $estudiante) {
            $this->gamificacionService->actualizarRankingGeneral($estudiante);

            foreach ($asignaturas as $asignatura) {
                $this->gamificacionService->actualizarRankingJuegos($estudiante, $asignatura->id);
                $this->gamificacionService->actualizarRankingEvaluaciones($estudiante, $asignatura->id);
                $this->gamificacionService->actualizarRankingTemas($estudiante, $asignatura->id);
            }
        }
    }

    /**
     * Exportar ranking a Excel
     */
    public function exportar(Request $request)
    {
        $request->validate([
            'categoria' => 'required|in:juegos,evaluaciones,temas,general',
        ]);

        return $this->exportService->exportarRankingExcel(
            $request->categoria,
            $request->asignatura,
            50
        );
    }
}
