<?php

namespace App\Notifications;

use App\Models\Tema;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoTemaDisponibleNotification extends Notification
{
    use Queueable;

    public function __construct(private Tema $tema)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'nuevo_tema',
            'titulo' => 'Nuevo tema disponible',
            'mensaje' => "Ya puedes estudiar: {$this->tema->titulo}",
            'url' => route('estudiante.temas.show', $this->tema->slug),
            'asignatura' => optional($this->tema->asignatura)->nombre,
            'tema_id' => $this->tema->id,
            'tema_titulo' => $this->tema->titulo,
        ];
    }
}

