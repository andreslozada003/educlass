<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('notifications')) {
            return redirect()->back()->with('error', 'La tabla de notificaciones no existe. Ejecuta las migraciones.');
        }

        $notificaciones = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function markAsRead(string $id)
    {
        if (!Schema::hasTable('notifications')) {
            return redirect()->back()->with('error', 'La tabla de notificaciones no existe.');
        }

        $notificacion = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notificacion->markAsRead();

        return redirect()->back()->with('success', 'Notificacion marcada como leida.');
    }

    public function markAllAsRead(Request $request)
    {
        if (!Schema::hasTable('notifications')) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Tabla de notificaciones no existe.'], 422)
                : redirect()->back()->with('error', 'La tabla de notificaciones no existe.');
        }

        Auth::user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Todas las notificaciones fueron marcadas como leidas.');
    }

    public function open(string $id)
    {
        if (!Schema::hasTable('notifications')) {
            return redirect()->route('notificaciones.index')
                ->with('error', 'La tabla de notificaciones no existe.');
        }

        $notificacion = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notificacion->markAsRead();

        $url = data_get($notificacion->data, 'url', route('notificaciones.index'));

        return redirect($url);
    }
}
