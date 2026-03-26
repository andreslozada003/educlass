<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Colegio;
use App\Models\IntentosJuego;
use App\Models\LogrosEstudiante;
use App\Models\Ranking;
use App\Models\ResultadosEvaluacion;
use App\Services\GamificacionService;
use App\Services\ProgresionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PerfilController extends Controller
{
    protected $progresionService;
    protected $gamificacionService;

    public function __construct(
        ProgresionService $progresionService,
        GamificacionService $gamificacionService
    ) {
        $this->progresionService = $progresionService;
        $this->gamificacionService = $gamificacionService;
    }

    /**
     * Mostrar perfil
     */
    public function show()
    {
        $estudiante = Auth::user()->loadMissing('colegio');
        $colegios = Colegio::activos()->orderBy('nombre')->get();

        $resumen = $this->progresionService->getResumenProgreso($estudiante);
        $gamificacion = $this->gamificacionService->getEstadisticas($estudiante);

        $estadisticas = [
            'puntos' => $gamificacion['puntos_totales'] ?? ($resumen['puntos_totales'] ?? 0),
            'nivel_max' => $resumen['nivel_maximo'] ?? ($resumen['nivel_global'] ?? 1),
            'juegos' => $gamificacion['total_juegos'] ?? 0,
            'evaluaciones' => $gamificacion['total_evaluaciones'] ?? 0,
            'temas_completados' => $resumen['temas_completados'] ?? ($resumen['completadas'] ?? 0),
            'porcentaje_general' => $resumen['porcentaje_general'] ?? 0,
            'logros' => $gamificacion['logros_obtenidos'] ?? 0,
        ];

        $rankings = Ranking::with('asignatura')
            ->where('estudiante_id', $estudiante->id)
            ->orderByRaw("CASE WHEN categoria = 'general' THEN 0 ELSE 1 END")
            ->orderBy('puntaje_total', 'desc')
            ->get();

        $logros = LogrosEstudiante::with('logro')
            ->where('estudiante_id', $estudiante->id)
            ->orderByDesc('fecha_obtenido')
            ->get();

        $juegosRecientes = IntentosJuego::with(['juego.tema.asignatura', 'juego.preguntasActivas'])
            ->where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->orderByDesc('fecha_intento')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $puntajeTotal = optional($item->juego)->puntaje_maximo
                    ?: (optional($item->juego)->puntaje_base ?: 0);

                return (object) [
                    'tipo' => 'juego',
                    'titulo' => optional($item->juego)->titulo ?? 'Juego',
                    'detalle' => data_get($item, 'juego.tema.asignatura.nombre', 'Juego educativo'),
                    'puntuacion' => round($item->porcentaje_aciertos ?? 0),
                    'puntaje_obtenido' => $item->puntaje_obtenido ?? 0,
                    'puntaje_total' => $puntajeTotal,
                    'created_at' => $item->fecha_intento ?? $item->created_at,
                ];
            });

        $evaluacionesRecientes = ResultadosEvaluacion::with('evaluacion.tema.asignatura')
            ->where('estudiante_id', $estudiante->id)
            ->orderByDesc('fecha_realizacion')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'tipo' => 'evaluacion',
                    'titulo' => optional($item->evaluacion)->titulo ?? 'Evaluacion',
                    'detalle' => data_get($item, 'evaluacion.tema.asignatura.nombre', 'Evaluacion'),
                    'puntuacion' => round($item->porcentaje_obtenido ?? 0),
                    'puntaje_obtenido' => $item->puntaje_obtenido ?? 0,
                    'puntaje_total' => optional($item->evaluacion)->puntaje_total ?: 100,
                    'created_at' => $item->fecha_realizacion ?? $item->created_at,
                ];
            });

        $actividades = $juegosRecientes
            ->merge($evaluacionesRecientes)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return view('estudiante.perfil.show', compact(
            'estudiante',
            'colegios',
            'resumen',
            'estadisticas',
            'rankings',
            'logros',
            'actividades'
        ));
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $estudiante = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'colegio_id' => 'required|exists:colegios,id',
        ]);

        $estudiante->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'colegio_id' => $request->colegio_id,
        ]);

        return redirect()->back()->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Actualizar avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $estudiante = Auth::user();

        if ($estudiante->avatar) {
            Storage::delete('public/avatars/' . $estudiante->avatar);
        }

        $fileName = time() . '_' . $estudiante->id . '.' . $request->avatar->extension();
        $request->avatar->storeAs('public/avatars', $fileName);

        $estudiante->update(['avatar' => $fileName]);

        return redirect()->back()->with('success', 'Avatar actualizado exitosamente.');
    }

    /**
     * Cambiar contrasena
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $estudiante = Auth::user();

        if (!Hash::check($request->current_password, $estudiante->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contrasena actual es incorrecta.'],
            ]);
        }

        $estudiante->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Contrasena cambiada exitosamente.');
    }

    /**
     * Eliminar cuenta
     */
    public function eliminarCuenta(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $estudiante = Auth::user();

        if (!Hash::check($request->password, $estudiante->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contrasena es incorrecta.'],
            ]);
        }

        if ($estudiante->avatar) {
            Storage::delete('public/avatars/' . $estudiante->avatar);
        }

        $estudiante->delete();
        Auth::logout();

        return redirect()->route('login')->with('success', 'Tu cuenta ha sido eliminada.');
    }
}
