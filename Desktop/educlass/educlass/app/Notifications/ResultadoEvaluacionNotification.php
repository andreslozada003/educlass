<?php

namespace App\Notifications;

use App\Models\ResultadosEvaluacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultadoEvaluacionNotification extends Notification
{
    use Queueable;

    public function __construct(private ResultadosEvaluacion $resultado)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $evaluacion = $this->resultado->evaluacion;
        $aprobado = (bool) $this->resultado->aprobado;

        return [
            'tipo' => 'resultado_evaluacion',
            'titulo' => $aprobado ? 'Evaluacion aprobada' : 'Evaluacion no aprobada',
            'mensaje' => ($evaluacion ? $evaluacion->titulo . ': ' : '') .
                ($aprobado ? 'Aprobaste tu evaluacion.' : 'Puedes intentarlo de nuevo.'),
            'url' => route('estudiante.evaluaciones.resultado', $this->resultado->id),
            'aprobado' => $aprobado,
            'resultado_id' => $this->resultado->id,
            'puntaje' => $this->resultado->puntaje_obtenido,
            'puntaje_total' => $this->resultado->puntaje_total,
        ];
    }
}

