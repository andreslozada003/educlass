<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\User;
use App\Models\Colegio;
use App\Notifications\MensajeDocenteNotification;
use App\Services\ProgresionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EstudianteManagementController extends Controller
{
    protected $progresionService;

    public function __construct(ProgresionService $progresionService)
    {
        $this->progresionService = $progresionService;
    }

    /**
     * Listar estudiantes
     */
    public function index(Request $request)
    {
        $query = User::estudiantes()
            ->with([
                'colegio',
                'rankings',
                'progresoEstudiante',
                'calificacionesPeriodo'
            ])
            ->where('activo', true);

        if ($request->filled('colegio')) {
            $query->where('colegio_id', $request->colegio);
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        $asignaturas = Asignatura::activas()->get();
        $colegios = Colegio::activos()->get();

        $estudiantes = $query->orderBy('nombre')->paginate(20);

        return view('docente.estudiantes.index', compact(
            'estudiantes',
            'asignaturas',
            'colegios'
        ));
    }

    /**
     * Mostrar detalle de estudiante
     */
    public function show($id)
    {
        $estudiante = User::estudiantes()
            ->with([
                'colegio',
                'rankings',
                'progresoEstudiante',
                'calificacionesPeriodo'
            ])
            ->findOrFail($id);

        // Sincronizar temas que ya cumplen condiciones de finalizacion pero no quedaron "completado".
        $progresosParaSincronizar = \App\Models\ProgresoEstudiante::where('estudiante_id', $id)
            ->with('tema')
            ->get();

        foreach ($progresosParaSincronizar as $progresoItem) {
            $tema = $progresoItem->tema;
            if (!$tema || $progresoItem->estado === 'completado') {
                continue;
            }

            // Reglas base: lectura minima cumplida.
            if (($progresoItem->porcentaje_lectura ?? 0) < 80) {
                continue;
            }

            // Si el tema tiene juego, debe estar completado por el estudiante.
            $juegoCumplido = true;
            $juegoPrincipal = $tema->juego_principal;
            if ($juegoPrincipal) {
                $juegoCumplido = \App\Models\IntentosJuego::where('estudiante_id', $id)
                    ->where('juego_id', $juegoPrincipal->id)
                    ->where('completado', true)
                    ->exists();
            }

            // Si el tema tiene evaluacion, debe estar aprobada.
            $evaluacionCumplida = true;
            $evaluacionPrincipal = $tema->evaluacion_principal;
            if ($evaluacionPrincipal) {
                $evaluacionCumplida = \App\Models\ResultadosEvaluacion::where('estudiante_id', $id)
                    ->where('evaluacion_id', $evaluacionPrincipal->id)
                    ->where('aprobado', true)
                    ->exists();
            }

            if ($juegoCumplido && $evaluacionCumplida) {
                $this->progresionService->completarTema($estudiante, $tema);
            }
        }

        $progresoPorTema = \App\Models\ProgresoEstudiante::where('estudiante_id', $id)
            ->with('tema.asignatura')
            ->get()
            ->filter(function ($item) {
                return $item->tema && $item->tema->asignatura;
            });

        $progreso = $progresoPorTema
            ->groupBy(function ($item) {
                return $item->tema->asignatura->id;
            })
            ->map(function ($items) {
                $asignatura = $items->first()->tema->asignatura;
                $totalTemas = $items->count();
                $temasCompletados = $items->where('estado', 'completado')->count();
                $porcentajeCompletado = $totalTemas > 0
                    ? round(($temasCompletados / $totalTemas) * 100, 2)
                    : 0;

                return (object) [
                    'asignatura' => $asignatura,
                    'nivel_actual' => $this->progresionService->calcularNivelActual($porcentajeCompletado),
                    'temas_completados' => $temasCompletados,
                    'porcentaje_completado' => $porcentajeCompletado,
                ];
            })
            ->values();
        $estadisticas = app(\App\Services\GamificacionService::class)->getEstadisticas($estudiante);
        // Compatibilidad con claves que usa la vista docente.
        $estadisticas['juegos_completados'] = $estadisticas['total_juegos'] ?? 0;
        $estadisticas['evaluaciones_completadas'] = $estadisticas['evaluaciones_aprobadas'] ?? 0;
        $estadisticas['logros'] = $estadisticas['logros_obtenidos'] ?? 0;

        $ultimosJuegos = \App\Models\IntentosJuego::where('estudiante_id', $id)
            ->with('juego.tema.asignatura')
            ->orderBy('fecha_intento', 'desc')
            ->limit(5)
            ->get();

        $ultimasEvaluaciones = \App\Models\ResultadosEvaluacion::where('estudiante_id', $id)
            ->with('evaluacion.tema.asignatura')
            ->orderBy('fecha_realizacion', 'desc')
            ->limit(5)
            ->get();

        $logros = \App\Models\LogrosEstudiante::where('estudiante_id', $id)
            ->with('logro')
            ->orderBy('fecha_obtenido', 'desc')
            ->limit(8)
            ->get();

        $calificaciones = \App\Models\CalificacionesPeriodo::where('estudiante_id', $id)
            ->with('asignatura')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($calificacion) {
                // La vista espera nota_final; usamos el promedio ponderado.
                $calificacion->nota_final = $calificacion->promedio_ponderado;
                return $calificacion;
            });

        $actividadesJuegos = $ultimosJuegos->map(function ($intento) {
            return (object) [
                'tipo' => 'juego',
                'juego' => $intento->juego,
                'evaluacion' => null,
                'asignatura' => optional(optional($intento->juego)->tema)->asignatura,
                'puntuacion_obtenida' => $intento->puntaje_obtenido ?? 0,
                'puntuacion_total' => optional($intento->juego)->puntaje_maximo
                    ?: (optional($intento->juego)->puntaje_base ?: 100),
                'created_at' => $intento->fecha_intento ?? $intento->created_at,
            ];
        });

        $actividadesEvaluaciones = $ultimasEvaluaciones->map(function ($resultado) {
            return (object) [
                'tipo' => 'evaluacion',
                'juego' => null,
                'evaluacion' => $resultado->evaluacion,
                'asignatura' => optional(optional($resultado->evaluacion)->tema)->asignatura,
                'puntuacion_obtenida' => $resultado->puntaje_obtenido ?? 0,
                'puntuacion_total' => optional($resultado->evaluacion)->puntaje_total ?: 100,
                'created_at' => $resultado->fecha_realizacion ?? $resultado->created_at,
            ];
        });

        $actividades = $actividadesJuegos
            ->merge($actividadesEvaluaciones)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return view('docente.estudiantes.show', compact(
            'estudiante',
            'progreso',
            'estadisticas',
            'logros',
            'actividades',
            'calificaciones',
            'ultimosJuegos',
            'ultimasEvaluaciones'
        ));
    }

    /**
     * Crear estudiante
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'colegio_id' => 'required|exists:colegios,id',
            'telefono' => 'nullable|string|max:20',
        ]);

        $password = Str::random(10);

        $estudiante = User::create([
            'tipo' => 'estudiante',
            'nombre' => $request->nombre,
            'email' => $request->email,
            'colegio_id' => $request->colegio_id,
            'telefono' => $request->telefono,
            'password' => Hash::make($password),
            'activo' => true,
        ]);

        $this->progresionService->inicializarProgreso($estudiante);

        return redirect()->route('docente.estudiantes.index')
            ->with('success', "Estudiante creado. Contraseña temporal: {$password}");
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword($id)
    {
        $estudiante = User::estudiantes()->findOrFail($id);

        $nuevaPassword = Str::random(10);
        $estudiante->password = Hash::make($nuevaPassword);
        $estudiante->save();

        return redirect()->back()
            ->with('success', "Contraseña reseteada. Nueva contraseña: {$nuevaPassword}");
    }

    /**
     * Desactivar estudiante
     */
    public function desactivar($id)
    {
        $estudiante = User::estudiantes()->findOrFail($id);
        $estudiante->activo = false;
        $estudiante->save();

        return redirect()->back()->with('success', 'Estudiante desactivado exitosamente.');
    }

    /**
     * Reactivar estudiante
     */
    public function reactivar($id)
    {
        $estudiante = User::estudiantes()->findOrFail($id);
        $estudiante->activo = true;
        $estudiante->save();

        return redirect()->back()->with('success', 'Estudiante reactivado exitosamente.');
    }

    /**
     * Enviar mensaje directo del docente al estudiante.
     */
    public function enviarMensaje(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string|min:5|max:1000',
            'tipo' => 'nullable|in:info,success,warning,error',
        ]);

        if (!Schema::hasTable('notifications')) {
            return redirect()->back()->with('error', 'No se puede enviar el mensaje: falta la tabla notifications. Ejecuta migrate.');
        }

        $estudiante = User::estudiantes()->findOrFail($id);
        $docente = Auth::user();

        $estudiante->notify(new MensajeDocenteNotification(
            $docente,
            $request->mensaje,
            $request->input('tipo', 'info')
        ));

        return redirect()->back()->with('success', 'Mensaje enviado al estudiante correctamente.');
    }

    /**
     * Exportar estudiantes
     */
    public function exportar()
    {
        return "Exportando estudiantes...";
    }
}
