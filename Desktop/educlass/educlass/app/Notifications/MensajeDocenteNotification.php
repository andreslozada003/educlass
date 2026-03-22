<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MensajeDocenteNotification extends Notification
{
    use Queueable;

    public function __construct(
        private User $docente,
        private string $mensaje,
        private string $tipo = 'info',
        private ?string $url = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'mensaje_docente',
            'nivel' => $this->tipo,
            'titulo' => 'Mensaje de tu docente',
            'mensaje' => $this->mensaje,
            'docente_id' => $this->docente->id,
            'docente_nombre' => $this->docente->nombre,
            'url' => $this->url ?: route('notificaciones.index'),
        ];
    }
}

