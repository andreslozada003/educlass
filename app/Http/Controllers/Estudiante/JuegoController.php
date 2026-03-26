<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\IntentosJuego;
use App\Models\Juego;
use App\Notifications\ResultadoJuegoNotification;
use App\Services\GamificacionService;
use App\Services\JuegoEngineService;
use App\Services\ProgresionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JuegoController extends Controller
{
    protected $progresionService;
    protected $gamificacionService;
    protected $juegoEngineService;

    public function __construct(
        ProgresionService $progresionService,
        GamificacionService $gamificacionService,
        JuegoEngineService $juegoEngineService
    ) {
        $this->progresionService = $progresionService;
        $this->gamificacionService = $gamificacionService;
        $this->juegoEngineService = $juegoEngineService;
    }

    /**
     * Mostrar pantalla de juego
     */
    public function jugar($juegoId)
    {
        $estudiante = Auth::user();
        $juego = Juego::with(['tema.asignatura', 'preguntasActivas'])->findOrFail($juegoId);

        $puedeJugar = $this->progresionService->puedeRealizarJuego($estudiante, $juego);

        if (!$puedeJugar['puede']) {
            return redirect()->route('estudiante.temas.show', $juego->tema->slug)
                ->with('error', $puedeJugar['razon']);
        }

        $intentosRestantes = $juego->intentosRestantes($estudiante->id);
        $gameData = $this->generarGameData($juego);
        $preguntas = collect($gameData['preguntas'] ?? $juego->preguntasActivas);

        return view('estudiante.juegos.play', compact(
            'juego',
            'intentosRestantes',
            'gameData',
            'preguntas'
        ));
    }

    /**
     * Generar datos del juego segun tipo
     */
    private function generarGameData(Juego $juego): array
    {
        return match ($juego->tipo) {
            'memoria' => $this->juegoEngineService->generarMemoriaData($juego),
            'arrastrar' => $this->juegoEngineService->generarArrastrarData($juego),
            'ordenar' => $this->juegoEngineService->generarOrdenarData($juego),
            'sopa' => $this->juegoEngineService->generarSopaLetras($juego),
            'completar' => $this->juegoEngineService->generarCompletarData($juego),
            'clasificar' => $this->juegoEngineService->generarClasificacionData($juego),
            default => ['preguntas' => $juego->preguntasActivas],
        };
    }

    /**
     * Procesar resultado del juego
     */
    public function guardarResultado(Request $request, $juegoId)
    {
        $estudiante = Auth::user();
        $juego = Juego::findOrFail($juegoId);

        $request->validate([
            'respuestas' => 'required|array',
            'duracion_segundos' => 'required|integer|min:0',
        ]);

        $validacion = $this->juegoEngineService->validarRespuestas($juego, $request->respuestas);

        $intento = $this->gamificacionService->registrarIntento(
            $estudiante,
            $juego,
            $request->respuestas,
            $request->duracion_segundos,
            true
        );

        $estudiante->notify(new ResultadoJuegoNotification($intento));

        $logrosObtenidos = $this->gamificacionService->verificarLogros($estudiante, 'juego_completado', [
            'juego' => $juego,
            'intento' => $intento,
            'racha_maxima' => $intento->racha_maxima ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'intento' => $intento,
            'resultados' => $validacion,
            'logros_obtenidos' => $logrosObtenidos,
            'redirect' => route('estudiante.juegos.resultado', $intento->id),
        ]);
    }

    /**
     * Mostrar resultado del juego
     */
    public function resultado($intentoId)
    {
        $estudiante = Auth::user();
        $intento = IntentosJuego::with(['juego.tema.asignatura'])
            ->where('estudiante_id', $estudiante->id)
            ->findOrFail($intentoId);

        $juego = $intento->juego;
        $mejorPuntaje = $juego->mejorPuntaje($estudiante->id);
        $esMejorPuntaje = $intento->puntaje_obtenido >= $mejorPuntaje;

        return view('estudiante.juegos.resultado', compact(
            'intento',
            'juego',
            'mejorPuntaje',
            'esMejorPuntaje'
        ));
    }

    /**
     * Mostrar historial de juegos
     */
    public function historial()
    {
        $estudiante = Auth::user();

        $consulta = IntentosJuego::where('estudiante_id', $estudiante->id)
            ->where('completado', true)
            ->with(['juego.tema.asignatura', 'juego.preguntasActivas'])
            ->orderByDesc('fecha_intento');

        $intentos = (clone $consulta)->paginate(10);
        $intentosTotales = (clone $consulta)->get();

        $totalRespuestas = $intentosTotales->sum(function ($intento) {
            return $intento->total_respuestas;
        });

        $totalCorrectas = $intentosTotales->sum(function ($intento) {
            return $intento->respuestas_correctas;
        });

        $estadisticas = [
            'total_juegos' => $intentosTotales->count(),
            'puntos_totales' => $intentosTotales->sum('puntaje_obtenido'),
            'precision' => $totalRespuestas > 0
                ? round(($totalCorrectas / $totalRespuestas) * 100, 2)
                : 0,
            'mejor_racha' => $intentosTotales->max(function ($intento) {
                return $this->calcularRachaMaxima($intento);
            }) ?? 0,
        ];

        return view('estudiante.juegos.historial', compact('intentos', 'estadisticas'));
    }

    /**
     * Calcular la racha maxima de respuestas correctas en un intento
     */
    private function calcularRachaMaxima(IntentosJuego $intento): int
    {
        $respuestas = $intento->respuestas ?? [];
        $preguntas = optional($intento->juego)->preguntasActivas;

        if (empty($respuestas) || !$preguntas) {
            return 0;
        }

        $rachaActual = 0;
        $rachaMaxima = 0;

        foreach ($respuestas as $preguntaId => $respuesta) {
            $pregunta = $preguntas->firstWhere('id', (int) $preguntaId)
                ?? $preguntas->firstWhere('id', $preguntaId);

            if ($pregunta && $pregunta->verificarRespuesta($respuesta)) {
                $rachaActual++;
                $rachaMaxima = max($rachaMaxima, $rachaActual);
            } else {
                $rachaActual = 0;
            }
        }

        return $rachaMaxima;
    }
}
