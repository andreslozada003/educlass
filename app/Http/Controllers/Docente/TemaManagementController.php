<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Tema;
use App\Models\Asignatura;
use App\Models\User;
use App\Notifications\NuevoTemaDisponibleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TemaManagementController extends Controller
{
    /**
     * Listar temas
     */
    public function index(Request $request)
    {
        $query = Tema::with('asignatura')
            ->where('docente_creador_id', Auth::id());

        // Filtros
        if ($request->filled('asignatura')) {
            $query->where('asignatura_id', $request->asignatura);
        }

        if ($request->filled('periodo')) {
            $query->where('periodo_academico', $request->periodo);
        }

        if ($request->filled('dificultad')) {
            $query->where('dificultad', $request->dificultad);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $temas = $query->orderBy('orden')->paginate(15);
        $asignaturas = Asignatura::activas()->get();

        return view('docente.temas.index', compact('temas', 'asignaturas'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $asignaturas = Asignatura::activas()->get();
        return view('docente.temas.create', compact('asignaturas'));
    }

    /**
     * Guardar nuevo tema
     */
    public function store(Request $request)
    {
        $request->validate([
            'asignatura_id' => 'required|exists:asignaturas,id',
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string',
            'dificultad' => 'required|integer|between:1,4',
            'periodo_academico' => 'required|integer|between:1,3',
            'tiempo_estimado_minutos' => 'required|integer|min:1',
            'imagen_destacada' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_url' => 'nullable|url',
        ]);

        // Generar slug único
        $slug = Str::slug($request->titulo);
        $contador = 1;
        while (Tema::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->titulo) . '-' . $contador;
            $contador++;
        }

        // Obtener orden
        $orden = Tema::where('asignatura_id', $request->asignatura_id)->max('orden') + 1;

        // Subir imagen
        $imagenPath = null;
        if ($request->hasFile('imagen_destacada')) {
            $imagenPath = $request->file('imagen_destacada')->store('temas', 'public');
        }

        $tema = Tema::create([
            'asignatura_id' => $request->asignatura_id,
            'titulo' => $request->titulo,
            'slug' => $slug,
            'contenido' => $request->contenido,
            'dificultad' => $request->dificultad,
            'periodo_academico' => $request->periodo_academico,
            'orden' => $orden,
            'imagen_destacada' => $imagenPath,
            'video_url' => $request->video_url,
            'tiempo_estimado_minutos' => $request->tiempo_estimado_minutos,
            'activo' => $request->boolean('activo', true),
            'docente_creador_id' => Auth::id(),
        ]);

        if ($tema->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($tema) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoTemaDisponibleNotification($tema));
                    }
                });
        }

        return redirect()->route('docente.temas.index')
            ->with('success', 'Tema creado exitosamente.');
    }

    /**
     * Mostrar tema
     */
    public function show($id)
    {
        $tema = Tema::with(['asignatura', 'juegos', 'evaluaciones'])
            ->where('docente_creador_id', Auth::id())
            ->findOrFail($id);

        $recomendacionesCategorias = $this->obtenerRecomendacionesCategorias($tema);

        return view('docente.temas.show', compact('tema', 'recomendacionesCategorias'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $tema = Tema::where('docente_creador_id', Auth::id())->findOrFail($id);
        $asignaturas = Asignatura::activas()->get();

        return view('docente.temas.edit', compact('tema', 'asignaturas'));
    }

    /**
     * Actualizar tema
     */
    public function update(Request $request, $id)
    {
        $tema = Tema::where('docente_creador_id', Auth::id())->findOrFail($id);
        $estabaActivo = (bool) $tema->activo;

        $request->validate([
            'asignatura_id' => 'required|exists:asignaturas,id',
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string',
            'dificultad' => 'required|integer|between:1,4',
            'periodo_academico' => 'required|integer|between:1,3',
            'tiempo_estimado_minutos' => 'required|integer|min:1',
            'imagen_destacada' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_url' => 'nullable|url',
        ]);

        // Actualizar slug si cambió el título
        if ($tema->titulo !== $request->titulo) {
            $slug = Str::slug($request->titulo);
            $contador = 1;
            while (Tema::where('slug', $slug)->where('id', '!=', $tema->id)->exists()) {
                $slug = Str::slug($request->titulo) . '-' . $contador;
                $contador++;
            }
            $tema->slug = $slug;
        }

        $data = [
            'asignatura_id' => $request->asignatura_id,
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'dificultad' => $request->dificultad,
            'periodo_academico' => $request->periodo_academico,
            'video_url' => $request->video_url,
            'tiempo_estimado_minutos' => $request->tiempo_estimado_minutos,
            'activo' => $request->boolean('activo', true),
        ];

        // Subir nueva imagen (si aplica)
        if ($request->hasFile('imagen_destacada')) {
            if ($tema->imagen_destacada) {
                Storage::delete('public/' . $tema->imagen_destacada);
            }
            $data['imagen_destacada'] = $request->file('imagen_destacada')->store('temas', 'public');
        }

        $tema->update($data);

        if (!$estabaActivo && $tema->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($tema) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoTemaDisponibleNotification($tema));
                    }
                });
        }

        return redirect()->route('docente.temas.index')
            ->with('success', 'Tema actualizado exitosamente.');
    }

    /**
     * Eliminar tema
     */
    public function destroy($id)
    {
        $tema = Tema::where('docente_creador_id', Auth::id())->findOrFail($id);

        // Eliminar imagen
        if ($tema->imagen_destacada) {
            Storage::delete('public/' . $tema->imagen_destacada);
        }

        $tema->delete();

        return redirect()->route('docente.temas.index')
            ->with('success', 'Tema eliminado exitosamente.');
    }

    /**
     * Reordenar temas
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'temas' => 'required|array',
            'temas.*.id' => 'required|exists:temas,id',
            'temas.*.orden' => 'required|integer',
        ]);

        foreach ($request->temas as $temaData) {
            Tema::where('id', $temaData['id'])
                ->where('docente_creador_id', Auth::id())
                ->update(['orden' => $temaData['orden']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Duplicar tema
     */
    public function duplicate($id)
    {
        $tema = Tema::with(['juegos.preguntas', 'evaluaciones.preguntas'])
            ->where('docente_creador_id', Auth::id())
            ->findOrFail($id);

        // Crear copia del tema
        $nuevoTema = $tema->replicate();
        $nuevoTema->titulo = $tema->titulo . ' (Copia)';
        $nuevoTema->slug = Str::slug($nuevoTema->titulo) . '-' . time();
        $nuevoTema->activo = false;
        $nuevoTema->orden = Tema::where('asignatura_id', $tema->asignatura_id)->max('orden') + 1;
        $nuevoTema->save();

        // Duplicar juegos
        foreach ($tema->juegos as $juego) {
            $nuevoJuego = $juego->replicate();
            $nuevoJuego->tema_id = $nuevoTema->id;
            $nuevoJuego->activo = false;
            $nuevoJuego->save();

            // Duplicar preguntas del juego
            foreach ($juego->preguntas as $pregunta) {
                $nuevaPregunta = $pregunta->replicate();
                $nuevaPregunta->juego_id = $nuevoJuego->id;
                $nuevaPregunta->save();
            }
        }

        // Duplicar evaluaciones
        foreach ($tema->evaluaciones as $evaluacion) {
            $nuevaEvaluacion = $evaluacion->replicate();
            $nuevaEvaluacion->tema_id = $nuevoTema->id;
            $nuevaEvaluacion->activa = false;
            $nuevaEvaluacion->save();

            // Duplicar preguntas de la evaluación
            foreach ($evaluacion->preguntas as $pregunta) {
                $nuevaPregunta = $pregunta->replicate();
                $nuevaPregunta->evaluacion_id = $nuevaEvaluacion->id;
                $nuevaPregunta->save();
            }
        }

        return redirect()->route('docente.temas.edit', $nuevoTema->id)
            ->with('success', 'Tema duplicado exitosamente.');
    }

    /**
     * Obtener recomendaciones de categorias por asignatura.
     */
    private function obtenerRecomendacionesCategorias(Tema $tema): ?array
    {
        $slug = optional($tema->asignatura)->slug;

        $recomendaciones = [
            'lenguaje' => [
                'titulo' => 'Español',
                'categorias' => [
                    'Quiz',
                    'Memoria / emparejar',
                    'Rompecabezas',
                    'Carrera o tablero',
                ],
                'habilidades' => [
                    'lectura',
                    'escritura',
                    'ortografía',
                    'comprensión lectora',
                    'formación de oraciones',
                ],
            ],
            'ciencias' => [
                'titulo' => 'Ciencias',
                'categorias' => [
                    'Aventura / misiones',
                    'Quiz',
                    'Escape room',
                    'Memoria / emparejar',
                ],
                'habilidades' => [
                    'clasificación',
                    'observación',
                    'conceptos del entorno',
                    'experimentación',
                    'relaciones entre elementos',
                ],
            ],
            'ingles' => [
                'titulo' => 'Inglés',
                'categorias' => [
                    'Memoria / emparejar',
                    'Bingo educativo',
                    'Quiz',
                    'Aventura',
                ],
                'habilidades' => [
                    'vocabulario',
                    'pronunciación',
                    'asociación palabra-imagen',
                    'frases básicas',
                ],
            ],
            'matematicas' => [
                'titulo' => 'Matemáticas',
                'categorias' => [
                    'Quiz',
                    'Carrera o tablero',
                    'Rompecabezas',
                    'Escape room',
                ],
                'habilidades' => [
                    'operaciones',
                    'lógica',
                    'resolución de problemas',
                    'cálculo mental',
                ],
            ],
        ];

        return $recomendaciones[$slug] ?? null;
    }
}
