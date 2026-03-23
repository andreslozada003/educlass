<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'remember' => 'boolean',
        ]);

        // Rate limiting
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Demasiados intentos. Por favor intenta en ' . RateLimiter::availableIn($key) . ' segundos.'],
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($key);

            $user = Auth::user();
            $user->ultimo_acceso = now();
            $user->save();

            // Redireccionar según rol
            if ($user->esEstudiante()) {
                return redirect()->intended(route('estudiante.dashboard'))
                    ->with('show_manual', true);
            } elseif ($user->esDocente() || $user->esAdmin()) {
                return redirect()->intended(route('docente.dashboard'))
                    ->with('show_manual', true);
            }

            return redirect()->intended('/')
                ->with('show_manual', true);
        }

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas no coinciden con nuestros registros.'],
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
