<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Juego;
use App\Models\Tema;
use App\Models\User;
use App\Models\PreguntasJuego;
use App\Notifications\NuevoJuegoDisponibleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JuegoManagementController extends Controller
{
    /**
     * Listar juegos
     */
    public function index(Request $request)
    {
        $query = Juego::with('tema.asignatura')
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

        $juegos = $query->orderBy('created_at', 'desc')->paginate(15);
        $tipos = config('juegos.tipos');

        return view('docente.juegos.index', compact('juegos', 'tipos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $temas = Tema::where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = config('juegos.tipos');
        $temaPreseleccionado = $request->tema_id;

        return view('docente.juegos.create', compact('temas', 'tipos', 'temaPreseleccionado'));
    }

    /**
     * Guardar juego
     */
    public function store(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'tipo' => 'required|in:quiz,memoria,arrastrar,completar,ordenar,sopa,clasificar',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'dificultad' => 'required|integer|between:1,4',
            'intentos_maximos' => 'required|integer|min:1|max:10',
            'puntaje_base' => 'required|integer|min:10',
            'tiempo_limite_segundos' => 'nullable|integer|min:30',
        ]);

        // Verificar que el tema pertenece al docente
        Tema::where('id', $request->tema_id)
            ->where('docente_creador_id', Auth::id())
            ->firstOrFail();

        $configuracion = $this->getConfiguracionPorTipo($request->tipo);

        $juego = Juego::create([
            'tema_id' => $request->tema_id,
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'configuracion' => $configuracion,
            'dificultad' => $request->dificultad,
            'intentos_maximos' => $request->intentos_maximos,
            'puntaje_base' => $request->puntaje_base,
            'tiempo_limite_segundos' => $request->tiempo_limite_segundos,
            'activo' => $request->boolean('activo', false),
        ]);

        if ($juego->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($juego) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoJuegoDisponibleNotification($juego));
                    }
                });
        }

        return redirect()->route('docente.juegos.preguntas', $juego->id)
            ->with('success', 'Juego creado. Ahora agrega las preguntas.');
    }

    /**
     * Obtener configuración por tipo
     */
    private function getConfiguracionPorTipo(string $tipo): array
    {
        return config("juegos.tipos.{$tipo}.configuracion", []);
    }

    /**
     * Mostrar juego
     */
    public function show($id)
    {
        $juego = Juego::with(['tema.asignatura', 'preguntas'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($id);

        return view('docente.juegos.show', compact('juego'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);
        $estabaActivo = (bool) $juego->activo;

        $temas = Tema::where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = config('juegos.tipos');

        return view('docente.juegos.edit', compact('juego', 'temas', 'tipos'));
    }

    /**
     * Actualizar juego
     */
    public function update(Request $request, $id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'dificultad' => 'required|integer|between:1,4',
            'intentos_maximos' => 'required|integer|min:1|max:10',
            'puntaje_base' => 'required|integer|min:10',
            'tiempo_limite_segundos' => 'nullable|integer|min:30',
        ]);

        $juego->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'dificultad' => $request->dificultad,
            'intentos_maximos' => $request->intentos_maximos,
            'puntaje_base' => $request->puntaje_base,
            'tiempo_limite_segundos' => $request->tiempo_limite_segundos,
            'activo' => $request->boolean('activo', false),
        ]);

        if (!$estabaActivo && $juego->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($juego) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoJuegoDisponibleNotification($juego));
                    }
                });
        }

        return redirect()->route('docente.juegos.index')
            ->with('success', 'Juego actualizado exitosamente.');
    }

    /**
     * Eliminar juego
     */
    public function destroy($id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $juego->delete();

        return redirect()->route('docente.juegos.index')
            ->with('success', 'Juego eliminado exitosamente.');
    }

    /**
     * Vista previa del juego
     */
    public function preview($id)
    {
        $juego = Juego::with(['tema.asignatura', 'preguntasActivas'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($id);

        return view('docente.juegos.preview', compact('juego'));
    }

    /**
     * Gestión de preguntas
     */
    public function preguntas($juegoId)
    {
        $juego = Juego::with('preguntas')
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($juegoId);

        return view('docente.juegos.preguntas', compact('juego'));
    }

    /**
     * Agregar pregunta
     */
    public function agregarPregunta(Request $request, $juegoId)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($juegoId);

        $request->validate([
            'enunciado' => 'required|string',
            'tipo' => 'required|in:opcion_multiple,verdadero_falso,emparejamiento,ordenamiento',
            'opciones' => 'nullable|array',
            'respuesta_correcta' => 'required',
            'puntaje' => 'required|integer|min:1',
        ]);

        $orden = $juego->preguntas()->max('orden') + 1;

        PreguntasJuego::create([
            'juego_id' => $juego->id,
            'tipo' => $request->tipo,
            'enunciado' => $request->enunciado,
            'opciones' => $request->opciones,
            'respuesta_correcta' => is_array($request->respuesta_correcta) 
                ? $request->respuesta_correcta 
                : [$request->respuesta_correcta],
            'puntaje' => $request->puntaje,
            'orden' => $orden,
            'activo' => true,
        ]);

        return redirect()->back()->with('success', 'Pregunta agregada exitosamente.');
    }

    /**
     * Eliminar pregunta
     */
    public function eliminarPregunta($juegoId, $preguntaId)
    {
        $pregunta = PreguntasJuego::whereHas('juego.tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->where('id', $preguntaId)->firstOrFail();

        $pregunta->delete();

        return redirect()->back()->with('success', 'Pregunta eliminada exitosamente.');
    }
}
