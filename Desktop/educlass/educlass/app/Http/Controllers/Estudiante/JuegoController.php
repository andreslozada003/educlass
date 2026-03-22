<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Juego;
use App\Models\IntentosJuego;
use App\Notifications\ResultadoJuegoNotification;
use App\Services\ProgresionService;
use App\Services\GamificacionService;
use App\Services\JuegoEngineService;
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

        // Verificar si puede jugar
        $puedeJugar = $this->progresionService->puedeRealizarJuego($estudiante, $juego);

        if (!$puedeJugar['puede']) {
            return redirect()->route('estudiante.temas.show', $juego->tema->slug)
                ->with('error', $puedeJugar['razon']);
        }

        // Obtener intentos restantes
        $intentosRestantes = $juego->intentosRestantes($estudiante->id);

        // Generar datos específicos según tipo de juego
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
     * Generar datos del juego según tipo
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

        // Validar respuestas
        $validacion = $this->juegoEngineService->validarRespuestas($juego, $request->respuestas);

        // Registrar intento
        $intento = $this->gamificacionService->registrarIntento(
            $estudiante,
            $juego,
            $request->respuestas,
            $request->duracion_segundos,
            true
        );

        $estudiante->notify(new ResultadoJuegoNotification($intento));

        // Verificar logros
        $logrosObtenidos = $this->gamificacionService->verificarLogros($estudiante, 'juego_completado', [
            'juego' => $juego,
            'intento' => $intento,
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

    $intentos = IntentosJuego::where('estudiante_id', $estudiante->id)
        ->with('juego') 
        ->orderBy('fecha_intento', 'desc')
        ->paginate(10); // 10 por página

    return view('estudiante.juegos.historial', compact('intentos'));
}

}

