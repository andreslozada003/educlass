<?php

namespace App\Notifications;

use App\Models\IntentosJuego;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultadoJuegoNotification extends Notification
{
    use Queueable;

    public function __construct(private IntentosJuego $intento)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $juego = $this->intento->juego;

        return [
            'tipo' => 'resultado_juego',
            'titulo' => 'Juego completado',
            'mensaje' => ($juego ? $juego->titulo . ': ' : '') . 'Revisa tu resultado.',
            'url' => route('estudiante.juegos.resultado', $this->intento->id),
            'intento_id' => $this->intento->id,
            'puntaje' => $this->intento->puntaje_obtenido,
            'puntaje_maximo' => $this->intento->puntaje_maximo,
        ];
    }
}

