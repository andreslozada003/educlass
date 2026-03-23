<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\PasswordRecoveryRequest;
use App\Notifications\MensajeDocenteNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordRecoveryRequestController extends Controller
{
    public function index()
    {
        $pendientes = PasswordRecoveryRequest::with('estudiante')
            ->where('estado', 'pendiente')
            ->latest()
            ->get();

        $historial = PasswordRecoveryRequest::with(['estudiante', 'docente'])
            ->whereIn('estado', ['atendida', 'rechazada'])
            ->latest()
            ->paginate(20);

        return view('docente.recuperaciones.index', compact('pendientes', 'historial'));
    }

    public function responder(Request $request, $id)
    {
        $solicitud = PasswordRecoveryRequest::with('estudiante')->findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            return redirect()->back()->with('warning', 'Esta solicitud ya fue atendida.');
        }

        if (!$solicitud->estudiante) {
            return redirect()->back()->with('error', 'No se encontro el estudiante asociado a la solicitud.');
        }

        $request->validate([
            'nueva_password' => 'nullable|string|min:8|max:64',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        $passwordTemporal = $request->filled('nueva_password')
            ? $request->nueva_password
            : Str::random(10);

        $estudiante = $solicitud->estudiante;
        $estudiante->password = Hash::make($passwordTemporal);
        $estudiante->save();

        $mensajeDocente = $request->filled('mensaje')
            ? trim($request->mensaje)
            : 'Tu nueva clave temporal es: ' . $passwordTemporal;

        $solicitud->update([
            'docente_id' => Auth::id(),
            'estado' => 'atendida',
            'mensaje_docente' => $mensajeDocente,
            'respondido_en' => now(),
        ]);

        $estudiante->notify(new MensajeDocenteNotification(
            Auth::user(),
            $mensajeDocente,
            'info'
        ));

        $whatsAppEnviado = false;
        $whatsAppService = app(WhatsAppService::class);
        if ($estudiante->telefono && $whatsAppService->enabled()) {
            $whatsAppEnviado = $whatsAppService->sendText(
                $estudiante->telefono,
                'Educlass: ' . $mensajeDocente
            );
        }

        $mensajeFinal = 'Solicitud atendida. Clave temporal enviada: ' . $passwordTemporal;
        if ($whatsAppService->enabled()) {
            $mensajeFinal .= $whatsAppEnviado
                ? ' | WhatsApp enviado.'
                : ' | No se pudo enviar por WhatsApp (revisa numero/configuracion).';
        } else {
            $mensajeFinal .= ' | WhatsApp desactivado en configuracion.';
        }

        return redirect()->back()->with('success', $mensajeFinal);
    }
}
