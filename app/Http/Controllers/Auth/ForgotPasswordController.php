<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordRecoveryRequest;
use App\Models\User;
use App\Notifications\SolicitudRecuperacionEstudianteNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Mostrar formulario de solicitud de recuperacion
     */
    public function showLinkRequestForm(Request $request)
    {
        $solicitud = null;

        if ($request->filled('nombre_estudiante')) {
            $nombreNormalizado = mb_strtolower(trim($request->nombre_estudiante));
            $solicitud = PasswordRecoveryRequest::where('nombre_normalizado', $nombreNormalizado)
                ->latest()
                ->first();
        }

        return view('auth.passwords.email', compact('solicitud'));
    }

    /**
     * Enviar enlace de recuperacion (docente) o solicitud al docente (estudiante)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'tipo_recuperacion' => 'required|in:docente,estudiante',
        ]);

        if ($request->tipo_recuperacion === 'docente') {
            $request->validate([
                'telefono_docente' => 'required|string|min:8|max:25',
            ]);

            $normalizar = function (string $phone): string {
                $digits = preg_replace('/\D+/', '', $phone);
                if (strlen($digits) === 10) {
                    $digits = config('whatsapp.default_country_code', '57') . $digits;
                }
                return $digits;
            };

            $telefonoBuscado = $normalizar($request->telefono_docente);

            $usuario = User::whereIn('tipo', ['docente', 'admin'])
                ->get()
                ->first(function ($u) use ($telefonoBuscado, $normalizar) {
                    if (!$u->telefono) {
                        return false;
                    }
                    return $normalizar($u->telefono) === $telefonoBuscado;
                });

            if (!$usuario) {
                throw ValidationException::withMessages([
                    'telefono_docente' => ['El celular ingresado no corresponde a una cuenta docente.'],
                ]);
            }

            $key = 'password-reset-docente-cel.' . $request->ip() . '.' . $usuario->id;
            if (RateLimiter::tooManyAttempts($key, 3)) {
                throw ValidationException::withMessages([
                    'telefono_docente' => ['Demasiados intentos. Por favor intenta mas tarde.'],
                ]);
            }

            $whatsAppService = app(WhatsAppService::class);
            if (!$whatsAppService->enabled()) {
                throw ValidationException::withMessages([
                    'telefono_docente' => ['WhatsApp no esta configurado en el sistema.'],
                ]);
            }

            $passwordTemporal = Str::random(10);
            $usuario->password = Hash::make($passwordTemporal);
            $usuario->save();

            $ok = $whatsAppService->sendText(
                $usuario->telefono,
                'Educlass: tu clave temporal de acceso es: ' . $passwordTemporal
            );

            RateLimiter::hit($key, 3600);

            if (!$ok) {
                throw ValidationException::withMessages([
                    'telefono_docente' => ['No se pudo enviar el mensaje por WhatsApp. Verifica numero o configuracion.'],
                ]);
            }

            return back()->with('status', 'Se envio una clave temporal al celular del docente por WhatsApp.');
        }

        $request->validate([
            'nombre_estudiante' => 'required|string|min:3|max:150',
        ]);

        $nombreIngresado = trim($request->nombre_estudiante);
        $nombreNormalizado = mb_strtolower($nombreIngresado);

        $estudiantes = User::where('tipo', 'estudiante')
            ->whereRaw('LOWER(nombre) = ?', [$nombreNormalizado])
            ->get();

        if ($estudiantes->isEmpty()) {
            throw ValidationException::withMessages([
                'nombre_estudiante' => ['No se encontro un estudiante con ese nombre.'],
            ]);
        }

        if ($estudiantes->count() > 1) {
            throw ValidationException::withMessages([
                'nombre_estudiante' => ['Hay varios estudiantes con ese nombre. Usa el nombre completo registrado.'],
            ]);
        }

        $estudiante = $estudiantes->first();

        $key = 'password-reset-estudiante.' . $request->ip() . '.' . $estudiante->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'nombre_estudiante' => ['Ya realizaste varias solicitudes. Intenta mas tarde.'],
            ]);
        }

        $solicitud = PasswordRecoveryRequest::create([
            'estudiante_id' => $estudiante->id,
            'nombre_estudiante' => $estudiante->nombre,
            'nombre_normalizado' => $nombreNormalizado,
            'estado' => 'pendiente',
            'solicitado_en' => now(),
        ]);

        $docentes = User::where('tipo', 'docente')
            ->where('activo', true)
            ->when($estudiante->colegio_id, function ($query) use ($estudiante) {
                $query->where('colegio_id', $estudiante->colegio_id);
            })
            ->get();

        if ($docentes->isEmpty()) {
            $docentes = User::where('tipo', 'docente')->where('activo', true)->get();
        }

        foreach ($docentes as $docente) {
            $docente->notify(new SolicitudRecuperacionEstudianteNotification($solicitud));
        }

        RateLimiter::hit($key, 3600);

        return redirect()->route('password.request', ['nombre_estudiante' => $estudiante->nombre])
            ->with('status', 'Tu solicitud fue enviada al docente. Revisa aqui mismo si ya te respondieron con tu clave temporal.');
    }
}
