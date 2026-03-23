<?php

namespace App\Notifications;

use App\Models\Juego;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoJuegoDisponibleNotification extends Notification
{
    use Queueable;

    public function __construct(private Juego $juego)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'nuevo_juego',
            'titulo' => 'Nuevo juego disponible',
            'mensaje' => "Nuevo juego: {$this->juego->titulo}",
            'url' => route('estudiante.juegos.jugar', $this->juego->id),
            'tema' => optional($this->juego->tema)->titulo,
            'juego_id' => $this->juego->id,
            'juego_titulo' => $this->juego->titulo,
        ];
    }
}

