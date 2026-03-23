<?php

namespace App\Notifications;

use App\Models\PasswordRecoveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudRecuperacionEstudianteNotification extends Notification
{
    use Queueable;

    public function __construct(private PasswordRecoveryRequest $solicitud)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'solicitud_recuperacion_estudiante',
            'titulo' => 'Solicitud de recuperacion de contrasena',
            'mensaje' => 'El estudiante ' . $this->solicitud->nombre_estudiante . ' solicito recuperar su contrasena.',
            'url' => route('docente.recuperaciones.index'),
            'solicitud_id' => $this->solicitud->id,
        ];
    }
}

