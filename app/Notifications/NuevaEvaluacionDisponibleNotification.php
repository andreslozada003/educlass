<?php

namespace App\Notifications;

use App\Models\Evaluacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevaEvaluacionDisponibleNotification extends Notification
{
    use Queueable;

    public function __construct(private Evaluacion $evaluacion)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'nueva_evaluacion',
            'titulo' => 'Nueva evaluacion disponible',
            'mensaje' => "Nueva evaluacion: {$this->evaluacion->titulo}",
            'url' => route('estudiante.evaluaciones.take', $this->evaluacion->id),
            'tema' => optional($this->evaluacion->tema)->titulo,
            'evaluacion_id' => $this->evaluacion->id,
            'evaluacion_titulo' => $this->evaluacion->titulo,
        ];
    }
}

