<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Juego;
use App\Models\PreguntasJuego;
use App\Models\Tema;
use App\Models\User;
use App\Notifications\NuevoJuegoDisponibleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JuegoManagementController extends Controller
{
    /**
     * Listar juegos
     */
    public function index(Request $request)
    {
        $query = Juego::with('tema.asignatura')
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            });

        if ($request->filled('asignatura')) {
            $query->whereHas('tema', function ($q) use ($request) {
                $q->where('asignatura_id', $request->asignatura);
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $juegos = $query->orderByDesc('created_at')->paginate(15);
        $tipos = config('juegos.tipos');

        return view('docente.juegos.index', compact('juegos', 'tipos'));
    }

    /**
     * Mostrar formulario de creacion
     */
    public function create(Request $request)
    {
        $temas = Tema::with('asignatura')
            ->where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = config('juegos.tipos');
        $temaPreseleccionado = $request->tema_id;
        $temaSeleccionado = $temas->firstWhere('id', (int) $temaPreseleccionado);
        $recomendacionesAsignatura = $this->obtenerRecomendacionesPorAsignatura();
        $tipoPredeterminado = $this->obtenerTipoPredeterminadoPorTema($temaSeleccionado, $recomendacionesAsignatura);

        return view('docente.juegos.create', compact(
            'temas',
            'tipos',
            'temaPreseleccionado',
            'recomendacionesAsignatura',
            'tipoPredeterminado'
        ));
    }

    /**
     * Guardar juego
     */
    public function store(Request $request)
    {
        $tiposDisponibles = implode(',', array_keys(config('juegos.tipos', [])));

        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'tipo' => "required|in:{$tiposDisponibles}",
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'dificultad' => 'required|integer|between:1,4',
            'intentos_maximos' => 'required|integer|min:1|max:10',
            'puntaje_base' => 'required|integer|min:10',
            'tiempo_limite_segundos' => 'nullable|integer|min:30',
            'operacion_principal' => 'nullable|in:suma,resta,multiplicacion,division,mixto',
            'objetivo_aventura' => 'nullable|in:puente,cofre,obstaculo',
            'recompensa_principal' => 'nullable|in:monedas,energia,ambas',
            'monedas_por_acierto' => 'nullable|integer|min:0|max:500',
            'energia_por_acierto' => 'nullable|integer|min:0|max:100',
        ]);

        Tema::where('id', $request->tema_id)
            ->where('docente_creador_id', Auth::id())
            ->firstOrFail();

        $juego = Juego::create([
            'tema_id' => $request->tema_id,
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'configuracion' => $this->buildConfiguracion($request, $request->tipo),
            'dificultad' => $request->dificultad,
            'intentos_maximos' => $request->intentos_maximos,
            'puntaje_base' => $request->puntaje_base,
            'tiempo_limite_segundos' => $request->tiempo_limite_segundos,
            'activo' => $request->boolean('activo', false),
        ]);

        if ($juego->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($juego) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoJuegoDisponibleNotification($juego));
                    }
                });
        }

        return redirect()->route('docente.juegos.preguntas', $juego->id)
            ->with('success', 'Juego creado. Ahora agrega las preguntas.');
    }

    /**
     * Obtener configuracion base por tipo
     */
    private function getConfiguracionPorTipo(string $tipo): array
    {
        return config("juegos.tipos.{$tipo}.configuracion", []);
    }

    /**
     * Construir configuracion final del juego
     */
    private function buildConfiguracion(Request $request, string $tipo, array $configuracionActual = []): array
    {
        $configuracionBase = array_merge(
            $this->getConfiguracionPorTipo($tipo),
            $configuracionActual
        );

        if ($tipo !== 'matematica_aventura') {
            return $configuracionBase;
        }

        return array_merge($configuracionBase, [
            'operacion_principal' => $request->input('operacion_principal', $configuracionBase['operacion_principal'] ?? 'mixto'),
            'objetivo_aventura' => $request->input('objetivo_aventura', $configuracionBase['objetivo_aventura'] ?? 'puente'),
            'recompensa_principal' => $request->input('recompensa_principal', $configuracionBase['recompensa_principal'] ?? 'monedas'),
            'monedas_por_acierto' => (int) $request->input('monedas_por_acierto', $configuracionBase['monedas_por_acierto'] ?? 15),
            'energia_por_acierto' => (int) $request->input('energia_por_acierto', $configuracionBase['energia_por_acierto'] ?? 10),
        ]);
    }

    /**
     * Obtener recomendaciones pedagogicas por asignatura.
     */
    private function obtenerRecomendacionesPorAsignatura(): array
    {
        return [
            'lenguaje' => [
                'titulo' => 'Español',
                'categorias' => [
                    'Quiz',
                    'Memoria / emparejar',
                    'Rompecabezas',
                    'Carrera o tablero',
                ],
                'habilidades' => [
                    'lectura',
                    'escritura',
                    'ortografia',
                    'comprension lectora',
                    'formacion de oraciones',
                ],
                'tipo_disponible_recomendado' => 'quiz',
            ],
            'ciencias' => [
                'titulo' => 'Ciencias',
                'categorias' => [
                    'Aventura / misiones',
                    'Quiz',
                    'Escape room',
                    'Memoria / emparejar',
                ],
                'habilidades' => [
                    'clasificacion',
                    'observacion',
                    'conceptos del entorno',
                    'experimentacion',
                    'relaciones entre elementos',
                ],
                'tipo_disponible_recomendado' => 'quiz',
            ],
            'ingles' => [
                'titulo' => 'Inglés',
                'categorias' => [
                    'Memoria / emparejar',
                    'Bingo educativo',
                    'Quiz',
                    'Aventura',
                ],
                'habilidades' => [
                    'vocabulario',
                    'pronunciacion',
                    'asociacion palabra-imagen',
                    'frases basicas',
                ],
                'tipo_disponible_recomendado' => 'memoria',
            ],
            'matematicas' => [
                'titulo' => 'Matemáticas',
                'categorias' => [
                    'Quiz',
                    'Carrera o tablero',
                    'Rompecabezas',
                    'Escape room',
                ],
                'habilidades' => [
                    'operaciones',
                    'logica',
                    'resolucion de problemas',
                    'calculo mental',
                ],
                'tipo_disponible_recomendado' => 'quiz',
            ],
        ];
    }

    /**
     * Obtener el tipo predeterminado segun la asignatura del tema.
     */
    private function obtenerTipoPredeterminadoPorTema(?Tema $tema, array $recomendacionesAsignatura): string
    {
        $slug = optional(optional($tema)->asignatura)->slug;

        if ($slug && isset($recomendacionesAsignatura[$slug]['tipo_disponible_recomendado'])) {
            return $recomendacionesAsignatura[$slug]['tipo_disponible_recomendado'];
        }

        return 'matematica_aventura';
    }

    /**
     * Mostrar juego
     */
    public function show($id)
    {
        $juego = Juego::with(['tema.asignatura', 'preguntas'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($id);

        return view('docente.juegos.show', compact('juego'));
    }

    /**
     * Mostrar formulario de edicion
     */
    public function edit($id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $temas = Tema::where('docente_creador_id', Auth::id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $tipos = config('juegos.tipos');

        return view('docente.juegos.edit', compact('juego', 'temas', 'tipos'));
    }

    /**
     * Actualizar juego
     */
    public function update(Request $request, $id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $estabaActivo = (bool) $juego->activo;

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'dificultad' => 'required|integer|between:1,4',
            'intentos_maximos' => 'required|integer|min:1|max:10',
            'puntaje_base' => 'required|integer|min:10',
            'tiempo_limite_segundos' => 'nullable|integer|min:30',
            'operacion_principal' => 'nullable|in:suma,resta,multiplicacion,division,mixto',
            'objetivo_aventura' => 'nullable|in:puente,cofre,obstaculo',
            'recompensa_principal' => 'nullable|in:monedas,energia,ambas',
            'monedas_por_acierto' => 'nullable|integer|min:0|max:500',
            'energia_por_acierto' => 'nullable|integer|min:0|max:100',
        ]);

        $juego->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'configuracion' => $this->buildConfiguracion($request, $juego->tipo, $juego->configuracion ?? []),
            'dificultad' => $request->dificultad,
            'intentos_maximos' => $request->intentos_maximos,
            'puntaje_base' => $request->puntaje_base,
            'tiempo_limite_segundos' => $request->tiempo_limite_segundos,
            'activo' => $request->boolean('activo', false),
        ]);

        if (!$estabaActivo && $juego->activo) {
            User::estudiantes()
                ->where('activo', true)
                ->chunkById(100, function ($estudiantes) use ($juego) {
                    foreach ($estudiantes as $estudiante) {
                        $estudiante->notify(new NuevoJuegoDisponibleNotification($juego));
                    }
                });
        }

        return redirect()->route('docente.juegos.index')
            ->with('success', 'Juego actualizado exitosamente.');
    }

    /**
     * Eliminar juego
     */
    public function destroy($id)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($id);

        $juego->delete();

        return redirect()->route('docente.juegos.index')
            ->with('success', 'Juego eliminado exitosamente.');
    }

    /**
     * Vista previa del juego
     */
    public function preview($id)
    {
        $juego = Juego::with(['tema.asignatura', 'preguntasActivas'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($id);

        return view('docente.juegos.preview', compact('juego'));
    }

    /**
     * Gestion de preguntas
     */
    public function preguntas($juegoId)
    {
        $juego = Juego::with(['preguntas', 'tema.asignatura'])
            ->whereHas('tema', function ($q) {
                $q->where('docente_creador_id', Auth::id());
            })
            ->findOrFail($juegoId);

        return view('docente.juegos.preguntas', compact('juego'));
    }

    /**
     * Agregar pregunta
     */
    public function agregarPregunta(Request $request, $juegoId)
    {
        $juego = Juego::whereHas('tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->findOrFail($juegoId);

        $this->validarPreguntaRequest($request, $juego);

        $orden = $juego->preguntas()->max('orden') + 1;

        PreguntasJuego::create($this->construirPreguntaPayload($request, $juego, $orden));

        return redirect()->back()->with('success', 'Pregunta agregada exitosamente.');
    }

    /**
     * Validar formulario segun el tipo de juego.
     */
    private function validarPreguntaRequest(Request $request, Juego $juego): void
    {
        $juego->loadMissing('tema.asignatura');

        switch ($juego->tipo) {
            case 'matematica_aventura':
                $request->validate([
                    'contexto_aventura' => 'required|string|max:255',
                    'numero_a' => 'required|numeric',
                    'operador' => 'required|in:+,-,x,/',
                    'numero_b' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($request) {
                            if ($request->input('operador') === '/' && (float) $value === 0.0) {
                                $fail('No puedes dividir entre cero.');
                            }
                        },
                    ],
                    'modo_respuesta' => 'required|in:opcion_multiple,respuesta_corta',
                    'distractores' => 'nullable|array',
                    'distractores.*' => 'nullable|numeric',
                    'puntaje' => 'required|integer|min:1',
                ]);

                if (
                    $request->input('modo_respuesta') === 'opcion_multiple' &&
                    count($this->filtrarValores($request->input('distractores', []))) < 2
                ) {
                    throw ValidationException::withMessages([
                        'distractores' => 'Agrega al menos dos distractores para la opcion multiple.',
                    ]);
                }
                break;

            case 'memoria':
                $request->validate([
                    'enunciado' => 'required|string|max:255',
                    'respuesta_correcta' => 'required|string|max:255',
                    'puntaje' => 'required|integer|min:1',
                ]);
                break;

            case 'arrastrar':
            case 'clasificar':
                $request->validate([
                    'consigna' => 'required|string|max:255',
                    'elementos_categoria' => 'required|array',
                    'elementos_categoria.*' => 'nullable|string|max:120',
                    'categorias_elemento' => 'required|array',
                    'categorias_elemento.*' => 'nullable|string|max:120',
                    'puntaje' => 'required|integer|min:1',
                ]);

                if (count($this->construirOpcionesCategoria($request)) === 0) {
                    throw ValidationException::withMessages([
                        'elementos_categoria' => 'Agrega al menos un elemento con su categoria.',
                    ]);
                }
                break;

            case 'ordenar':
                if ($this->esOrdenarIngles($juego)) {
                    $request->validate([
                        'elemento_ordenar' => 'required|string|max:255',
                        'respuesta_correcta' => 'nullable|string|max:255|required_without:imagen_pareja',
                        'imagen_pareja' => 'nullable|image|max:3072|required_without:respuesta_correcta',
                        'puntaje' => 'required|integer|min:1',
                    ]);
                } else {
                    $request->validate([
                        'elemento_ordenar' => 'required|string|max:255',
                        'posicion_correcta' => 'required|integer|min:1',
                        'puntaje' => 'required|integer|min:1',
                    ]);
                }
                break;

            case 'sopa':
            case 'completar':
                $request->validate([
                    'enunciado' => 'required|string|max:255',
                    'respuesta_correcta' => 'required|string|max:120',
                    'puntaje' => 'required|integer|min:1',
                ]);
                break;

            default:
                $request->validate([
                    'enunciado' => 'required|string',
                    'tipo' => 'required|in:opcion_multiple,verdadero_falso,emparejamiento,ordenamiento',
                    'opciones' => 'nullable|array',
                    'respuesta_correcta' => 'required',
                    'puntaje' => 'required|integer|min:1',
                ]);
                break;
        }
    }

    /**
     * Construir payload final para preguntas segun tipo de juego.
     */
    private function construirPreguntaPayload(Request $request, Juego $juego, int $orden): array
    {
        $juego->loadMissing('tema.asignatura');

        switch ($juego->tipo) {
            case 'matematica_aventura':
                $resultado = $this->calcularResultadoOperacion(
                    (float) $request->input('numero_a'),
                    $request->input('operador'),
                    (float) $request->input('numero_b')
                );

                $enunciado = trim($request->input('contexto_aventura'));
                $enunciado .= '. Resuelve: '
                    . $request->input('numero_a')
                    . ' ' . $request->input('operador') . ' '
                    . $request->input('numero_b')
                    . ' = ?';

                $opciones = null;
                if ($request->input('modo_respuesta') === 'opcion_multiple') {
                    $opciones = $this->filtrarValores($request->input('distractores', []));
                    $opciones[] = $resultado;
                    $opciones = array_values(array_unique($opciones));
                    shuffle($opciones);
                }

                return [
                    'juego_id' => $juego->id,
                    'tipo' => 'opcion_multiple',
                    'enunciado' => $enunciado,
                    'opciones' => $opciones,
                    'respuesta_correcta' => [$resultado],
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];

            case 'memoria':
                return [
                    'juego_id' => $juego->id,
                    'tipo' => 'emparejamiento',
                    'enunciado' => $request->input('enunciado'),
                    'opciones' => null,
                    'respuesta_correcta' => [$request->input('respuesta_correcta')],
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];

            case 'arrastrar':
            case 'clasificar':
                $opciones = $this->construirOpcionesCategoria($request);

                return [
                    'juego_id' => $juego->id,
                    'tipo' => 'emparejamiento',
                    'enunciado' => $request->input('consigna'),
                    'opciones' => $opciones,
                    'respuesta_correcta' => array_values(array_unique(array_column($opciones, 'categoria'))),
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];

            case 'ordenar':
                if ($this->esOrdenarIngles($juego)) {
                    $parejaVisual = $this->resolverParejaVisualOrdenarIngles($request);

                    return [
                        'juego_id' => $juego->id,
                        'tipo' => 'ordenamiento',
                        'enunciado' => $request->input('elemento_ordenar'),
                        'opciones' => null,
                        'respuesta_correcta' => [$parejaVisual],
                        'puntaje' => $request->input('puntaje'),
                        'orden' => $orden,
                        'activo' => true,
                    ];
                }

                return [
                    'juego_id' => $juego->id,
                    'tipo' => 'ordenamiento',
                    'enunciado' => $request->input('elemento_ordenar'),
                    'opciones' => null,
                    'respuesta_correcta' => ['orden' => (int) $request->input('posicion_correcta')],
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];

            case 'sopa':
            case 'completar':
                return [
                    'juego_id' => $juego->id,
                    'tipo' => 'opcion_multiple',
                    'enunciado' => $request->input('enunciado'),
                    'opciones' => null,
                    'respuesta_correcta' => [$request->input('respuesta_correcta')],
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];

            default:
                return [
                    'juego_id' => $juego->id,
                    'tipo' => $request->input('tipo'),
                    'enunciado' => $request->input('enunciado'),
                    'opciones' => $this->filtrarValores($request->input('opciones', [])),
                    'respuesta_correcta' => is_array($request->input('respuesta_correcta'))
                        ? $request->input('respuesta_correcta')
                        : [$request->input('respuesta_correcta')],
                    'puntaje' => $request->input('puntaje'),
                    'orden' => $orden,
                    'activo' => true,
                ];
        }
    }

    /**
     * Determinar si el juego ordenar es la variante especial de ingles.
     */
    private function esOrdenarIngles(Juego $juego): bool
    {
        return $juego->tipo === 'ordenar'
            && optional(optional($juego->tema)->asignatura)->slug === 'ingles';
    }

    /**
     * Resolver la pareja visual del juego ordenar en ingles.
     */
    private function resolverParejaVisualOrdenarIngles(Request $request): string
    {
        if ($request->hasFile('imagen_pareja')) {
            $ruta = $request->file('imagen_pareja')->store('preguntas/ingles-match', 'public');

            return 'storage/' . $ruta;
        }

        return trim((string) $request->input('respuesta_correcta'));
    }

    /**
     * Construir opciones estructuradas para arrastrar y clasificar.
     */
    private function construirOpcionesCategoria(Request $request): array
    {
        $elementos = $request->input('elementos_categoria', []);
        $categorias = $request->input('categorias_elemento', []);
        $opciones = [];

        foreach ($elementos as $index => $elemento) {
            $elemento = trim((string) $elemento);
            $categoria = trim((string) ($categorias[$index] ?? ''));

            if ($elemento === '' || $categoria === '') {
                continue;
            }

            $opciones[] = [
                'elemento' => $elemento,
                'categoria' => $categoria,
            ];
        }

        return $opciones;
    }

    /**
     * Filtrar valores vacios.
     */
    private function filtrarValores(array $valores): array
    {
        return array_values(array_filter(array_map(function ($valor) {
            return is_string($valor) ? trim($valor) : $valor;
        }, $valores), function ($valor) {
            return $valor !== null && $valor !== '';
        }));
    }

    /**
     * Calcular resultado de operacion matematica.
     */
    private function calcularResultadoOperacion(float $a, string $operador, float $b): string
    {
        $resultado = match ($operador) {
            '+' => $a + $b,
            '-' => $a - $b,
            'x' => $a * $b,
            '/' => $b !== 0.0 ? $a / $b : 0,
            default => $a + $b,
        };

        if (floor($resultado) == $resultado) {
            return (string) (int) $resultado;
        }

        return (string) round($resultado, 2);
    }

    /**
     * Eliminar pregunta
     */
    public function eliminarPregunta($juegoId, $preguntaId)
    {
        $pregunta = PreguntasJuego::whereHas('juego.tema', function ($q) {
            $q->where('docente_creador_id', Auth::id());
        })->where('id', $preguntaId)->firstOrFail();

        $pregunta->delete();

        return redirect()->back()->with('success', 'Pregunta eliminada exitosamente.');
    }
}
