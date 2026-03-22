<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Colegio;
use App\Models\Ranking;
use App\Models\LogrosEstudiante;
use App\Models\IntentosJuego;
use App\Models\ResultadosEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    /**
     * Mostrar perfil
     */
    public function show()
    {
        $estudiante = Auth::user();
        $colegios = Colegio::activos()->orderBy('nombre')->get();

        // Estadísticas
        $estadisticas = [
            'puntos' => $estudiante->puntos_totales ?? $estudiante->puntos ?? 0,
            'nivel_max' => $estudiante->nivel ?? 1,
            'juegos' => IntentosJuego::where('estudiante_id', $estudiante->id)->count(),
            'evaluaciones' => ResultadosEvaluacion::where('estudiante_id', $estudiante->id)->count(),
        ];

        // Rankings - CORREGIDO: usar puntaje_total
        $rankings = Ranking::with('asignatura')
            ->where('estudiante_id', $estudiante->id)
            ->orderBy('puntaje_total', 'desc')  // ✅ CORREGIDO
            ->get();

        // Logros
        $logros = LogrosEstudiante::with('logro')
            ->where('estudiante_id', $estudiante->id)
            ->get();

        // Actividad reciente (últimos 10)
        $actividades = collect();

        // Juegos recientes
        $juegosRecientes = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($item) {
                return (object)[
                    'tipo' => 'juego',
                    'titulo' => $item->juego->titulo ?? 'Juego',
                    'puntuacion' => $item->puntuacion ?? 0,
                    'created_at' => $item->created_at,
                ];
            });

        // Evaluaciones recientes
        $evaluacionesRecientes = ResultadosEvaluacion::with('evaluacion')
            ->where('estudiante_id', $estudiante->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($item) {
                // Calcular porcentaje
                $puntuacion = 0;
                if (isset($item->puntaje_total) && $item->puntaje_total > 0) {
                    $puntuacion = round(($item->puntaje_obtenido / $item->puntaje_total) * 100);
                }
                
                return (object)[
                    'tipo' => 'evaluacion',
                    'titulo' => $item->evaluacion->titulo ?? 'Evaluación',
                    'puntuacion' => $puntuacion,
                    'created_at' => $item->created_at,
                ];
            });

        $actividades = $juegosRecientes->merge($evaluacionesRecientes)
            ->sortByDesc('created_at')
            ->take(10);

        return view('estudiante.perfil.show', compact(
            'estudiante', 
            'colegios', 
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

        // Eliminar avatar anterior
        if ($estudiante->avatar) {
            Storage::delete('public/avatars/' . $estudiante->avatar);
        }

        // Guardar nuevo avatar
        $fileName = time() . '_' . $estudiante->id . '.' . $request->avatar->extension();
        $request->avatar->storeAs('public/avatars', $fileName);

        $estudiante->update(['avatar' => $fileName]);

        return redirect()->back()->with('success', 'Avatar actualizado exitosamente.');
    }

    /**
     * Cambiar contraseña
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
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $estudiante->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Contraseña cambiada exitosamente.');
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
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        // Eliminar avatar
        if ($estudiante->avatar) {
            Storage::delete('public/avatars/' . $estudiante->avatar);
        }

        // Soft delete
        $estudiante->delete();

        Auth::logout();

        return redirect()->route('login')->with('success', 'Tu cuenta ha sido eliminada.');
    }
}