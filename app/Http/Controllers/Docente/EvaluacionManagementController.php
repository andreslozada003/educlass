<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\Tema;
use App\Models\User;
use App\Models\PreguntasEvaluacion;
use App\Models\ResultadosEvaluacion;
use App\Notifications\NuevaEvaluacionDisponibleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluacionManagementController extends Controller
{
    /**
     * Listar evaluaciones
     */
    public function index(Request $request)
    {
        $query = Evaluacion::with('tema.asignatura')
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            });

        if ($request->filled('asignatura')) {
            $query->whereHas('tema', function ($q) use ($request) {
                $q->where('asignatura_id', $request->asignatura);
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $evaluaciones = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('docente.evaluaciones.index', compact('evaluaciones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $temas = Tema::with('asignatura')->where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = [
            'diagnostica' => 'Diagnóstica',
            'formativa' => 'Formativa',
            'sumativa' => 'Sumativa',
        ];

        $temaPreseleccionado = $request->tema_id;

        return view('docente.evaluaciones.create', compact('temas', 'tipos', 'temaPreseleccionado'));
    }

    /**
     * Guardar evaluación
     */
    public function store(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:diagnostica,formativa,sumativa',
            'tiempo_limite_minutos' => 'required|integer|min:5|max:180',
            'intentos_permitidos' => 'required|integer|min:1|max:10',
            'umbral_aprobacion' => 'required|integer|min:50|max:100',
        ]);

        // Verificar que el tema pertenece al docente
        Tema::where('id', $request->tema_id)
            ->where('docente_creador_id', Auth::id())
            ->firstOrFail();

        $evaluacion = Evaluacion::create([
            'tema_id' => $request->tema_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos,
            'intentos_permitidos' => $request->intentos_permitidos,
            'umbral_aprobacion' => $request->umbral_aprobacion,
            'puntaje_total' => 0,
            'activa' => $request->boolean('activa', false),
        ]);

        if ($evaluacion->activa) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($evaluacion) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevaEvaluacionDisponibleNotification($evaluacion));
                    }
                });
        }

        return redirect()->route('docente.evaluaciones.preguntas', $evaluacion->id)
            ->with('success', 'Evaluación creada. Ahora agrega las preguntas.');
    }

    /**
     * Mostrar evaluación
     */
    public function show($id)
    {
        $evaluacion = Evaluacion::with(['tema.asignatura', 'preguntas', 'resultados.estudiante'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($id);

        // Estadísticas
        $totalResultados = $evaluacion->resultados->count();
        $aprobados = $evaluacion->resultados->where('aprobado', true)->count();
        $promedioPuntaje = $evaluacion->resultados->avg('puntaje_obtenido');

        return view('docente.evaluaciones.show', compact(
            'evaluacion',
            'totalResultados',
            'aprobados',
            'promedioPuntaje'
        ));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $evaluacion = Evaluacion::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $temas = Tema::where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = [
            'diagnostica' => 'Diagnóstica',
            'formativa' => 'Formativa',
            'sumativa' => 'Sumativa',
        ];

        return view('docente.evaluaciones.edit', compact('evaluacion', 'temas', 'tipos'));
    }

    /**
     * Actualizar evaluación
     */
    public function update(Request $request, $id)
    {
        $evaluacion = Evaluacion::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);
        $estabaActiva = (bool) $evaluacion->activa;

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:diagnostica,formativa,sumativa',
            'tiempo_limite_minutos' => 'required|integer|min:5|max:180',
            'intentos_permitidos' => 'required|integer|min:1|max:10',
            'umbral_aprobacion' => 'required|integer|min:50|max:100',
        ]);

        $evaluacion->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos,
            'intentos_permitidos' => $request->intentos_permitidos,
            'umbral_aprobacion' => $request->umbral_aprobacion,
            'activa' => $request->boolean('activa', false),
        ]);

        if (!$estabaActiva && $evaluacion->activa) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($evaluacion) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevaEvaluacionDisponibleNotification($evaluacion));
                    }
                });
        }

        return redirect()->route('docente.evaluaciones.index')
            ->with('success', 'Evaluación actualizada exitosamente.');
    }

    /**
     * Eliminar evaluación
     */
    public function destroy($id)
    {
        $evaluacion = Evaluacion::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $evaluacion->delete();

        return redirect()->route('docente.evaluaciones.index')
            ->with('success', 'Evaluación eliminada exitosamente.');
    }

    /**
     * Gestión de preguntas
     */
    public function preguntas($evaluacionId)
    {
        $evaluacion = Evaluacion::with('preguntas')
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($evaluacionId);

        return view('docente.evaluaciones.preguntas', compact('evaluacion'));
    }

    /**
     * Agregar pregunta
     */
    public function agregarPregunta(Request $request, $evaluacionId)
    {
        $evaluacion = Evaluacion::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($evaluacionId);

        $request->validate([
            'enunciado' => 'required|string',
            'tipo' => 'required|in:multiple,vf,corta,emparejamiento',
            'opciones' => 'nullable|array',
            'respuesta_correcta' => 'required',
            'puntaje' => 'required|integer|min:1',
        ]);

        $orden = $evaluacion->preguntas()->max('orden') + 1;

        PreguntasEvaluacion::create([
            'evaluacion_id' => $evaluacion->id,
            'tipo' => $request->tipo,
            'enunciado' => $request->enunciado,
            'opciones' => $request->opciones,
            'respuesta_correcta' => is_string($request->respuesta_correcta) 
                ? $request->respuesta_correcta 
                : json_encode($request->respuesta_correcta),
            'puntaje' => $request->puntaje,
            'orden' => $orden,
        ]);

        // Actualizar puntaje total
        $evaluacion->puntaje_total = $evaluacion->preguntas()->sum('puntaje');
        $evaluacion->save();

        return redirect()->back()->with('success', 'Pregunta agregada exitosamente.');
    }

    /**
     * Eliminar pregunta
     */
    public function eliminarPregunta($evaluacionId, $preguntaId)
    {
        $pregunta = PreguntasEvaluacion::whereHas('evaluacion.tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->where('id', $preguntaId)->firstOrFail();

        $pregunta->delete();

        // Actualizar puntaje total
        $evaluacion = Evaluacion::find($evaluacionId);
        $evaluacion->puntaje_total = $evaluacion->preguntas()->sum('puntaje');
        $evaluacion->save();

        return redirect()->back()->with('success', 'Pregunta eliminada exitosamente.');
    }

    /**
     * Ver resultados de evaluación
     */
    public function resultados($evaluacionId)
    {
        $evaluacion = Evaluacion::with(['resultados.estudiante', 'tema.asignatura'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($evaluacionId);

        $resultados = $evaluacion->resultados()
            ->with('estudiante')
            ->orderBy('fecha_realizacion', 'desc')
            ->paginate(20);

        return view('docente.evaluaciones.resultados', compact('evaluacion', 'resultados'));
    }

    /**
     * Reiniciar intentos de un estudiante para una evaluacion.
     */
    public function reiniciarIntentosEstudiante($evaluacionId, $estudianteId)
    {
        $evaluacion = Evaluacion::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($evaluacionId);

        $eliminados = ResultadosEvaluacion::where('evaluacion_id', $evaluacion->id)
            ->where('estudiante_id', $estudianteId)
            ->delete();

        $mensaje = $eliminados > 0
            ? "Intentos reiniciados correctamente ({$eliminados} registro(s) eliminado(s))."
            : 'No habia intentos para reiniciar en este estudiante.';

        return redirect()->back()->with('success', $mensaje);
    }
}
