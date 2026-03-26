<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\ResultadosEvaluacion;
use App\Notifications\ResultadoEvaluacionNotification;
use App\Services\ProgresionService;
use App\Services\GamificacionService;
use App\Services\CalificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluacionController extends Controller
{
    protected $progresionService;
    protected $gamificacionService;
    protected $calificacionService;

    public function __construct(
        ProgresionService $progresionService,
        GamificacionService $gamificacionService,
        CalificacionService $calificacionService
    ) {
        $this->progresionService = $progresionService;
        $this->gamificacionService = $gamificacionService;
        $this->calificacionService = $calificacionService;
    }

    /**
     * Mostrar listado de evaluaciones (pendientes y completadas)
     * NUEVO MÉTODO para index.blade.php
     */
    public function index(Request $request)
    {
        $estudiante = Auth::user();
        
        // Obtener evaluaciones pendientes (que puede realizar)
        $evaluacionesPendientes = Evaluacion::with(['tema.asignatura', 'preguntas'])
            ->whereHas('tema', function($query) use ($estudiante) {
                $query->where('activo', true);
            })
            ->whereDoesntHave('resultados', function($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id)
                      ->where('aprobado', true);
            })
            ->get()
            ->filter(function($evaluacion) use ($estudiante) {
                $puede = $this->progresionService->puedeRealizarEvaluacion($estudiante, $evaluacion);
                return $puede['puede'];
            });

        // Obtener resultados completados
        $resultados = ResultadosEvaluacion::with(['evaluacion.tema.asignatura'])
            ->where('estudiante_id', $estudiante->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calcular estadísticas
        $totalEvaluaciones = Evaluacion::count();
        $aprobadas = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', true)
            ->count();
        $reprobadas = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('aprobado', false)
            ->count();
        
        $promedio = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->avg('puntaje_obtenido') ?? 0;

        $estadisticas = [
            'total_evaluaciones' => $totalEvaluaciones,
            'aprobadas' => $aprobadas,
            'reprobadas' => $reprobadas,
            'promedio' => $promedio,
        ];

        return view('estudiante.evaluaciones.index', compact(
            'evaluacionesPendientes',
            'resultados',
            'estadisticas'
        ));
    }

    /**
     * Mostrar evaluación para realizar
     * RENOMBRADO: realizar() → take()
     * Usa Route Model Binding
     */
    public function take(Evaluacion $evaluacion)
    {
        $estudiante = Auth::user();
        
        // Cargar relaciones necesarias
        $evaluacion->load(['tema.asignatura', 'preguntas']);

        // Verificar si puede realizar la evaluación
        $puedeRealizar = $this->progresionService->puedeRealizarEvaluacion($estudiante, $evaluacion);

        if (!$puedeRealizar['puede']) {
            return redirect()->route('estudiante.temas.show', $evaluacion->tema->slug)
                ->with('error', $puedeRealizar['razon']);
        }

        // Obtener intentos restantes
        $intentosRestantes = $evaluacion->intentosRestantes($estudiante->id);

        // Obtener preguntas ordenadas aleatoriamente si aplica
        $preguntas = $evaluacion->preguntas;

        return view('estudiante.evaluaciones.take', compact(
            'evaluacion',
            'intentosRestantes',
            'preguntas'
        ));
    }

    /**
     * Procesar y guardar resultado de evaluación
     * RENOMBRADO: guardarResultado() → submit()
     * Usa Route Model Binding
     */
    public function submit(Request $request, Evaluacion $evaluacion)
    {
        $estudiante = Auth::user();
        
        // Validar datos - CORREGIDO: tiempo_empleado (en segundos, del JS)
        $request->validate([
            'respuestas' => 'required|array',
            'puntuacion' => 'required|numeric|min:0|max:100',
            'tiempo_empleado' => 'required|integer|min:0', // En segundos, convertir a minutos
        ]);

        // Convertir tiempo de segundos a minutos
        $tiempoEmpleadoMinutos = round($request->tiempo_empleado / 60);

        // Calcular puntaje basado en respuestas
        $puntajeObtenido = 0;
        $puntajeTotal = 0;
        $respuestasDetalle = [];

        $preguntas = $evaluacion->preguntas;
        
        foreach ($preguntas as $pregunta) {
            $puntajeTotal += $pregunta->puntaje ?? 1;
            
            // Verificar si respondió esta pregunta
            if (isset($request->respuestas[$pregunta->id])) {
                $respuestaEstudiante = $request->respuestas[$pregunta->id];
                $esCorrecta = $this->verificarRespuesta($pregunta, $respuestaEstudiante);
                
                if ($esCorrecta) {
                    $puntajeObtenido += $pregunta->puntaje ?? 1;
                }

                $respuestasDetalle[$pregunta->id] = [
                    'respuesta' => $respuestaEstudiante,
                    'correcta' => $esCorrecta,
                ];
            }
        }

        // Si no hay puntaje total definido, usar 100
        if ($puntajeTotal == 0) {
            $puntajeTotal = 100;
        }

        // Calcular porcentaje
        $porcentaje = ($puntajeObtenido / $puntajeTotal) * 100;
        
        // Determinar si aprobó (usar puntuacion del request o calcular)
        $puntuacionFinal = $request->puntuacion ?? $porcentaje;
        $aprobado = $puntuacionFinal >= $evaluacion->umbral_aprobacion;

        // Obtener número de intento
        $intentoNumero = ResultadosEvaluacion::where('estudiante_id', $estudiante->id)
            ->where('evaluacion_id', $evaluacion->id)
            ->count() + 1;

        // Guardar resultado
        $resultado = ResultadosEvaluacion::create([
            'estudiante_id' => $estudiante->id,
            'evaluacion_id' => $evaluacion->id,
            'puntaje_obtenido' => $puntajeObtenido,
            'puntaje_total' => $puntajeTotal,
            'respuestas' => $respuestasDetalle,
            'tiempo_empleado_minutos' => $tiempoEmpleadoMinutos,
            'aprobado' => $aprobado,
            'intento_numero' => $intentoNumero,
            'fecha_realizacion' => now(),
            'ip_address' => $request->ip(),
        ]);

        $estudiante->notify(new ResultadoEvaluacionNotification($resultado));

        // Calcular intentos restantes
        $intentosRestantes = max(0, $evaluacion->intentos_permitidos - $intentoNumero);
        $logrosObtenidos = [];

        // Si aprobó, actualizar gamificación y calificaciones
        if ($aprobado) {
            $this->gamificacionService->actualizarRankingEvaluaciones($estudiante, $evaluacion->tema->asignatura_id);
            $this->gamificacionService->actualizarRankingGeneral($estudiante);
            $logrosObtenidos = $this->gamificacionService->verificarLogros($estudiante, 'evaluacion_aprobada', [
                'evaluacion' => $evaluacion,
                'resultado' => $resultado,
            ]);

            // Calcular calificación del período
            $this->calificacionService->calcularCalificacionPeriodo(
                $estudiante->id,
                $evaluacion->tema->asignatura_id,
                $evaluacion->tema->periodo_academico ?? 'default'
            );

            // Al aprobar evaluación (y ya habiendo cumplido lectura+juego), completar tema.
            $this->progresionService->completarTema($estudiante, $evaluacion->tema);
        }

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
            'intentos_restantes' => $intentosRestantes,
            'logros_obtenidos' => $logrosObtenidos,
            'redirect' => route('estudiante.evaluaciones.resultado', $resultado->id),
        ]);
    }

    /**
     * Verificar si una respuesta es correcta
     * Método auxiliar privado
     */
    private function verificarRespuesta($pregunta, $respuesta)
    {
        $tipo = $pregunta->tipo ?? 'multiple';
        $respuestaCorrecta = $pregunta->respuesta_correcta;

        if (is_string($respuestaCorrecta)) {
            $raw = trim($respuestaCorrecta);
            if (
                (str_starts_with($raw, '[') && str_ends_with($raw, ']')) ||
                (str_starts_with($raw, '{') && str_ends_with($raw, '}'))
            ) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $respuestaCorrecta = $decoded;
                }
            }
        }

        $normalizar = function ($valor) {
            if (is_bool($valor)) {
                return $valor ? '1' : '0';
            }
            if (is_numeric($valor)) {
                return (string) $valor;
            }
            return mb_strtolower(trim((string) $valor));
        };

        switch ($tipo) {
            case 'vf':
            case 'multiple':
            case 'corta':
                if (is_array($respuestaCorrecta)) {
                    $permitidas = array_map($normalizar, $respuestaCorrecta);
                    return in_array($normalizar($respuesta), $permitidas, true);
                }
                return $normalizar($respuesta) === $normalizar($respuestaCorrecta);

            case 'emparejamiento':
                if (is_string($respuesta) && is_array($respuestaCorrecta)) {
                    $decoded = json_decode($respuesta, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $respuesta = $decoded;
                    }
                }
                if (is_string($respuestaCorrecta) && is_array($respuesta)) {
                    $decoded = json_decode($respuestaCorrecta, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $respuestaCorrecta = $decoded;
                    }
                }

                if (!is_array($respuesta) || !is_array($respuestaCorrecta)) {
                    return false;
                }

                $normalizadaEstudiante = [];
                foreach ($respuesta as $k => $v) {
                    $normalizadaEstudiante[$normalizar($k)] = $normalizar($v);
                }

                $normalizadaCorrecta = [];
                foreach ($respuestaCorrecta as $k => $v) {
                    $normalizadaCorrecta[$normalizar($k)] = $normalizar($v);
                }

                ksort($normalizadaEstudiante);
                ksort($normalizadaCorrecta);

                return $normalizadaEstudiante === $normalizadaCorrecta;

            default:
                if (is_array($respuestaCorrecta)) {
                    $permitidas = array_map($normalizar, $respuestaCorrecta);
                    return in_array($normalizar($respuesta), $permitidas, true);
                }
                return $normalizar($respuesta) === $normalizar($respuestaCorrecta);
        }
    }

    /**
     * Mostrar resultado de evaluación específica
     * CORREGIDO: Usa Route Model Binding
     */
    public function resultado(ResultadosEvaluacion $resultado)
    {
        $estudiante = Auth::user();

        // Verificar que el resultado pertenezca al estudiante
        if ($resultado->estudiante_id !== $estudiante->id) {
            abort(403, 'No autorizado');
        }

        $resultado->load(['evaluacion.tema.asignatura', 'evaluacion.preguntas']);

        $evaluacion = $resultado->evaluacion;

        // Si aprobó, verificar si puede desbloquear siguiente tema
        $siguienteTema = null;
        if ($resultado->aprobado) {
            $siguienteTema = $this->progresionService->desbloquearSiguienteTema($estudiante, $evaluacion->tema);
        }

        return view('estudiante.evaluaciones.resultado', compact(
            'resultado',
            'evaluacion',
            'siguienteTema'
        ));
    }

    /**
     * Mostrar historial de evaluaciones
     * CORREGIDO: Redirige a index con tab=completadas
     */
    public function historial()
    {
        return redirect()->route('estudiante.evaluaciones.index', ['tab' => 'completadas']);
    }
}
