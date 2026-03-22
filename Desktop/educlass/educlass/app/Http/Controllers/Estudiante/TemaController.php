<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Tema;
use App\Services\ProgresionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemaController extends Controller
{
    protected $progresionService;

    public function __construct(ProgresionService $progresionService)
    {
        $this->progresionService = $progresionService;
    }

    /**
     * Ver contenido de un tema
     */
    public function show($slug)
    {
        $estudiante = Auth::user();
        $tema = Tema::where('slug', $slug)
            ->with([
                'asignatura',
                'juegos' => function ($query) {
                    $query->where('activo', true);
                },
                'evaluaciones' => function ($query) {
                    $query->where('activa', true)->with('preguntas');
                },
            ])
            ->firstOrFail();

        // Verificar si puede acceder al tema
        if (!$this->progresionService->puedeAccederTema($estudiante, $tema)) {
            return redirect()->route('estudiante.asignaturas.show', $tema->asignatura->slug)
                ->with('error', 'Este tema esta bloqueado. Completa los temas anteriores primero.');
        }

        // Registrar inicio de lectura
        $progreso = $this->progresionService->iniciarLectura($estudiante, $tema);

        // Obtener tema anterior y siguiente
        $temaAnterior = $tema->anterior();
        $temaSiguiente = $tema->siguiente();

        $juegosCompletados = collect();
        if ($tema->juegos->isNotEmpty()) {
            $juegosCompletados = \App\Models\IntentosJuego::where('estudiante_id', $estudiante->id)
                ->whereIn('juego_id', $tema->juegos->pluck('id'))
                ->where('completado', true)
                ->pluck('juego_id')
                ->unique();
        }

        $evaluacionesCompletadas = collect();
        if ($tema->evaluaciones->isNotEmpty()) {
            $evaluacionesCompletadas = \App\Models\ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
                ->whereIn('evaluacion_id', $tema->evaluaciones->pluck('id'))
                ->with('evaluacion')
                ->orderBy('fecha_realizacion', 'desc')
                ->get()
                ->groupBy('evaluacion_id')
                ->map(function ($items) {
                    $resultado = $items->first();
                    // Alias para mantener compatibilidad con la vista actual.
                    $resultado->puntuacion_obtenida = $resultado->puntaje_obtenido;
                    $resultado->puntuacion_total = optional($resultado->evaluacion)->puntaje_total ?? 0;
                    return $resultado;
                });
        }

        $temaCompletado = optional($progreso)->estado === 'completado';

        return view('estudiante.temas.show', compact(
            'tema',
            'progreso',
            'temaCompletado',
            'temaAnterior',
            'temaSiguiente',
            'juegosCompletados',
            'evaluacionesCompletadas'
        ));
    }

    /**
     * Actualizar progreso de lectura
     */
    public function actualizarLectura(Request $request, $temaId)
    {
        $estudiante = Auth::user();
        $tema = Tema::findOrFail($temaId);

        $request->validate([
            'porcentaje' => 'required|integer|min:0|max:100',
        ]);

        $this->progresionService->actualizarLectura($estudiante, $tema, $request->porcentaje);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Progreso de lectura actualizado.');
    }

    /**
     * Marcar tema como completado
     */
    public function completar($temaId)
    {
        $estudiante = Auth::user();
        $tema = Tema::findOrFail($temaId);

        $resultado = $this->progresionService->completarTema($estudiante, $tema);

        if ($resultado['exito']) {
            $mensaje = $resultado['siguiente_tema']
                ? $resultado['mensaje'] . ' Has desbloqueado: ' . $resultado['siguiente_tema_nombre']
                : $resultado['mensaje'] . ' Has completado todos los temas de esta asignatura!';

            return redirect()->route('estudiante.asignaturas.show', $tema->asignatura->slug)
                ->with('success', $mensaje);
        }

        return redirect()->back()->with('error', $resultado['mensaje']);
    }
}
