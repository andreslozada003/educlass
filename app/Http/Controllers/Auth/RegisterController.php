<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Colegio;
use App\Services\ProgresionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    protected $progresionService;

    public function __construct(ProgresionService $progresionService)
    {
        $this->progresionService = $progresionService;
    }

    /**
     * Mostrar formulario de registro de estudiante
     */
    public function showEstudianteRegistrationForm()
    {
        $colegios = Colegio::activos()->orderBy('nombre')->get();
        return view('auth.register-estudiante', compact('colegios'));
    }

    /**
     * Mostrar formulario de registro de docente
     */
    public function showDocenteRegistrationForm()
    {
        $colegios = Colegio::activos()->orderBy('nombre')->get();
        return view('auth.register-docente', compact('colegios'));
    }

    /**
     * Registrar estudiante
     */
    public function registerEstudiante(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'colegio_id' => 'required|exists:colegios,id',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terminos' => 'required|accepted',
        ], [
            'terminos.required' => 'Debes aceptar los términos y condiciones.',
        ]);

        $user = User::create([
            'tipo' => 'estudiante',
            'nombre' => $request->nombre,
            'colegio_id' => $request->colegio_id,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'activo' => true,
        ]);

        // Inicializar progreso del estudiante
        $this->progresionService->inicializarProgreso($user);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('estudiante.dashboard')
            ->with('success', '¡Bienvenido a Educlass! Tu cuenta ha sido creada exitosamente.');
    }

    /**
     * Registrar docente
     */
    public function registerDocente(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'colegio_id' => 'required|exists:colegios,id',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terminos' => 'required|accepted',
        ], [
            'terminos.required' => 'Debes aceptar los términos y condiciones.',
        ]);

        $user = User::create([
            'tipo' => 'docente',
            'nombre' => $request->nombre,
            'colegio_id' => $request->colegio_id,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'activo' => true,
        ]);

        event(new Registered($user));

        // Enviar email de verificación
        // $user->sendEmailVerificationNotification();

        return redirect()->route('login')
            ->with('info', 'Tu cuenta ha sido creada. Por favor verifica tu correo electrónico para activarla.');
    }
}
