@extends('layouts.app')

@section('title', $juego->titulo)

@php
    $config = $juego->configuracion ?? [];
    $esMatematicaAventura = $juego->tipo === 'matematica_aventura';
    $esMemoria = $juego->tipo === 'memoria';
    $esSopa = $juego->tipo === 'sopa';
    $esCompletarIngles = $juego->tipo === 'completar' && optional($juego->tema->asignatura)->slug === 'ingles';
    $esOrdenarIngles = $juego->tipo === 'ordenar' && optional($juego->tema->asignatura)->slug === 'ingles';
    $esSopaMatematica = $esSopa && optional($juego->tema->asignatura)->slug === 'matematicas';
    $objetivo = $config['objetivo_aventura'] ?? 'puente';
    $operacion = $config['operacion_principal'] ?? 'mixto';
    $recompensa = $config['recompensa_principal'] ?? 'monedas';
    $monedas = $config['monedas_por_acierto'] ?? 15;
    $energia = $config['energia_por_acierto'] ?? 10;
    $previewSopaGrid = collect($gameData['grid'] ?? [])->take(6);

    $goalLabels = [
        'puente' => 'Cruza los puentes numericos',
        'cofre' => 'Abre los cofres del tesoro',
        'obstaculo' => 'Vence los obstaculos del camino',
    ];

    $operationLabels = [
        'suma' => 'Sumas',
        'resta' => 'Restas',
        'multiplicacion' => 'Multiplicaciones',
        'division' => 'Divisiones',
        'mixto' => 'Mixto',
    ];
@endphp

@section('content')
<div class="container mx-auto px-4 py-8" x-data="gameEngine()" x-init="initGame()">
    <div class="mb-6">
        <div class="flex items-center text-gray-600">
            <a href="{{ route('estudiante.asignaturas.index') }}" class="hover:text-blue-600">Asignaturas</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('estudiante.asignaturas.show', $juego->tema->asignatura) }}" class="hover:text-blue-600">{{ $juego->tema->asignatura->nombre }}</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-800 font-medium">{{ $juego->titulo }}</span>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center text-2xl">{{ $juego->tipo_icono }}</div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $juego->titulo }}</h1>
                    <p class="text-gray-600">{{ $juego->tema->titulo }}</p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <div class="text-sm text-gray-500">Puntaje base</div>
                <div class="text-2xl font-bold text-purple-600">{{ $juego->puntaje_base }}</div>
            </div>
        </div>
    </div>

    @if($esMatematicaAventura)
        <div class="mb-6 overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-500 via-cyan-500 to-slate-900 text-white shadow-xl shadow-cyan-100">
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-white/70">Mision matematica</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold">{{ $goalLabels[$objetivo] ?? 'Cruza los puentes numericos' }}</h2>
                        <p class="mt-3 max-w-2xl text-white/85">
                            Resuelve operaciones de {{ strtolower($operationLabels[$operacion] ?? 'Mixto') }} para seguir avanzando y gana recompensas en cada acierto.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm min-w-[220px]">
                        <div class="rounded-2xl bg-white/10 px-4 py-4">
                            <p class="uppercase tracking-[0.18em] text-white/60">Monedas</p>
                            <p class="mt-2 text-2xl font-semibold">+{{ $monedas }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-4">
                            <p class="uppercase tracking-[0.18em] text-white/60">Energia</p>
                            <p class="mt-2 text-2xl font-semibold">+{{ $energia }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($esMemoria)
        <div class="mb-6 overflow-hidden rounded-[2rem] border border-indigo-200 bg-[radial-gradient(circle_at_top,_rgba(255,244,214,0.95),_rgba(181,217,120,0.88)_42%,_rgba(45,58,92,0.96)_100%)] shadow-2xl shadow-indigo-100">
            <div class="relative p-6 md:p-8">
                <div class="absolute inset-y-0 left-0 w-40 bg-[radial-gradient(circle,_rgba(255,196,87,0.28),_transparent_70%)]"></div>
                <div class="absolute inset-y-0 right-0 w-40 bg-[radial-gradient(circle,_rgba(249,115,22,0.25),_transparent_70%)]"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-indigo-950/60">Memoria matematica</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold text-slate-900">Encuentra cada pareja antes de perder tus vidas</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-700">
                            Destapa cartas, relaciona operaciones con sus respuestas y gana monedas mientras avanzas por un escenario de aventura.
                        </p>
                    </div>
                    <div class="grid min-w-[220px] grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Vidas</p>
                            <p class="mt-2 text-2xl font-semibold text-rose-600">3 corazones</p>
                        </div>
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Meta</p>
                            <p class="mt-2 text-2xl font-semibold text-amber-500">{{ $preguntas->count() }} parejas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($esCompletarIngles)
        <div class="mb-6 overflow-hidden rounded-[2rem] border border-sky-200 bg-[radial-gradient(circle_at_top,_rgba(191,219,254,0.96),_rgba(240,249,255,0.96)_36%,_rgba(220,252,231,0.92)_100%)] shadow-2xl shadow-sky-100">
            <div class="relative p-6 md:p-8">
                <div class="absolute inset-y-0 left-0 hidden w-40 bg-[radial-gradient(circle,_rgba(56,189,248,0.20),_transparent_70%)] lg:block"></div>
                <div class="absolute inset-y-0 right-0 hidden w-40 bg-[radial-gradient(circle,_rgba(250,204,21,0.22),_transparent_70%)] lg:block"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-sky-700">English worksheet</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold text-slate-900">Complete the sentences with the correct word</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                            Llena cada espacio usando el banco de palabras y verifica la hoja completa como en una actividad guiada de ingles.
                        </p>
                    </div>
                    <div class="grid min-w-[220px] grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Frases</p>
                            <p class="mt-2 text-2xl font-semibold text-sky-700">{{ count($gameData['frases'] ?? []) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Modo</p>
                            <p class="mt-2 text-2xl font-semibold text-emerald-600">Word bank</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($esOrdenarIngles)
        <div class="mb-6 overflow-hidden rounded-[2rem] border border-amber-200 bg-[radial-gradient(circle_at_top,_rgba(254,240,138,0.20),_rgba(255,251,235,0.98)_24%,_rgba(226,232,240,0.98)_100%)] shadow-2xl shadow-amber-100">
            <div class="relative p-6 md:p-8">
                <div class="absolute inset-y-0 left-0 hidden w-40 bg-[radial-gradient(circle,_rgba(59,130,246,0.16),_transparent_70%)] lg:block"></div>
                <div class="absolute inset-y-0 right-0 hidden w-40 bg-[radial-gradient(circle,_rgba(234,179,8,0.18),_transparent_70%)] lg:block"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-sky-700">English matching board</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold text-slate-900">Match the words with the correct picture card</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                            Selecciona una palabra en ingles y luego su pareja visual para unirlas dentro del tablero.
                        </p>
                    </div>
                    <div class="grid min-w-[220px] grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Pairs</p>
                            <p class="mt-2 text-2xl font-semibold text-sky-700">{{ count($gameData['parejas'] ?? []) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Mode</p>
                            <p class="mt-2 text-2xl font-semibold text-amber-600">Match</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($esSopaMatematica)
        <div class="mb-6 overflow-hidden rounded-[2rem] border border-amber-200/60 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.24),_rgba(33,58,89,0.98)_26%,_rgba(40,25,17,0.98)_76%)] text-white shadow-2xl shadow-amber-100">
            <div class="relative p-6 md:p-8">
                <div class="absolute inset-y-0 left-0 hidden w-40 bg-[radial-gradient(circle,_rgba(251,191,36,0.24),_transparent_70%)] lg:block"></div>
                <div class="absolute inset-y-0 right-0 hidden w-40 bg-[radial-gradient(circle,_rgba(56,189,248,0.18),_transparent_70%)] lg:block"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-amber-100/80">Sopa matematica</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold text-white">Busca los resultados correctos dentro del tablero</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200">
                            Usa las operaciones como pista, arrastra sobre las letras y verifica cada palabra para desbloquear el tesoro matematico.
                        </p>
                    </div>
                    <div class="grid min-w-[220px] grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                            <p class="uppercase tracking-[0.18em] text-white/60">Objetivo</p>
                            <p class="mt-2 text-2xl font-semibold text-amber-300">{{ count($gameData['palabras'] ?? []) }}</p>
                            <p class="mt-1 text-xs text-slate-300">palabras escondidas</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                            <p class="uppercase tracking-[0.18em] text-white/60">Tiempo</p>
                            <p class="mt-2 text-2xl font-semibold text-cyan-300">{{ $juego->tiempo_limite_formateado }}</p>
                            <p class="mt-1 text-xs text-slate-300">para hallar el tesoro</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($esSopa)
        <div class="mb-6 overflow-hidden rounded-3xl border border-sky-100 bg-gradient-to-r from-sky-50 via-white to-indigo-50 shadow-lg shadow-sky-100">
            <div class="p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-sky-600">Sopa de letras</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold text-slate-900">Encuentra las palabras ocultas</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                            Sigue las pistas, selecciona las letras en linea recta y verifica cada palabra antes de que termine el tiempo.
                        </p>
                    </div>
                    <div class="grid min-w-[220px] grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white bg-white/90 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Palabras</p>
                            <p class="mt-2 text-2xl font-semibold text-sky-700">{{ count($gameData['palabras'] ?? []) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white bg-white/90 px-4 py-4 shadow-sm">
                            <p class="uppercase tracking-[0.18em] text-slate-500">Tiempo</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $juego->tiempo_limite_formateado }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-3 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div class="font-medium">
                @if($esMemoria)
                    Parejas encontradas <span x-text="memoryMatchedPairs"></span> de <span x-text="memoryTotalPairs"></span>
                @elseif($esCompletarIngles)
                    Frases correctas <span x-text="englishCorrectCount()"></span> de <span x-text="englishSentenceCount"></span>
                @elseif($esOrdenarIngles)
                    Parejas encontradas <span x-text="englishMatchFoundCount()"></span> de <span x-text="englishMatchTotalPairs"></span>
                @elseif($esSopa)
                    Palabras encontradas <span x-text="wordSearchFoundCount()"></span> de <span x-text="wordSearchTotalWords"></span>
                @else
                    Pregunta <span x-text="currentQuestion + 1"></span> de <span x-text="totalQuestions"></span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                @if($esMatematicaAventura)
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                        <span>ðŸ’°</span>
                        <span x-text="coinsEarned"></span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span>âš¡</span>
                        <span x-text="energyEarned"></span>
                    </div>
                @elseif($esMemoria)
                    <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700">
                        <span>â¤ï¸</span>
                        <span x-text="memoryLives"></span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                        <span>ðŸ’°</span>
                        <span x-text="memoryCoins"></span>
                    </div>
                @elseif($esSopaMatematica)
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                        <span>ðŸ’°</span>
                        <span x-text="wordSearchCoins"></span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                        <span>ðŸ”Ž</span>
                        <span x-text="wordSearchRemaining()"></span>
                    </div>
                @elseif($esCompletarIngles)
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                        <span>ðŸ“</span>
                        <span x-text="englishFilledCount()"></span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span>ðŸ·ï¸</span>
                        <span x-text="englishAvailableWordsCount()"></span>
                    </div>
                @elseif($esOrdenarIngles)
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                        <span>Left</span>
                        <span x-text="englishMatchRemaining()"></span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                        <span>Coins</span>
                        <span x-text="englishMatchCoins"></span>
                    </div>
                @endif
                <div x-show="timeLeft > 0" class="font-medium text-orange-600" x-text="formatTime(timeLeft)"></div>
                <div class="font-bold text-purple-600" x-text="score"></div>
            </div>
        </div>

        <div class="p-8">
            <div x-show="gameState === 'start'" class="py-10">
                @if($esMemoria)
                    <div class="mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-indigo-100 bg-[radial-gradient(circle_at_top,_rgba(245,235,178,0.96),_rgba(186,220,126,0.92)_40%,_rgba(70,54,112,0.96)_100%)] shadow-2xl shadow-indigo-100">
                        <div class="relative p-8 md:p-10">
                            <div class="absolute inset-y-10 left-8 hidden w-24 rounded-full bg-orange-400/30 blur-3xl md:block"></div>
                            <div class="absolute inset-y-10 right-8 hidden w-24 rounded-full bg-amber-300/30 blur-3xl md:block"></div>

                            <div class="relative grid grid-cols-1 gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                                <div class="text-left">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm">
                                        <span>ðŸ§ </span>
                                        <span>Modo memoria</span>
                                    </div>
                                    <h2 class="mt-5 text-4xl font-display font-bold text-white drop-shadow-sm">Memoria Matematica</h2>
                                    <p class="mt-4 max-w-xl text-base leading-7 text-indigo-50">
                                        Destapa cartas, empareja operaciones con resultados y protege tus vidas para completar la mision.
                                    </p>

                                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-2xl bg-white/85 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Parejas</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $preguntas->count() }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-white/85 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Vidas</p>
                                            <p class="mt-2 text-xl font-semibold text-rose-600">3</p>
                                        </div>
                                        <div class="rounded-2xl bg-white/85 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Tiempo</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $juego->tiempo_limite_formateado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-white/40 bg-white/15 p-6 shadow-xl backdrop-blur-sm">
                                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-5">
                                        @for($i = 0; $i < min(10, max(6, $preguntas->count() * 2)); $i++)
                                            @php
                                                $estaAbierta = in_array($i, [1, 2]);
                                            @endphp
                                            <div class="aspect-[0.82] rounded-[1.5rem] border {{ $estaAbierta ? 'border-amber-200 bg-amber-50 text-slate-900' : 'border-violet-300/50 bg-gradient-to-br from-violet-500 to-fuchsia-700 text-white' }} p-3 shadow-lg">
                                                <div class="flex h-full items-center justify-center rounded-[1.2rem] border {{ $estaAbierta ? 'border-amber-100 bg-white/70' : 'border-white/10 bg-white/10' }} text-3xl font-bold">
                                                    @if($estaAbierta)
                                                        {{ $i === 1 ? '5 + 2' : '7' }}
                                                    @else
                                                        ?
                                                    @endif
                                                </div>
                                            </div>
                                        @endfor
                                    </div>

                                    <div class="mt-5 rounded-2xl bg-white/80 px-4 py-3 text-center text-lg font-semibold text-indigo-700 shadow-sm">
                                        Encuentra todas las parejas para ganar el tesoro
                                    </div>
                                </div>
                            </div>

                            <div class="relative mt-8 text-center">
                                <button @click="startGame()" class="rounded-2xl bg-indigo-600 px-8 py-3 font-medium text-white shadow-lg shadow-indigo-300 transition hover:-translate-y-0.5 hover:bg-indigo-700">
                                    Comenzar memoria
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($esCompletarIngles)
                    <div class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-sky-200 bg-[radial-gradient(circle_at_top,_rgba(191,219,254,0.98),_rgba(255,255,255,0.98)_38%,_rgba(220,252,231,0.94)_100%)] shadow-2xl shadow-sky-100">
                        <div class="relative p-8 md:p-10">
                            <div class="absolute inset-y-10 left-8 hidden w-24 rounded-full bg-sky-300/25 blur-3xl md:block"></div>
                            <div class="absolute inset-y-10 right-8 hidden w-24 rounded-full bg-amber-200/25 blur-3xl md:block"></div>

                            <div class="relative grid grid-cols-1 gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                                <div class="text-left">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-sky-700 shadow-sm">
                                        <span>âœï¸</span>
                                        <span>English practice</span>
                                    </div>
                                    <h2 class="mt-5 text-4xl font-display font-bold text-slate-900">Complete the sentences!</h2>
                                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                                        Fill in the blanks with the correct word from the word bank and complete the whole worksheet before time runs out.
                                    </p>

                                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Sentences</p>
                                            <p class="mt-2 text-xl font-semibold text-sky-700">{{ count($gameData['frases'] ?? []) }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Word bank</p>
                                            <p class="mt-2 text-xl font-semibold text-emerald-600">{{ count($gameData['frases'] ?? []) }} words</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Time</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $juego->tiempo_limite_formateado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-emerald-100 bg-white/90 p-6 shadow-xl">
                                    <div class="mx-auto max-w-2xl rounded-[1.8rem] border-[6px] border-emerald-200 bg-[repeating-linear-gradient(to_bottom,_#fffdf5_0,_#fffdf5_48px,_#d8efff_48px,_#d8efff_52px)] px-6 py-6 shadow-inner">
                                        <div class="rounded-[1.4rem] border-4 border-amber-300 bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-500 px-5 py-4 text-center text-white shadow-lg">
                                            <p class="text-xs uppercase tracking-[0.24em] text-white/80">Worksheet preview</p>
                                            <p class="mt-2 text-3xl font-display font-bold">Complete the sentences!</p>
                                        </div>

                                        <div class="mt-6 space-y-4">
                                            @foreach(collect($gameData['frases'] ?? [])->take(3) as $frase)
                                                <div class="flex items-center gap-3 text-lg font-semibold text-slate-700">
                                                    <span class="text-sky-600">{{ $loop->iteration }}.</span>
                                                    <span>{{ preg_replace('/_{2,}/', '______', $frase['enunciado'] ?? '') }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-8">
                                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Word bank</p>
                                            <div class="mt-3 flex flex-wrap gap-3">
                                                @foreach(collect($gameData['frases'] ?? [])->take(4) as $frase)
                                                    <span class="rounded-2xl bg-sky-100 px-4 py-2 text-sm font-bold text-sky-700 shadow-sm">{{ $frase['respuesta'] ?? '' }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative mt-8 text-center">
                                <button @click="startGame()" class="rounded-2xl bg-emerald-500 px-8 py-3 font-medium text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-600">
                                    Start worksheet
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($esOrdenarIngles)
                    <div class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-amber-200 bg-[radial-gradient(circle_at_top,_rgba(254,240,138,0.24),_rgba(255,251,235,0.98)_34%,_rgba(226,232,240,0.98)_100%)] shadow-2xl shadow-amber-100">
                        <div class="relative p-8 md:p-10">
                            <div class="absolute inset-y-10 left-8 hidden w-24 rounded-full bg-sky-300/20 blur-3xl md:block"></div>
                            <div class="absolute inset-y-10 right-8 hidden w-24 rounded-full bg-amber-300/20 blur-3xl md:block"></div>

                            <div class="relative grid grid-cols-1 gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                                <div class="text-left">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-sky-700 shadow-sm">
                                        <span>ðŸ‡¬ðŸ‡§</span>
                                        <span>Hello! Let's learn English</span>
                                    </div>
                                    <h2 class="mt-5 text-4xl font-display font-bold text-slate-900">Match the Words!</h2>
                                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                                        Match each English word with its correct picture card and complete the whole board to win the round.
                                    </p>

                                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Pairs</p>
                                            <p class="mt-2 text-xl font-semibold text-sky-700">{{ count($gameData['parejas'] ?? []) }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Board</p>
                                            <p class="mt-2 text-xl font-semibold text-emerald-600">Matching</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white/90 p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Time</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $juego->tiempo_limite_formateado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-slate-700 bg-[radial-gradient(circle_at_top,_rgba(34,197,94,0.12),_rgba(22,101,52,0.96)_18%,_rgba(21,128,61,0.94)_60%,_rgba(15,23,42,0.94)_100%)] p-6 shadow-xl">
                                    <div class="rounded-[1.7rem] border-8 border-amber-900/60 bg-slate-800/20 p-6 shadow-inner">
                                        <p class="text-center text-3xl font-display font-bold text-amber-50">Match the Words!</p>
                                        <div class="mt-6 grid grid-cols-2 gap-4">
                                            @foreach(collect($gameData['parejas'] ?? [])->take(3) as $pair)
                                                <div class="rounded-2xl bg-amber-50 px-4 py-4 text-center text-xl font-black text-sky-900 shadow-sm">
                                                    {{ $pair['palabra'] ?? '' }}
                                                </div>
                                            @endforeach
                                            @foreach(collect($gameData['opciones'] ?? [])->take(3) as $pair)
                                                @php
                                                    $previewPairValue = $pair['pareja'] ?? '';
                                                    $previewPairIsImage = is_string($previewPairValue)
                                                        && preg_match('/^(https?:\/\/|\/|storage\/).+\.(png|jpe?g|gif|webp|svg)$/i', $previewPairValue);
                                                    $previewPairUrl = $previewPairIsImage
                                                        ? (\Illuminate\Support\Str::startsWith($previewPairValue, ['http://', 'https://', '/']) ? $previewPairValue : asset($previewPairValue))
                                                        : null;
                                                @endphp
                                                <div class="rounded-2xl bg-white px-4 py-4 text-center text-xl font-black text-slate-800 shadow-sm">
                                                    @if($previewPairIsImage)
                                                        <img src="{{ $previewPairUrl }}" alt="Picture card" class="mx-auto h-20 w-full rounded-2xl object-contain">
                                                    @else
                                                        {{ $previewPairValue }}
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-6 rounded-2xl bg-white/10 px-4 py-3 text-center text-sm font-semibold text-amber-50">
                                            Click the word first and then the matching picture card.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative mt-8 text-center">
                                <button @click="startGame()" class="rounded-2xl bg-sky-600 px-8 py-3 font-medium text-white shadow-lg shadow-sky-200 transition hover:-translate-y-0.5 hover:bg-sky-700">
                                    Start matching
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($esSopaMatematica)
                    <div class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-amber-200/60 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.18),_rgba(36,52,77,0.96)_22%,_rgba(53,33,21,0.98)_78%)] text-white shadow-2xl shadow-amber-100">
                        <div class="relative p-8 md:p-10">
                            <div class="absolute inset-y-12 left-6 hidden w-24 rounded-full bg-amber-300/25 blur-3xl lg:block"></div>
                            <div class="absolute inset-y-12 right-6 hidden w-24 rounded-full bg-sky-300/20 blur-3xl lg:block"></div>

                            <div class="relative grid grid-cols-1 gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                                <div class="text-left">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-amber-100 backdrop-blur-sm">
                                        <span>â›ï¸</span>
                                        <span>Mision de busqueda</span>
                                    </div>
                                    <h2 class="mt-5 text-4xl font-display font-bold text-white drop-shadow-sm">Sopa Matematica</h2>
                                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-200">
                                        Encuentra cada resultado escondido en el tablero, usando las operaciones como pista y verificando cada seleccion para sumar puntos.
                                    </p>

                                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Objetivos</p>
                                            <p class="mt-2 text-xl font-semibold text-amber-300">{{ count($gameData['palabras'] ?? []) }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Tablero</p>
                                            <p class="mt-2 text-xl font-semibold text-cyan-300">{{ $gameData['tamano'] ?? 0 }} x {{ $gameData['tamano'] ?? 0 }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-sm backdrop-blur-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Tiempo</p>
                                            <p class="mt-2 text-xl font-semibold text-white">{{ $juego->tiempo_limite_formateado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-white/15 bg-white/10 p-6 shadow-xl backdrop-blur-sm">
                                    <div class="mx-auto max-w-md rounded-[1.6rem] border border-cyan-200/40 bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-700 px-5 py-4 text-center shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.22em] text-white/75">Vista previa</p>
                                        <p class="mt-1 text-3xl font-display font-bold text-white">Sopa Matematica</p>
                                    </div>

                                    <div class="mt-6 grid gap-2" style="grid-template-columns: repeat({{ min(6, max(1, (int) ($gameData['tamano'] ?? 0))) }}, minmax(0, 1fr));">
                                        @forelse($previewSopaGrid as $previewRow)
                                            @foreach(collect($previewRow)->take(6) as $previewLetter)
                                                <div class="aspect-square rounded-2xl border border-amber-100/60 bg-amber-50/90 text-center text-lg font-black leading-[3.2rem] text-slate-900 shadow-sm">
                                                    {{ $previewLetter }}
                                                </div>
                                            @endforeach
                                        @empty
                                            <div class="col-span-full rounded-2xl border border-dashed border-white/20 px-4 py-6 text-center text-sm text-slate-300">
                                                Agrega palabras para previsualizar la sopa.
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-5 rounded-2xl bg-emerald-400/15 px-4 py-3 text-center text-sm font-semibold text-emerald-100">
                                        Arrastra sobre el tablero y pulsa verificar para confirmar cada resultado.
                                    </div>
                                </div>
                            </div>

                            <div class="relative mt-8 text-center">
                                <button @click="startGame()" class="rounded-2xl bg-emerald-500 px-8 py-3 font-medium text-white shadow-lg shadow-emerald-900/30 transition hover:-translate-y-0.5 hover:bg-emerald-600">
                                    Comenzar sopa matematica
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($esSopa)
                    <div class="mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-indigo-50 shadow-xl shadow-sky-100">
                        <div class="p-8 md:p-10">
                            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                                <div class="text-left">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                                        <span>ðŸ”¤</span>
                                        <span>Modo sopa</span>
                                    </div>
                                    <h2 class="mt-5 text-4xl font-display font-bold text-slate-900">Sopa de Letras</h2>
                                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                                        Selecciona letras en horizontal, vertical o diagonal y verifica cada palabra para completar el reto.
                                    </p>

                                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Palabras</p>
                                            <p class="mt-2 text-xl font-semibold text-sky-700">{{ count($gameData['palabras'] ?? []) }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Tamano</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $gameData['tamano'] ?? 0 }} x {{ $gameData['tamano'] ?? 0 }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Tiempo</p>
                                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $juego->tiempo_limite_formateado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-sky-100 bg-white/90 p-6 shadow-lg">
                                    <div class="grid gap-2" style="grid-template-columns: repeat({{ min(6, max(1, (int) ($gameData['tamano'] ?? 0))) }}, minmax(0, 1fr));">
                                        @forelse($previewSopaGrid as $previewRow)
                                            @foreach(collect($previewRow)->take(6) as $previewLetter)
                                                <div class="aspect-square rounded-2xl border border-sky-100 bg-slate-50 text-center text-lg font-black leading-[3.2rem] text-slate-900">
                                                    {{ $previewLetter }}
                                                </div>
                                            @endforeach
                                        @empty
                                            <div class="col-span-full rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500">
                                                Agrega palabras para previsualizar la sopa.
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-5 rounded-2xl bg-sky-50 px-4 py-3 text-center text-sm font-semibold text-sky-700">
                                        Selecciona una linea recta y confirma la palabra con el boton verificar.
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 text-center">
                                <button @click="startGame()" class="rounded-2xl bg-sky-600 px-8 py-3 font-medium text-white shadow-lg shadow-sky-200 transition hover:-translate-y-0.5 hover:bg-sky-700">
                                    Comenzar sopa
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="text-5xl mb-4">{{ $juego->tipo_icono }}</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-3">Listo para comenzar</h2>
                        <p class="text-gray-600 mb-6 max-w-xl mx-auto">{{ $juego->descripcion }}</p>

                        @if($esMatematicaAventura)
                            <div class="mx-auto mb-6 max-w-3xl rounded-3xl border border-emerald-100 bg-emerald-50 p-6 text-left">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Objetivo</p>
                                        <p class="mt-2 font-semibold text-gray-900">{{ $goalLabels[$objetivo] ?? 'Cruza los puentes numericos' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Operacion</p>
                                        <p class="mt-2 font-semibold text-gray-900">{{ $operationLabels[$operacion] ?? 'Mixto' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Recompensa</p>
                                        <p class="mt-2 font-semibold text-gray-900">
                                            @if($recompensa === 'energia')
                                                +{{ $energia }} energia
                                            @elseif($recompensa === 'ambas')
                                                +{{ $monedas }} monedas y +{{ $energia }} energia
                                            @else
                                                +{{ $monedas }} monedas
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-6 space-y-1 text-sm text-gray-500">
                            <p>{{ $preguntas->count() }} preguntas</p>
                            <p>Dificultad {{ $juego->dificultad }}</p>
                            <p>Tiempo: {{ $juego->tiempo_limite_formateado }}</p>
                        </div>
                        <button @click="startGame()" class="rounded-xl bg-purple-600 px-8 py-3 font-medium text-white transition hover:bg-purple-700">
                            {{ $esMatematicaAventura ? 'Comenzar aventura' : 'Comenzar juego' }}
                        </button>
                    </div>
                @endif
            </div>

            <div x-show="gameState === 'playing'" x-cloak>
                @if($esMemoria)
                    <div class="overflow-hidden rounded-[2rem] border border-indigo-100 bg-[radial-gradient(circle_at_top,_rgba(245,235,178,0.98),_rgba(186,220,126,0.9)_42%,_rgba(59,47,98,0.96)_100%)] shadow-2xl shadow-indigo-100">
                        <div class="relative p-5 md:p-8">
                            <div class="absolute left-4 top-8 hidden h-28 w-28 rounded-full bg-orange-400/30 blur-3xl md:block"></div>
                            <div class="absolute bottom-10 right-4 hidden h-28 w-28 rounded-full bg-amber-300/30 blur-3xl md:block"></div>

                            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-white/85 text-4xl shadow-lg">ðŸ§’</div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.24em] text-indigo-950/50">Memoria matematica</p>
                                        <h3 class="mt-2 text-3xl font-display font-semibold text-white">Encuentra cada pareja correcta</h3>
                                        <p class="mt-2 text-sm text-indigo-50">Relaciona operaciones con resultados para abrir el tesoro final.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                    <div class="rounded-2xl bg-white/85 px-4 py-4 shadow-sm">
                                        <p class="uppercase tracking-[0.18em] text-slate-500">Vidas</p>
                                        <p class="mt-2 text-2xl font-semibold text-rose-600" x-text="memoryLives"></p>
                                    </div>
                                    <div class="rounded-2xl bg-white/85 px-4 py-4 shadow-sm">
                                        <p class="uppercase tracking-[0.18em] text-slate-500">Monedas</p>
                                        <p class="mt-2 text-2xl font-semibold text-amber-500" x-text="memoryCoins"></p>
                                    </div>
                                    <div class="rounded-2xl bg-white/85 px-4 py-4 shadow-sm">
                                        <p class="uppercase tracking-[0.18em] text-slate-500">Parejas</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-900"><span x-text="memoryMatchedPairs"></span>/<span x-text="memoryTotalPairs"></span></p>
                                    </div>
                                    <div class="rounded-2xl bg-white/85 px-4 py-4 shadow-sm">
                                        <p class="uppercase tracking-[0.18em] text-slate-500">Progreso</p>
                                        <p class="mt-2 text-2xl font-semibold text-indigo-700" x-text="memoryProgressPercent() + '%'"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1fr_220px]">
                                <div>
                                    <div class="mb-5 flex items-center justify-between gap-4 rounded-2xl bg-white/80 px-5 py-4 shadow-sm">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Objetivo</p>
                                            <p class="mt-1 font-semibold text-slate-900">Destapa dos cartas y encuentra la pareja correcta</p>
                                        </div>
                                        <div class="h-3 w-40 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 via-indigo-500 to-fuchsia-500" :style="`width:${memoryProgressPercent()}%`"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                                        <template x-for="card in memoryCardsForDisplay()" :key="card.id">
                                            <button
                                                type="button"
                                                @click="flipMemoryCard(card.id)"
                                                :disabled="memoryLockBoard || card.matched"
                                                class="group relative aspect-[0.76] [perspective:1200px]"
                                            >
                                                <div class="relative h-full w-full transition-transform duration-500 [transform-style:preserve-3d]"
                                                     :class="{ '[transform:rotateY(180deg)]': card.flipped || card.matched }">
                                                    <div class="absolute inset-0 rounded-[1.7rem] border border-violet-300/60 bg-gradient-to-br from-violet-500 via-fuchsia-600 to-indigo-900 p-3 shadow-xl [backface-visibility:hidden]">
                                                        <div class="flex h-full items-center justify-center rounded-[1.35rem] border border-white/10 bg-white/10 text-6xl font-black text-white/95 shadow-inner">
                                                            ?
                                                        </div>
                                                    </div>
                                                    <div class="absolute inset-0 rounded-[1.7rem] border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100 p-3 text-slate-900 shadow-xl [backface-visibility:hidden] [transform:rotateY(180deg)]"
                                                         :class="{ 'ring-4 ring-emerald-300': card.matched }">
                                                        <div class="flex h-full items-center justify-center rounded-[1.35rem] border border-white/60 bg-white/70 p-3 text-center text-3xl font-black leading-tight shadow-inner"
                                                             :class="card.tipo === 'pregunta' ? 'text-slate-800' : 'text-indigo-700'">
                                                            <span x-text="card.contenido"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-[1.7rem] bg-white/85 p-5 shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Mensaje</p>
                                        <p class="mt-3 text-2xl font-display font-semibold text-indigo-700" x-text="memoryCelebration || 'Encuentra la siguiente pareja'"></p>
                                        <p class="mt-2 text-sm text-slate-600">Cada pareja correcta suma puntos y monedas. Si fallas, pierdes una vida.</p>
                                    </div>

                                    <div class="rounded-[1.7rem] bg-slate-900/85 p-5 text-white shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.18em] text-cyan-300">Tesoro acumulado</p>
                                        <p class="mt-3 text-4xl font-black text-amber-300" x-text="memoryCoins"></p>
                                        <p class="mt-2 text-sm text-slate-300">Sigue emparejando para completar toda la cueva del conocimiento.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($esSopa)
                    <div class="{{ $esSopaMatematica ? 'overflow-hidden rounded-[2rem] border border-amber-200/60 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.2),_rgba(37,51,73,0.97)_24%,_rgba(56,33,21,0.99)_76%)] text-white shadow-2xl shadow-amber-100' : 'overflow-hidden rounded-[2rem] border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-indigo-50 shadow-xl shadow-sky-100' }}">
                        <div class="relative p-5 md:p-8">
                            @if($esSopaMatematica)
                                <div class="absolute left-4 top-8 hidden h-28 w-28 rounded-full bg-amber-300/20 blur-3xl md:block"></div>
                                <div class="absolute bottom-10 right-4 hidden h-28 w-28 rounded-full bg-sky-300/20 blur-3xl md:block"></div>
                            @endif

                            <div class="relative space-y-6">
                                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="{{ $esSopaMatematica ? 'flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-white/12 text-4xl shadow-lg backdrop-blur-sm' : 'flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-sky-100 text-4xl shadow-lg' }}">ðŸ”¤</div>
                                        <div>
                                            <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.24em] text-amber-100/80' : 'text-xs uppercase tracking-[0.24em] text-sky-700' }}">
                                                {{ $esSopaMatematica ? 'Sopa matematica' : 'Sopa de letras' }}
                                            </p>
                                            <h3 class="{{ $esSopaMatematica ? 'mt-2 text-3xl font-display font-semibold text-white' : 'mt-2 text-3xl font-display font-semibold text-slate-900' }}">
                                                {{ $esSopaMatematica ? 'Encuentra cada resultado escondido' : 'Encuentra cada palabra escondida' }}
                                            </h3>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-sm text-slate-200' : 'mt-2 text-sm text-slate-600' }}">
                                                {{ $esSopaMatematica ? 'Arrastra sobre el tablero, mantente en linea recta y verifica para desbloquear el tesoro.' : 'Selecciona letras en linea recta y confirma cada palabra con el boton verificar.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                        <div class="{{ $esSopaMatematica ? 'rounded-2xl border border-white/10 bg-white/10 px-4 py-4 shadow-sm backdrop-blur-sm' : 'rounded-2xl border border-white bg-white px-4 py-4 shadow-sm' }}">
                                            <p class="{{ $esSopaMatematica ? 'uppercase tracking-[0.18em] text-slate-300' : 'uppercase tracking-[0.18em] text-slate-500' }}">Encontradas</p>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-2xl font-semibold text-amber-300' : 'mt-2 text-2xl font-semibold text-sky-700' }}"><span x-text="wordSearchFoundCount()"></span>/<span x-text="wordSearchTotalWords"></span></p>
                                        </div>
                                        <div class="{{ $esSopaMatematica ? 'rounded-2xl border border-white/10 bg-white/10 px-4 py-4 shadow-sm backdrop-blur-sm' : 'rounded-2xl border border-white bg-white px-4 py-4 shadow-sm' }}">
                                            <p class="{{ $esSopaMatematica ? 'uppercase tracking-[0.18em] text-slate-300' : 'uppercase tracking-[0.18em] text-slate-500' }}">Restantes</p>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-2xl font-semibold text-cyan-300' : 'mt-2 text-2xl font-semibold text-slate-900' }}" x-text="wordSearchRemaining()"></p>
                                        </div>
                                        <div class="{{ $esSopaMatematica ? 'rounded-2xl border border-white/10 bg-white/10 px-4 py-4 shadow-sm backdrop-blur-sm' : 'rounded-2xl border border-white bg-white px-4 py-4 shadow-sm' }}">
                                            <p class="{{ $esSopaMatematica ? 'uppercase tracking-[0.18em] text-slate-300' : 'uppercase tracking-[0.18em] text-slate-500' }}">Tablero</p>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-2xl font-semibold text-white' : 'mt-2 text-2xl font-semibold text-slate-900' }}" x-text="wordSearchGrid.length"></p>
                                        </div>
                                        <div class="{{ $esSopaMatematica ? 'rounded-2xl border border-white/10 bg-white/10 px-4 py-4 shadow-sm backdrop-blur-sm' : 'rounded-2xl border border-white bg-white px-4 py-4 shadow-sm' }}">
                                            <p class="{{ $esSopaMatematica ? 'uppercase tracking-[0.18em] text-slate-300' : 'uppercase tracking-[0.18em] text-slate-500' }}">Monedas</p>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-2xl font-semibold text-amber-300' : 'mt-2 text-2xl font-semibold text-sky-700' }}" x-text="wordSearchCoins"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.82fr_1.12fr_0.66fr]">
                                    <div class="space-y-4">
                                        <div class="{{ $esSopaMatematica ? 'rounded-[1.8rem] border border-white/10 bg-white/10 p-5 shadow-lg backdrop-blur-sm' : 'rounded-[1.8rem] border border-sky-100 bg-white p-5 shadow-lg' }}">
                                            <div class="flex items-center justify-between gap-4">
                                                <div>
                                                    <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.18em] text-amber-100/80' : 'text-xs uppercase tracking-[0.18em] text-sky-700' }}">
                                                        {{ $esSopaMatematica ? 'Operaciones' : 'Pistas' }}
                                                    </p>
                                                    <p class="{{ $esSopaMatematica ? 'mt-2 text-xl font-display font-semibold text-white' : 'mt-2 text-xl font-display font-semibold text-slate-900' }}">
                                                        {{ $esSopaMatematica ? 'Resuelve y busca la palabra' : 'Sigue cada pista' }}
                                                    </p>
                                                </div>
                                                <div class="{{ $esSopaMatematica ? 'rounded-2xl bg-amber-300/15 px-3 py-2 text-xs font-semibold text-amber-100' : 'rounded-2xl bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700' }}">
                                                    <span x-text="wordSearchFoundCount()"></span>/<span x-text="wordSearchTotalWords"></span>
                                                </div>
                                            </div>

                                            <div class="mt-4 space-y-3">
                                                <template x-for="(word, index) in wordSearchWords" :key="`clue-${word.id}`">
                                                    <div class="rounded-[1.35rem] p-4 transition" :style="wordSearchClueStyle(index, word.id)">
                                                        <div class="flex items-start justify-between gap-4">
                                                            <div>
                                                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] opacity-75">
                                                                    {{ $esSopaMatematica ? 'Operacion' : 'Pista' }}
                                                                </p>
                                                                <p class="mt-2 text-xl font-black leading-tight" x-text="word.pista || word.palabra"></p>
                                                                <p class="mt-2 text-sm font-semibold opacity-80">
                                                                    Busca: <span x-text="word.palabra"></span>
                                                                </p>
                                                            </div>
                                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/55 text-base font-black text-slate-900 shadow-sm">
                                                                <span x-text="word.found ? 'âœ“' : index + 1"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="{{ $esSopaMatematica ? 'mb-5 rounded-[1.8rem] border border-white/10 bg-white/10 px-5 py-4 shadow-lg backdrop-blur-sm' : 'mb-5 rounded-[1.8rem] border border-sky-100 bg-white px-5 py-4 shadow-lg' }}">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.18em] text-slate-300' : 'text-xs uppercase tracking-[0.18em] text-slate-500' }}">Seleccion actual</p>
                                                    <p class="{{ $esSopaMatematica ? 'mt-2 text-2xl font-display font-semibold text-white' : 'mt-2 text-2xl font-display font-semibold text-slate-900' }}" x-text="wordSearchSelectionLabel()"></p>
                                                </div>
                                                <div class="sm:min-w-[170px]">
                                                    <div class="{{ $esSopaMatematica ? 'h-3 overflow-hidden rounded-full bg-white/10' : 'h-3 overflow-hidden rounded-full bg-slate-200' }}">
                                                        <div class="{{ $esSopaMatematica ? 'h-full rounded-full bg-gradient-to-r from-cyan-400 via-amber-300 to-emerald-400' : 'h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500' }}" :style="`width:${wordSearchProgressPercent()}%`"></div>
                                                    </div>
                                                    <p class="{{ $esSopaMatematica ? 'mt-2 text-right text-xs text-slate-300' : 'mt-2 text-right text-xs text-slate-500' }}" x-text="wordSearchProgressPercent() + '%'"></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="{{ $esSopaMatematica ? 'rounded-[2rem] border border-amber-100/40 bg-amber-50/90 p-4 shadow-2xl' : 'rounded-[2rem] border border-sky-100 bg-white p-4 shadow-xl' }}">
                                            <div class="{{ $esSopaMatematica ? 'mx-auto mb-5 max-w-sm rounded-[1.6rem] border border-cyan-200/50 bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-700 px-5 py-4 text-center shadow-lg' : 'mx-auto mb-5 max-w-sm rounded-[1.6rem] border border-sky-200 bg-sky-50 px-5 py-4 text-center shadow-sm' }}">
                                                <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.22em] text-white/75' : 'text-xs uppercase tracking-[0.22em] text-sky-700' }}">
                                                    {{ $esSopaMatematica ? 'Sopa Matematica' : 'Tablero de busqueda' }}
                                                </p>
                                                <p class="{{ $esSopaMatematica ? 'mt-1 text-3xl font-display font-bold text-white' : 'mt-1 text-2xl font-display font-bold text-slate-900' }}">
                                                    {{ $esSopaMatematica ? 'Encuentra el tesoro' : 'Encuentra las palabras' }}
                                                </p>
                                            </div>

                                            <div class="mx-auto w-full max-w-[42rem]">
                                                <div class="grid gap-2 select-none" :style="wordSearchGridStyle()" @pointermove.prevent="trackWordSearchPointer($event)" @pointerup.prevent="finishWordSearchSelection()" style="touch-action: none;">
                                                    <template x-for="(row, rowIndex) in wordSearchGrid" :key="`row-${rowIndex}`">
                                                        <template x-for="(cell, colIndex) in row" :key="`cell-${rowIndex}-${colIndex}`">
                                                            <button
                                                                type="button"
                                                                data-word-cell="true"
                                                                :data-row="rowIndex"
                                                                :data-col="colIndex"
                                                                @pointerdown.prevent="startWordSearchSelection(rowIndex, colIndex)"
                                                                @pointerenter="updateWordSearchSelection(rowIndex, colIndex)"
                                                                @pointerup.prevent="finishWordSearchSelection()"
                                                                :style="wordSearchCellStyle(rowIndex, colIndex)"
                                                                class="flex aspect-square w-full min-h-[2.35rem] items-center justify-center overflow-hidden rounded-xl border p-0 text-center font-black uppercase leading-none tracking-tight transition duration-150 md:min-h-[2.9rem]"
                                                            >
                                                                <span class="pointer-events-none block w-full leading-none" x-text="cell"></span>
                                                            </button>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="{{ $esSopaMatematica ? 'rounded-[1.8rem] border border-white/10 bg-white/10 p-5 shadow-lg backdrop-blur-sm' : 'rounded-[1.8rem] border border-sky-100 bg-white p-5 shadow-lg' }}">
                                            <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.18em] text-cyan-200' : 'text-xs uppercase tracking-[0.18em] text-sky-700' }}">Palabras objetivo</p>
                                            <div class="mt-4 space-y-3">
                                                <template x-for="(word, index) in wordSearchWords" :key="`badge-${word.id}`">
                                                    <div class="flex items-center justify-between gap-3 rounded-[1.2rem] px-4 py-3 transition" :style="wordSearchBadgeStyle(index, word.id)">
                                                        <span class="text-base font-black tracking-wide" x-text="word.palabra"></span>
                                                        <span class="text-sm font-semibold" x-text="word.found ? 'Encontrada' : 'Pendiente'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="{{ $esSopaMatematica ? 'rounded-[1.8rem] border border-white/10 bg-slate-950/40 p-5 shadow-lg' : 'rounded-[1.8rem] border border-slate-200 bg-slate-900 p-5 text-white shadow-lg' }}">
                                            <p class="{{ $esSopaMatematica ? 'text-xs uppercase tracking-[0.18em] text-amber-200' : 'text-xs uppercase tracking-[0.18em] text-cyan-300' }}">Mensaje</p>
                                            <p class="{{ $esSopaMatematica ? 'mt-3 text-2xl font-display font-semibold text-white' : 'mt-3 text-2xl font-display font-semibold text-white' }}" x-text="wordSearchMessage"></p>
                                            <p class="{{ $esSopaMatematica ? 'mt-2 text-sm text-slate-300' : 'mt-2 text-sm text-slate-300' }}">
                                                {{ $esSopaMatematica ? 'Puedes arrastrar hacia adelante o hacia atras. Solo cuentan lineas rectas.' : 'Selecciona una linea recta; las diagonales tambien son validas.' }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="verifyWordSearchSelection()"
                                            :disabled="!canVerifyWordSearch()"
                                            :class="{ 'opacity-40 cursor-not-allowed': !canVerifyWordSearch() }"
                                            class="{{ $esSopaMatematica ? 'w-full rounded-[1.5rem] bg-gradient-to-r from-lime-400 via-emerald-500 to-green-600 px-6 py-4 text-xl font-black text-white shadow-xl shadow-emerald-950/25 transition hover:-translate-y-0.5' : 'w-full rounded-[1.5rem] bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-4 text-xl font-black text-white shadow-xl shadow-sky-200 transition hover:-translate-y-0.5' }}"
                                        >
                                            Verificar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($esCompletarIngles)
                    <div class="overflow-hidden rounded-[2rem] border border-sky-200 bg-[radial-gradient(circle_at_top,_rgba(191,219,254,0.98),_rgba(255,255,255,0.98)_34%,_rgba(220,252,231,0.94)_100%)] shadow-2xl shadow-sky-100">
                        <div class="relative p-4 md:p-8">
                            <div class="absolute left-4 top-8 hidden h-28 w-28 rounded-full bg-sky-300/20 blur-3xl md:block"></div>
                            <div class="absolute bottom-10 right-4 hidden h-28 w-28 rounded-full bg-amber-200/20 blur-3xl md:block"></div>

                            <div class="relative space-y-6">
                                <div class="mx-auto max-w-5xl rounded-[1.8rem] border-4 border-amber-300 bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-500 px-6 py-5 text-center text-white shadow-xl">
                                    <p class="text-xs uppercase tracking-[0.24em] text-white/80">English practice</p>
                                    <h3 class="mt-2 text-4xl font-display font-bold">Complete the Sentences!</h3>
                                </div>

                                <div class="mx-auto max-w-6xl rounded-[2rem] border-[6px] border-emerald-200 bg-[repeating-linear-gradient(to_bottom,_#fffdf5_0,_#fffdf5_52px,_#d7efff_52px,_#d7efff_56px)] p-4 shadow-inner md:p-8">
                                    <div class="rounded-[1.5rem] border-4 border-amber-200 bg-amber-50 px-5 py-4 text-center shadow-sm">
                                        <p class="text-xl font-display font-semibold text-slate-700">Fill in the blanks with the correct word.</p>
                                    </div>

                                    <div class="mt-8 space-y-5">
                                        <template x-for="(sentence, index) in englishSentences" :key="sentence.id">
                                            <button
                                                type="button"
                                                @click="englishSelectSentence(sentence.id)"
                                                class="flex w-full items-center gap-3 rounded-[1.4rem] px-3 py-2 text-left transition"
                                                :class="{
                                                    'bg-white/70 ring-2 ring-sky-300 shadow-sm': englishActiveSentenceId === sentence.id && sentence.status !== 'correct',
                                                    'bg-transparent': englishActiveSentenceId !== sentence.id,
                                                    'opacity-90': sentence.status === 'correct'
                                                }"
                                            >
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-100 text-base font-black text-sky-700 shadow-sm" x-text="index + 1"></div>
                                                <div class="flex flex-1 flex-wrap items-end gap-2 text-2xl font-semibold leading-relaxed text-slate-800 md:text-[2rem]">
                                                    <span x-text="sentence.before"></span>
                                                    <span
                                                        class="inline-flex min-w-[120px] items-center justify-center rounded-xl border-b-[3px] px-3 py-1 text-center text-xl font-bold transition md:min-w-[150px] md:text-2xl"
                                                        :class="{
                                                            'border-sky-500 text-sky-700 bg-sky-50': englishActiveSentenceId === sentence.id && sentence.status !== 'correct',
                                                            'border-emerald-500 text-emerald-700 bg-emerald-50': sentence.status === 'correct',
                                                            'border-rose-400 text-rose-700 bg-rose-50': sentence.status === 'incorrect',
                                                            'border-slate-300 text-slate-400 bg-white/70': !sentence.userAnswer && sentence.status !== 'incorrect' && sentence.status !== 'correct'
                                                        }"
                                                        x-text="sentence.userAnswer || '______'"
                                                    ></span>
                                                    <span x-text="sentence.after"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>

                                    <div class="mt-10 rounded-[1.6rem] border-4 border-amber-200 bg-white/85 p-5 shadow-sm">
                                        <div class="rounded-[1.1rem] bg-gradient-to-r from-amber-600 to-orange-500 px-4 py-3 text-center text-white shadow-md">
                                            <p class="text-2xl font-display font-bold uppercase tracking-[0.18em]">Word Bank</p>
                                        </div>

                                        <div class="mt-5 flex flex-wrap gap-3">
                                            <template x-for="word in englishWordBank" :key="word.id">
                                                <button
                                                    type="button"
                                                    @click="englishAssignWord(word.id)"
                                                    :disabled="!!word.usedBy"
                                                    :style="englishWordStyle(word)"
                                                    class="rounded-[1rem] px-5 py-3 text-lg font-black text-white shadow-md transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    <span x-text="word.text"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="mt-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <div class="rounded-[1.4rem] bg-white/85 px-5 py-4 shadow-sm">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Message</p>
                                            <p class="mt-2 text-2xl font-display font-semibold text-slate-800" x-text="englishWorksheetMessage || 'Choose a line and then a word from the bank.'"></p>
                                        </div>

                                        <div class="flex flex-col gap-3 sm:flex-row">
                                            <button
                                                type="button"
                                                @click="englishClearActiveSentence()"
                                                :disabled="!englishCanClearActiveSentence()"
                                                class="rounded-2xl bg-white px-6 py-3 font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            >
                                                Clear
                                            </button>
                                            <button
                                                type="button"
                                                @click="submitEnglishWorksheet()"
                                                :disabled="!canSubmitEnglishWorksheet()"
                                                class="rounded-2xl bg-emerald-500 px-8 py-3 font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-40"
                                            >
                                                Check answers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($esOrdenarIngles)
                    <div class="overflow-hidden rounded-[2rem] border border-amber-300 bg-[linear-gradient(180deg,_#7a4e2e_0%,_#99633b_14%,_#d7b392_14%,_#f2e6d7_100%)] shadow-2xl shadow-amber-100">
                        <div class="relative p-4 md:p-8">
                            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[240px_1fr_220px]">
                                <div class="space-y-4">
                                    <div class="rounded-[1.7rem] bg-gradient-to-br from-sky-600 to-blue-800 p-5 text-white shadow-xl">
                                        <p class="text-xs uppercase tracking-[0.24em] text-white/70">English corner</p>
                                        <h3 class="mt-3 text-3xl font-display font-bold">Hello!</h3>
                                        <p class="mt-2 text-lg font-semibold">Let's match the words.</p>
                                        <p class="mt-3 text-sm leading-6 text-sky-100">
                                            Choose a word card and then the correct picture card to complete the classroom board.
                                        </p>
                                    </div>

                                    <div class="rounded-[1.6rem] bg-white/85 p-5 shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Round goal</p>
                                        <p class="mt-3 text-2xl font-display font-semibold text-slate-900">
                                            Finish all <span x-text="englishMatchTotalPairs"></span> pairs.
                                        </p>
                                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-gradient-to-r from-amber-400 via-orange-500 to-sky-600" :style="`width:${englishMatchProgressPercent()}%`"></div>
                                        </div>
                                        <p class="mt-3 text-sm text-slate-600">Each correct match adds score and coins.</p>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border-[12px] border-amber-900 bg-[radial-gradient(circle_at_top,_rgba(34,197,94,0.16),_rgba(21,128,61,0.98)_22%,_rgba(20,83,45,0.98)_100%)] p-4 shadow-2xl md:p-6">
                                    <div class="rounded-[1.6rem] border border-white/10 bg-slate-950/20 p-4 md:p-6">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.24em] text-amber-100/70">Classroom board</p>
                                                <h3 class="mt-2 text-4xl font-display font-bold text-white">Match the Words!</h3>
                                            </div>
                                            <div class="rounded-2xl bg-slate-950/35 px-4 py-3 text-right text-white shadow-lg">
                                                <p class="text-xs uppercase tracking-[0.18em] text-amber-100/70">Score</p>
                                                <p class="mt-2 text-3xl font-black text-amber-300" x-text="score"></p>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_110px_minmax(0,1fr)] md:items-start">
                                            <div class="space-y-4">
                                                <template x-for="(pair, index) in englishMatchPairs" :key="`pair-${pair.id}`">
                                                    <button
                                                        type="button"
                                                        @click="englishSelectMatchPair(pair.id)"
                                                        :disabled="pair.matched"
                                                        :style="englishMatchPairStyle(pair)"
                                                        class="w-full rounded-[1.5rem] px-4 py-4 text-left transition duration-200 hover:-translate-y-0.5 disabled:cursor-default"
                                                    >
                                                        <div class="flex items-center justify-between gap-4">
                                                            <div>
                                                                <p class="text-xs uppercase tracking-[0.18em] opacity-70">Word</p>
                                                                <p class="mt-2 text-3xl font-black leading-tight" x-text="pair.palabra || 'Word'"></p>
                                                            </div>
                                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/80 text-sm font-black text-slate-800 shadow-sm">
                                                                <span x-text="pair.matched ? 'OK' : index + 1"></span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>

                                            <div class="flex flex-col items-center gap-4 py-2">
                                                <div class="rounded-[1.4rem] bg-white/12 px-4 py-4 text-center text-amber-50 shadow-inner">
                                                    <p class="text-xs uppercase tracking-[0.18em] text-white/70">How to play</p>
                                                    <p class="mt-3 text-sm font-semibold">1. Tap a word.</p>
                                                    <p class="text-sm font-semibold">2. Tap the matching card.</p>
                                                </div>

                                                <div class="hidden w-full gap-3 md:flex md:flex-col">
                                                    <template x-for="pair in englishMatchPairs" :key="`connector-${pair.id}`">
                                                        <div class="h-3 w-full rounded-full transition" :style="englishMatchConnectorStyle(pair)"></div>
                                                    </template>
                                                </div>
                                            </div>

                                            <div class="space-y-4">
                                                <template x-for="(option, index) in englishMatchOptions" :key="`option-${option.id}-${index}`">
                                                    <button
                                                        type="button"
                                                        @click="englishSelectMatchOption(option.id)"
                                                        :disabled="option.matched"
                                                        :style="englishMatchOptionStyle(option)"
                                                        class="flex w-full items-center justify-between gap-4 rounded-[1.5rem] px-4 py-4 text-left transition duration-200 hover:-translate-y-0.5 disabled:cursor-default"
                                                    >
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs uppercase tracking-[0.18em] opacity-70">Picture card</p>
                                                            <template x-if="englishMatchIsImage(option.pareja)">
                                                                <img :src="englishMatchImageSrc(option.pareja)" alt="Picture card" class="mt-3 h-20 w-full rounded-2xl object-contain bg-white/70 p-2 shadow-sm">
                                                            </template>
                                                            <template x-if="!englishMatchIsImage(option.pareja)">
                                                                <p class="mt-2 text-2xl font-black leading-tight" x-text="option.pareja || 'Card'"></p>
                                                            </template>
                                                        </div>
                                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/80 text-sm font-black text-slate-800 shadow-sm">
                                                            <span x-text="option.matched ? 'OK' : String.fromCharCode(65 + index)"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mt-6 rounded-[1.5rem] bg-white/12 px-5 py-4 text-center text-white shadow-inner">
                                            <p class="text-xs uppercase tracking-[0.18em] text-amber-100/70">Message</p>
                                            <p class="mt-2 text-2xl font-display font-semibold" x-text="englishMatchMessage || 'Choose a card to begin.'"></p>
                                        </div>

                                        <div x-show="englishMatchCelebration" x-cloak class="mt-6 rounded-[1.6rem] bg-gradient-to-r from-yellow-300 via-orange-400 to-rose-500 px-6 py-5 text-center shadow-xl">
                                            <p class="text-4xl font-display font-black text-slate-900">Great Job!</p>
                                            <p class="mt-2 text-lg font-semibold text-slate-900">You matched every word on the board.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-[1.6rem] bg-white/85 p-5 shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Board stats</p>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl bg-sky-50 px-4 py-3">
                                                <p class="text-xs uppercase tracking-[0.18em] text-sky-700">Matched</p>
                                                <p class="mt-2 text-2xl font-black text-sky-900"><span x-text="englishMatchFoundCount()"></span>/<span x-text="englishMatchTotalPairs"></span></p>
                                            </div>
                                            <div class="rounded-2xl bg-amber-50 px-4 py-3">
                                                <p class="text-xs uppercase tracking-[0.18em] text-amber-700">Coins</p>
                                                <p class="mt-2 text-2xl font-black text-amber-900" x-text="englishMatchCoins"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-[1.6rem] bg-slate-900 p-5 text-white shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.18em] text-cyan-300">Completed pairs</p>
                                        <div class="mt-4 space-y-3">
                                            <template x-if="englishMatchedPairs().length === 0">
                                                <p class="text-sm text-slate-300">No pairs matched yet.</p>
                                            </template>
                                            <template x-for="pair in englishMatchedPairs()" :key="`done-${pair.id}`">
                                                <div class="rounded-2xl px-4 py-3" :style="`background:${hexToRgba(pair.connectionColor, 0.16)}; border:1px solid ${pair.connectionColor};`">
                                                    <p class="text-sm font-semibold text-white" x-text="pair.palabra"></p>
                                                    <template x-if="englishMatchIsImage(pair.pareja)">
                                                        <img :src="englishMatchImageSrc(pair.pareja)" alt="Matched picture" class="mt-2 h-16 w-full rounded-xl object-contain bg-white/80 p-2">
                                                    </template>
                                                    <template x-if="!englishMatchIsImage(pair.pareja)">
                                                        <p class="mt-1 text-xs text-slate-200" x-text="pair.pareja"></p>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <template x-if="currentQuestionData">
                        <div>
                            @if($esMatematicaAventura)
                                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-cyan-700">Mision actual</p>
                                        <p class="mt-2 font-semibold text-cyan-950" x-text="goalLabel()"></p>
                                        <p class="mt-1 text-sm text-cyan-700" x-text="operationLabel()"></p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-emerald-700">Recompensa por acierto</p>
                                        <p class="mt-2 font-semibold text-emerald-950" x-text="rewardLabel()"></p>
                                        <p class="mt-1 text-sm text-emerald-700">Sigue resolviendo para acumular mas.</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Progreso</p>
                                            <p class="text-sm font-semibold text-slate-800" x-text="progressPercent() + '%' "></p>
                                        </div>
                                        <div class="mt-3 h-3 rounded-full bg-slate-200 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500" :style="`width:${progressPercent()}%`"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-6">
                                <h3 class="text-xl font-medium text-gray-800 mb-3" x-text="currentQuestionData.enunciado || currentQuestionData.pregunta || 'Pregunta'"></h3>
                                <template x-if="currentQuestionData.imagen_apoyo">
                                    <img :src="'/storage/preguntas/' + currentQuestionData.imagen_apoyo" class="max-w-md rounded-lg mb-3" alt="Imagen de apoyo">
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="currentQuestionData.opciones_normalizadas.length > 0">
                                <template x-for="(opcion, index) in currentQuestionData.opciones_normalizadas" :key="index">
                                    <button @click="selectAnswer(index)"
                                        :disabled="answerSubmitted"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600': selectedAnswer === index && !answerSubmitted,
                                            'bg-green-500 text-white border-green-500': answerSubmitted && isCorrect && selectedAnswer === index,
                                            'bg-red-500 text-white border-red-500': answerSubmitted && !isCorrect && selectedAnswer === index,
                                            'bg-white text-gray-700 border-gray-200 hover:border-blue-400': selectedAnswer !== index && !answerSubmitted,
                                            'opacity-50': answerSubmitted && selectedAnswer !== index
                                        }"
                                        class="p-4 border-2 rounded-xl text-left transition font-medium">
                                        <span x-text="opcion.texto"></span>
                                    </button>
                                </template>
                            </div>

                            <div x-show="currentQuestionData.opciones_normalizadas.length === 0" class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">Tu respuesta</label>
                                <input type="text"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :value="textAnswer"
                                    @input="textAnswer = $event.target.value"
                                    :disabled="answerSubmitted"
                                    placeholder="Escribe tu respuesta">
                            </div>

                            <div class="mt-8 text-center">
                                <button x-show="!answerSubmitted && canSubmit()" @click="submitAnswer()" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition font-medium">Confirmar respuesta</button>
                                <button x-show="answerSubmitted" @click="nextQuestion()" class="bg-purple-600 text-white px-8 py-3 rounded-xl hover:bg-purple-700 transition font-medium">Siguiente pregunta</button>
                            </div>

                            <div x-show="answerSubmitted" class="mt-6 text-center space-y-2">
                                <p x-show="isCorrect" class="text-green-600 font-medium text-lg">Correcto</p>
                                <p x-show="!isCorrect" class="text-red-600 font-medium text-lg">Incorrecto</p>
                                @if($esMatematicaAventura)
                                    <p x-show="isCorrect" class="text-cyan-700 font-medium" x-text="rewardFeedback()"></p>
                                @endif
                            </div>
                        </div>
                    </template>
                @endif
            </div>

            <div x-show="gameState === 'finished'" x-cloak class="text-center py-10">
                <div class="text-5xl mb-4">
                    @if($esMatematicaAventura)
                        ðŸ†
                    @elseif($esMemoria)
                        ðŸ§ 
                    @elseif($esOrdenarIngles)
                        ABC
                    @elseif($esSopaMatematica)
                        ðŸ’Ž
                    @else
                        ðŸŽ‰
                    @endif
                </div>
                <h2 class="mb-4 text-3xl font-bold text-gray-800">
                    @if($esMatematicaAventura)
                        Aventura completada
                    @elseif($esMemoria)
                        Memoria completada
                    @elseif($esOrdenarIngles)
                        Matching completado
                    @elseif($esSopa)
                        Sopa completada
                    @else
                        Juego completado
                    @endif
                </h2>

                <div class="mx-auto mb-6 max-w-4xl rounded-2xl bg-gray-50 p-6">
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div>
                            <p class="text-2xl font-bold text-purple-600" x-text="score"></p>
                            <p class="text-sm text-gray-500">Puntos</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600" x-text="correctAnswers"></p>
                            <p class="text-sm text-gray-500">{{ $esMemoria || $esOrdenarIngles ? 'Parejas' : ($esSopa ? 'Palabras' : 'Correctas') }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600" x-text="totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) + '%' : '0%'"></p>
                            <p class="text-sm text-gray-500">Precision</p>
                        </div>
                        @if($esMatematicaAventura)
                            <div>
                                <p class="text-2xl font-bold text-amber-500" x-text="coinsEarned"></p>
                                <p class="text-sm text-gray-500">Monedas</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-emerald-600" x-text="energyEarned"></p>
                                <p class="text-sm text-gray-500">Energia</p>
                            </div>
                        @elseif($esMemoria)
                            <div>
                                <p class="text-2xl font-bold text-amber-500" x-text="memoryCoins"></p>
                                <p class="text-sm text-gray-500">Monedas</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-rose-500" x-text="memoryLives"></p>
                                <p class="text-sm text-gray-500">Vidas</p>
                            </div>
                        @elseif($esSopaMatematica)
                            <div>
                                <p class="text-2xl font-bold text-amber-500" x-text="wordSearchCoins"></p>
                                <p class="text-sm text-gray-500">Monedas</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-sky-600" x-text="wordSearchTotalWords"></p>
                                <p class="text-sm text-gray-500">Objetivos</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <a href="{{ route('estudiante.temas.show', $juego->tema) }}" class="rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition hover:bg-gray-300">Volver al tema</a>
                    <button @click="restartGame()" class="rounded-xl bg-purple-600 px-6 py-3 font-medium text-white transition hover:bg-purple-700">
                        @if($esMatematicaAventura)
                            Repetir aventura
                        @elseif($esMemoria)
                            Jugar memoria otra vez
                        @elseif($esOrdenarIngles)
                            Jugar matching otra vez
                        @elseif($esSopa)
                            Jugar sopa otra vez
                        @else
                            Jugar de nuevo
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function gameEngine() {
    return {
        gameState: 'start',
        gameType: @json($juego->tipo),
        subjectSlug: @json(optional($juego->tema->asignatura)->slug),
        adventureConfig: @json($config),
        gameData: @json($gameData),
        currentQuestion: 0,
        totalQuestions: {{ $preguntas->count() }},
        questions: @json($preguntas->values()),
        currentQuestionData: null,
        selectedAnswer: null,
        textAnswer: '',
        answerSubmitted: false,
        isCorrect: false,
        score: 0,
        correctAnswers: 0,
        coinsEarned: 0,
        energyEarned: 0,
        timeLeft: {{ $juego->tiempo_limite_segundos ?? 0 }},
        timer: null,
        responses: {},
        startedAt: null,
        memoryCards: [],
        memoryFlippedCardIds: [],
        memoryMatchedPairs: 0,
        memoryTotalPairs: {{ $preguntas->count() }},
        memoryLives: 3,
        memoryCoins: 0,
        memoryLockBoard: false,
        memoryCelebration: '',
        englishSentences: [],
        englishWordBank: [],
        englishSentenceCount: {{ count($gameData['frases'] ?? []) }},
        englishActiveSentenceId: null,
        englishWorksheetMessage: '',
        englishCorrectIds: [],
        englishWordPalette: ['#22C55E', '#F59E0B', '#3B82F6', '#EF4444', '#8B5CF6', '#10B981', '#EC4899', '#F97316'],
        englishMatchPairs: [],
        englishMatchOptions: [],
        englishMatchSelectedPairId: null,
        englishMatchSelectedOptionId: null,
        englishMatchTotalPairs: {{ count($gameData['parejas'] ?? []) }},
        englishMatchMessage: '',
        englishMatchCoins: 0,
        englishMatchCelebration: false,
        englishMatchColorPalette: ['#F97316', '#3B82F6', '#10B981', '#EC4899', '#FACC15', '#8B5CF6', '#14B8A6', '#EF4444'],
        wordSearchTheme: @json($esSopaMatematica ? 'math' : ($esSopa ? 'default' : null)),
        wordSearchGrid: [],
        wordSearchWords: [],
        wordSearchTotalWords: {{ count($gameData['palabras'] ?? []) }},
        wordSearchSelectionActive: false,
        wordSearchSelectionStart: null,
        wordSearchSelectionPath: [],
        wordSearchFoundCells: {},
        wordSearchCoins: 0,
        wordSearchMessage: '',
        wordSearchPointerListenerAttached: false,
        wordSearchColorPalette: ['#7DD3FC', '#A3E635', '#FBBF24', '#FB7185', '#C084FC', '#F97316', '#34D399', '#F59E0B'],
        isSavingResult: false,

        initGame() {
            if (this.isMemory()) {
                this.initMemoryDeck();
                return;
            }

            if (this.isEnglishWorksheet()) {
                this.initEnglishWorksheet();
                return;
            }

            if (this.isEnglishMatchBoard()) {
                this.initEnglishMatchBoard();
                return;
            }

            if (this.isWordSearch()) {
                if (!this.wordSearchPointerListenerAttached) {
                    window.addEventListener('pointerup', () => this.finishWordSearchSelection());
                    window.addEventListener('pointercancel', () => this.finishWordSearchSelection());
                    this.wordSearchPointerListenerAttached = true;
                }
                this.initWordSearch();
                return;
            }

            this.questions = this.questions.map((p) => {
                let opciones = p.opciones ?? [];
                if (typeof opciones === 'string') {
                    try { opciones = JSON.parse(opciones); } catch (_) { opciones = []; }
                }
                if (!Array.isArray(opciones)) opciones = [];

                if ((p.tipo === 'verdadero_falso' || p.tipo === 'vf') && opciones.length === 0) {
                    opciones = ['Verdadero', 'Falso'];
                }

                const opciones_normalizadas = opciones
                    .filter(o => o !== null && o !== undefined && String(o).trim() !== '')
                    .map((o, i) => {
                        if (typeof o === 'object') {
                            return {
                                texto: o.texto ?? o.label ?? o.value ?? o.valor ?? `Opcion ${i + 1}`,
                                valor: String(o.value ?? o.valor ?? o.texto ?? o.label ?? i),
                            };
                        }
                        return { texto: String(o), valor: String(o) };
                    });

                return { ...p, opciones_normalizadas };
            });
        },

        isAdventure() {
            return this.gameType === 'matematica_aventura';
        },

        isMemory() {
            return this.gameType === 'memoria';
        },

        isEnglishWorksheet() {
            return this.gameType === 'completar' && this.subjectSlug === 'ingles';
        },

        isEnglishMatchBoard() {
            return this.gameType === 'ordenar' && this.subjectSlug === 'ingles';
        },

        isWordSearch() {
            return this.gameType === 'sopa';
        },

        isMathWordSearch() {
            return this.wordSearchTheme === 'math';
        },

        initMemoryDeck() {
            const cards = Array.isArray(this.gameData.cartas) ? this.gameData.cartas : [];
            this.memoryCards = cards.map((card) => ({
                ...card,
                flipped: false,
                matched: false,
            }));
            this.memoryTotalPairs = Number(this.gameData.total_pares || 0);
        },

        resetMemoryState() {
            this.memoryFlippedCardIds = [];
            this.memoryMatchedPairs = 0;
            this.memoryLives = 3;
            this.memoryCoins = 0;
            this.memoryLockBoard = false;
            this.memoryCelebration = '';
            this.initMemoryDeck();
        },

        memoryCardsForDisplay() {
            return this.memoryCards;
        },

        memoryProgressPercent() {
            if (this.memoryTotalPairs === 0) return 0;
            return Math.round((this.memoryMatchedPairs / this.memoryTotalPairs) * 100);
        },

        initEnglishWorksheet() {
            const frases = Array.isArray(this.gameData.frases) ? this.gameData.frases : [];
            const palette = this.englishWordPalette;

            this.englishSentences = frases.map((frase, index) => {
                const parts = this.splitEnglishSentence(frase?.enunciado ?? '');
                return {
                    id: frase.id,
                    before: parts.before,
                    after: parts.after,
                    enunciado: frase?.enunciado ?? '',
                    respuesta_correcta: frase?.respuesta ?? '',
                    puntaje: Number(frase?.puntaje || 10),
                    userAnswer: '',
                    wordId: null,
                    status: 'idle',
                    color: palette[index % palette.length],
                };
            });

            this.englishSentenceCount = this.englishSentences.length;
            this.totalQuestions = this.englishSentenceCount;
            this.englishWordBank = this.shuffleArray(this.englishSentences.map((sentence, index) => ({
                id: `word-${sentence.id}-${index}`,
                text: sentence.respuesta_correcta,
                usedBy: null,
                color: sentence.color,
            })));
            this.englishWorksheetMessage = 'Choose a sentence and then pick a word from the bank.';
        },

        resetEnglishWorksheetState() {
            this.englishActiveSentenceId = null;
            this.englishWorksheetMessage = 'Choose a sentence and then pick a word from the bank.';
            this.englishCorrectIds = [];
            this.englishSentences = this.englishSentences.map((sentence) => ({
                ...sentence,
                userAnswer: '',
                wordId: null,
                status: 'idle',
            }));
            this.englishWordBank = this.shuffleArray(this.englishWordBank.map((word) => ({
                ...word,
                usedBy: null,
            })));
            this.englishSentenceCount = this.englishSentences.length;
        },

        splitEnglishSentence(text) {
            const sentence = String(text ?? '');
            const match = sentence.match(/^(.*?)(_{2,}|\.{3,})(.*)$/);

            if (match) {
                return {
                    before: match[1],
                    after: match[3],
                };
            }

            return {
                before: sentence,
                after: '',
            };
        },

        shuffleArray(items) {
            const copy = Array.isArray(items) ? [...items] : [];
            for (let index = copy.length - 1; index > 0; index--) {
                const swapIndex = Math.floor(Math.random() * (index + 1));
                [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
            }
            return copy;
        },

        englishFilledCount() {
            return this.englishSentences.filter((sentence) => this.normalize(sentence.userAnswer) !== '').length;
        },

        englishCorrectCount() {
            return this.englishCorrectIds.length;
        },

        englishAvailableWordsCount() {
            return this.englishWordBank.filter((word) => !word.usedBy).length;
        },

        englishSelectSentence(sentenceId) {
            const sentence = this.englishSentences.find((item) => item.id === sentenceId);
            if (!sentence || sentence.status === 'correct') return;

            if (this.englishActiveSentenceId === sentenceId && sentence.userAnswer) {
                this.englishClearSentence(sentenceId);
                return;
            }

            this.englishActiveSentenceId = sentenceId;
            this.englishWorksheetMessage = 'Now choose a word from the bank.';
        },

        englishAssignWord(wordId) {
            const word = this.englishWordBank.find((item) => item.id === wordId);
            if (!word || word.usedBy) return;

            const targetId = this.englishActiveSentenceId
                ?? this.englishSentences.find((sentence) => sentence.status !== 'correct' && !sentence.userAnswer)?.id;

            if (!targetId) return;

            const sentence = this.englishSentences.find((item) => item.id === targetId);
            if (!sentence || sentence.status === 'correct') return;

            if (sentence.wordId) {
                const previousWord = this.englishWordBank.find((item) => item.id === sentence.wordId);
                if (previousWord) previousWord.usedBy = null;
            }

            sentence.userAnswer = word.text;
            sentence.wordId = word.id;
            sentence.status = 'filled';
            word.usedBy = sentence.id;
            this.englishActiveSentenceId = sentence.id;
            this.englishWorksheetMessage = 'Great. Continue filling the worksheet or check your answers.';
        },

        englishClearSentence(sentenceId) {
            const sentence = this.englishSentences.find((item) => item.id === sentenceId);
            if (!sentence || sentence.status === 'correct') return;

            if (sentence.wordId) {
                const word = this.englishWordBank.find((item) => item.id === sentence.wordId);
                if (word) word.usedBy = null;
            }

            sentence.userAnswer = '';
            sentence.wordId = null;
            sentence.status = 'idle';
            this.englishActiveSentenceId = sentence.id;
            this.englishWorksheetMessage = 'Pick another word for this sentence.';
        },

        englishCanClearActiveSentence() {
            const sentence = this.englishSentences.find((item) => item.id === this.englishActiveSentenceId);
            return !!sentence && sentence.status !== 'correct' && !!sentence.userAnswer;
        },

        englishClearActiveSentence() {
            if (!this.englishCanClearActiveSentence()) return;
            this.englishClearSentence(this.englishActiveSentenceId);
        },

        englishWordStyle(word) {
            const shadow = word.usedBy ? 'rgba(148, 163, 184, 0.18)' : this.hexToRgba(word.color, 0.28);
            const background = word.usedBy ? '#94A3B8' : word.color;
            return `background:${background}; box-shadow:0 10px 22px ${shadow};`;
        },

        canSubmitEnglishWorksheet() {
            return this.englishFilledCount() > 0;
        },

        submitEnglishWorksheet() {
            if (!this.canSubmitEnglishWorksheet()) return;

            let allCorrect = true;

            this.englishSentences.forEach((sentence) => {
                const responseValue = sentence.userAnswer;
                this.responses[sentence.id] = responseValue;

                if (!responseValue) {
                    sentence.status = 'idle';
                    allCorrect = false;
                    return;
                }

                const correct = this.isCorrectAnswer(sentence, responseValue);
                sentence.status = correct ? 'correct' : 'incorrect';

                if (correct) {
                    if (!this.englishCorrectIds.includes(sentence.id)) {
                        this.englishCorrectIds.push(sentence.id);
                        this.correctAnswers++;
                        this.score += Number(sentence.puntaje || 10);
                    }
                } else {
                    allCorrect = false;
                }
            });

            if (this.englishCorrectIds.length < this.englishSentenceCount) {
                allCorrect = false;
            }

            this.englishWorksheetMessage = allCorrect
                ? 'Excellent! You completed the worksheet.'
                : 'Some answers need another try. Tap a sentence to change its word.';

            if (allCorrect) {
                setTimeout(() => this.finishGame(), 800);
            }
        },

        initEnglishMatchBoard() {
            const pairs = Array.isArray(this.gameData.parejas) ? this.gameData.parejas : [];
            const options = Array.isArray(this.gameData.opciones) && this.gameData.opciones.length > 0
                ? this.gameData.opciones
                : pairs;

            this.englishMatchPairs = pairs.map((pair, index) => ({
                id: pair?.id ?? `pair-${index}`,
                palabra: String(pair?.palabra ?? ''),
                pareja: String(pair?.pareja ?? ''),
                puntaje: Number(pair?.puntaje || 10),
                matched: false,
                selected: false,
                optionId: null,
                connectionColor: this.englishMatchColorPalette[index % this.englishMatchColorPalette.length],
            }));

            this.englishMatchOptions = this.shuffleArray(options.map((option, index) => ({
                id: option?.id ?? `option-${index}`,
                palabra: String(option?.palabra ?? ''),
                pareja: String(option?.pareja ?? ''),
                puntaje: Number(option?.puntaje || 10),
                matched: false,
                selected: false,
                pairId: null,
            })));

            this.englishMatchTotalPairs = this.englishMatchPairs.length;
            this.totalQuestions = this.englishMatchTotalPairs;
            this.englishMatchMessage = this.englishMatchTotalPairs > 0
                ? 'Choose a word card and then its matching picture card.'
                : 'This board has no pairs yet.';
        },

        resetEnglishMatchBoardState() {
            this.englishMatchSelectedPairId = null;
            this.englishMatchSelectedOptionId = null;
            this.englishMatchCoins = 0;
            this.englishMatchCelebration = false;
            this.englishMatchMessage = this.englishMatchTotalPairs > 0
                ? 'Choose a word card and then its matching picture card.'
                : 'This board has no pairs yet.';

            this.englishMatchPairs = this.englishMatchPairs.map((pair) => ({
                ...pair,
                matched: false,
                selected: false,
                optionId: null,
            }));

            this.englishMatchOptions = this.shuffleArray(this.englishMatchOptions.map((option) => ({
                ...option,
                matched: false,
                selected: false,
                pairId: null,
            })));

            this.englishMatchTotalPairs = this.englishMatchPairs.length;
        },

        englishMatchFoundCount() {
            return this.englishMatchPairs.filter((pair) => pair.matched).length;
        },

        englishMatchRemaining() {
            return Math.max(0, this.englishMatchTotalPairs - this.englishMatchFoundCount());
        },

        englishMatchProgressPercent() {
            if (this.englishMatchTotalPairs === 0) return 0;
            return Math.round((this.englishMatchFoundCount() / this.englishMatchTotalPairs) * 100);
        },

        englishMatchedPairs() {
            return this.englishMatchPairs.filter((pair) => pair.matched);
        },

        englishMatchSyncSelection() {
            this.englishMatchPairs = this.englishMatchPairs.map((pair) => ({
                ...pair,
                selected: String(pair.id) === String(this.englishMatchSelectedPairId),
            }));

            this.englishMatchOptions = this.englishMatchOptions.map((option) => ({
                ...option,
                selected: String(option.id) === String(this.englishMatchSelectedOptionId),
            }));
        },

        englishSelectMatchPair(pairId) {
            const pair = this.englishMatchPairs.find((item) => String(item.id) === String(pairId));
            if (!pair || pair.matched) return;

            this.englishMatchSelectedPairId = String(this.englishMatchSelectedPairId) === String(pairId)
                ? null
                : pairId;

            this.englishMatchSyncSelection();

            if (this.englishMatchSelectedPairId && this.englishMatchSelectedOptionId) {
                this.englishAttemptMatch();
                return;
            }

            this.englishMatchMessage = this.englishMatchSelectedPairId
                ? 'Good. Now choose the matching picture card.'
                : 'Choose a word card to continue.';
        },

        englishSelectMatchOption(optionId) {
            const option = this.englishMatchOptions.find((item) => String(item.id) === String(optionId));
            if (!option || option.matched) return;

            this.englishMatchSelectedOptionId = String(this.englishMatchSelectedOptionId) === String(optionId)
                ? null
                : optionId;

            this.englishMatchSyncSelection();

            if (this.englishMatchSelectedPairId && this.englishMatchSelectedOptionId) {
                this.englishAttemptMatch();
                return;
            }

            this.englishMatchMessage = this.englishMatchSelectedOptionId
                ? 'Great. Now choose the English word.'
                : 'Choose a picture card to continue.';
        },

        englishAttemptMatch() {
            const pair = this.englishMatchPairs.find((item) => String(item.id) === String(this.englishMatchSelectedPairId));
            const option = this.englishMatchOptions.find((item) => String(item.id) === String(this.englishMatchSelectedOptionId));

            if (!pair || !option || pair.matched || option.matched) return;

            const correct = String(pair.id) === String(option.id)
                || this.normalize(pair.pareja) === this.normalize(option.pareja);

            if (!correct) {
                this.englishMatchSelectedOptionId = null;
                this.englishMatchSyncSelection();
                this.englishMatchMessage = 'Not this one. Try another picture card.';
                return;
            }

            pair.matched = true;
            pair.selected = false;
            pair.optionId = option.id;

            option.matched = true;
            option.selected = false;
            option.pairId = pair.id;

            this.responses[pair.id] = option.pareja;
            this.correctAnswers++;
            this.score += Number(pair.puntaje || 10);
            this.englishMatchCoins += Math.max(5, Number(pair.puntaje || 10));
            this.englishMatchSelectedPairId = null;
            this.englishMatchSelectedOptionId = null;
            this.englishMatchSyncSelection();

            if (this.englishMatchFoundCount() >= this.englishMatchTotalPairs) {
                this.englishMatchCelebration = true;
                this.englishMatchMessage = 'Great Job! You matched every word.';
                setTimeout(() => this.finishGame(), 1100);
                return;
            }

            this.englishMatchMessage = `${pair.palabra} is matched correctly. Keep going.`;
        },

        englishMatchPairStyle(pair) {
            const color = pair?.connectionColor || '#38BDF8';

            if (pair?.matched) {
                return `background:linear-gradient(135deg, ${this.hexToRgba(color, 0.26)}, #fff7ed); border:1px solid ${color}; box-shadow:0 18px 34px ${this.hexToRgba(color, 0.24)}; color:#0f172a;`;
            }

            if (pair?.selected) {
                return `background:#eff6ff; border:1px solid ${color}; box-shadow:0 16px 30px ${this.hexToRgba(color, 0.20)}; color:#0f172a;`;
            }

            return 'background:linear-gradient(135deg, #fff7ed, #fffbeb); border:1px solid rgba(251,191,36,0.34); box-shadow:0 14px 30px rgba(120,53,15,0.10); color:#0f172a;';
        },

        englishMatchOptionStyle(option) {
            const matchedPair = this.englishMatchPairs.find((pair) => String(pair.id) === String(option?.pairId));
            const color = matchedPair?.connectionColor || '#38BDF8';

            if (option?.matched) {
                return `background:linear-gradient(135deg, ${this.hexToRgba(color, 0.22)}, #ffffff); border:1px solid ${color}; box-shadow:0 18px 34px ${this.hexToRgba(color, 0.22)}; color:#0f172a;`;
            }

            if (option?.selected) {
                return 'background:#eff6ff; border:1px solid #38bdf8; box-shadow:0 16px 30px rgba(56,189,248,0.18); color:#0f172a;';
            }

            return 'background:linear-gradient(135deg, #fffdf5, #ffffff); border:1px solid rgba(226,232,240,0.95); box-shadow:0 14px 30px rgba(15,23,42,0.08); color:#0f172a;';
        },

        englishMatchConnectorStyle(pair) {
            if (pair?.matched) {
                return `background:linear-gradient(90deg, ${this.hexToRgba(pair.connectionColor, 0.45)}, ${pair.connectionColor}); box-shadow:0 8px 20px ${this.hexToRgba(pair.connectionColor, 0.24)};`;
            }

            if (pair?.selected) {
                return 'background:linear-gradient(90deg, #bae6fd, #38bdf8); box-shadow:0 8px 18px rgba(56,189,248,0.18);';
            }

            return 'background:rgba(255,255,255,0.16);';
        },

        englishMatchIsImage(value) {
            const text = String(value ?? '').trim();
            if (!text) return false;

            return /^(https?:\/\/|\/|storage\/).+\.(png|jpe?g|gif|webp|svg)$/i.test(text)
                || /^(https?:\/\/).+/i.test(text);
        },

        englishMatchImageSrc(value) {
            const text = String(value ?? '').trim();
            if (!text) return '';
            if (/^https?:\/\//i.test(text) || text.startsWith('/')) return text;
            if (text.startsWith('storage/')) return `/${text}`;
            return text;
        },

        initWordSearch() {
            const grid = Array.isArray(this.gameData.grid) ? this.gameData.grid : [];
            const words = Array.isArray(this.gameData.palabras) ? this.gameData.palabras : [];

            this.wordSearchGrid = grid.map((row) => Array.isArray(row)
                ? row.map((cell) => String(cell ?? '').toUpperCase())
                : []);

            this.wordSearchWords = words.map((word, index) => ({
                ...word,
                palabra: String(word?.palabra ?? '').toUpperCase(),
                pista: String(word?.pista ?? ''),
                puntaje: Number(word?.puntaje || 10),
                found: false,
                color: this.wordSearchColorPalette[index % this.wordSearchColorPalette.length],
            }));

            this.wordSearchTotalWords = this.wordSearchWords.length;
            this.totalQuestions = this.wordSearchTotalWords;
            this.wordSearchMessage = this.isMathWordSearch()
                ? 'Arrastra sobre el tablero y luego pulsa verificar.'
                : 'Selecciona una palabra y confirma con verificar.';
        },

        resetWordSearchState() {
            this.wordSearchSelectionActive = false;
            this.wordSearchSelectionStart = null;
            this.wordSearchSelectionPath = [];
            this.wordSearchFoundCells = {};
            this.wordSearchCoins = 0;
            this.wordSearchMessage = this.isMathWordSearch()
                ? 'Arrastra sobre el tablero y luego pulsa verificar.'
                : 'Selecciona una palabra y confirma con verificar.';

            this.wordSearchWords = this.wordSearchWords.map((word) => ({
                ...word,
                found: false,
            }));
            this.wordSearchTotalWords = this.wordSearchWords.length;
        },

        wordSearchFoundCount() {
            return this.wordSearchWords.filter((word) => word.found).length;
        },

        wordSearchRemaining() {
            return Math.max(0, this.wordSearchTotalWords - this.wordSearchFoundCount());
        },

        wordSearchProgressPercent() {
            if (this.wordSearchTotalWords === 0) return 0;
            return Math.round((this.wordSearchFoundCount() / this.wordSearchTotalWords) * 100);
        },

        wordSearchSelectionLabel() {
            const letters = this.wordSearchLettersFromPath(this.wordSearchSelectionPath);
            if (letters) return letters;
            return this.isMathWordSearch() ? 'Traza una palabra del tablero' : 'Selecciona una palabra del tablero';
        },

        canVerifyWordSearch() {
            return this.wordSearchSelectionPath.length > 0;
        },

        wordSearchGridStyle() {
            const columns = this.wordSearchGrid[0]?.length || Number(this.gameData.tamano || 1) || 1;
            const gap = columns >= 14 ? '0.22rem' : columns >= 11 ? '0.32rem' : '0.5rem';
            return `grid-template-columns: repeat(${columns}, minmax(0, 1fr)); gap:${gap};`;
        },

        wordSearchCellTypographyStyle() {
            const columns = this.wordSearchGrid[0]?.length || Number(this.gameData.tamano || 1) || 1;

            if (columns >= 15) return 'font-size:0.88rem; line-height:1;';
            if (columns >= 13) return 'font-size:0.98rem; line-height:1;';
            if (columns >= 11) return 'font-size:1.08rem; line-height:1;';
            if (columns >= 9) return 'font-size:1.18rem; line-height:1;';

            return 'font-size:1.35rem; line-height:1;';
        },

        wordSearchCellKey(row, col) {
            return `${row}-${col}`;
        },

        wordSearchLettersFromPath(path) {
            if (!Array.isArray(path) || path.length === 0) return '';
            return path
                .map((cell) => this.wordSearchGrid[cell.row]?.[cell.col] ?? '')
                .join('');
        },

        wordSearchFindWord(wordId) {
            return this.wordSearchWords.find((word) => Number(word.id) === Number(wordId));
        },

        hexToRgba(hex, alpha) {
            const normalized = String(hex || '').replace('#', '');
            if (normalized.length !== 6) {
                return `rgba(148, 163, 184, ${alpha})`;
            }

            const r = parseInt(normalized.slice(0, 2), 16);
            const g = parseInt(normalized.slice(2, 4), 16);
            const b = parseInt(normalized.slice(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        },

        wordSearchClueStyle(index, wordId) {
            const word = this.wordSearchFindWord(wordId);
            const color = word?.color || this.wordSearchColorPalette[index % this.wordSearchColorPalette.length];

            if (word?.found) {
                return `background:${this.hexToRgba(color, this.isMathWordSearch() ? 0.88 : 0.18)}; color:${this.isMathWordSearch() ? '#0f172a' : '#0f172a'}; border:1px solid ${this.hexToRgba(color, 0.92)}; box-shadow:0 14px 30px ${this.hexToRgba(color, 0.22)};`;
            }

            if (this.isMathWordSearch()) {
                return 'background:rgba(255,255,255,0.1); color:#f8fafc; border:1px solid rgba(255,255,255,0.08); box-shadow:0 14px 32px rgba(15,23,42,0.12);';
            }

            return 'background:#f8fafc; color:#0f172a; border:1px solid rgba(186,230,253,0.95); box-shadow:0 14px 30px rgba(14,165,233,0.08);';
        },

        wordSearchBadgeStyle(index, wordId) {
            const word = this.wordSearchFindWord(wordId);
            const color = word?.color || this.wordSearchColorPalette[index % this.wordSearchColorPalette.length];

            if (word?.found) {
                return `background:${this.hexToRgba(color, this.isMathWordSearch() ? 0.9 : 0.2)}; color:${this.isMathWordSearch() ? '#082f49' : '#0f172a'}; border:1px solid ${this.hexToRgba(color, 0.92)}; box-shadow:0 12px 26px ${this.hexToRgba(color, 0.18)};`;
            }

            if (this.isMathWordSearch()) {
                return 'background:rgba(255,255,255,0.12); color:#f8fafc; border:1px solid rgba(255,255,255,0.08);';
            }

            return 'background:#eff6ff; color:#0f172a; border:1px solid rgba(186,230,253,0.95);';
        },

        wordSearchCellStyle(row, col) {
            const key = this.wordSearchCellKey(row, col);
            const isPreview = this.wordSearchSelectionPath.some((cell) => cell.row === row && cell.col === col);
            const foundColor = this.wordSearchFoundCells[key];
            const typography = this.wordSearchCellTypographyStyle();

            if (isPreview) {
                return this.isMathWordSearch()
                    ? `background:linear-gradient(135deg, #fef08a, #fb923c); color:#7c2d12; border-color:rgba(245,158,11,0.95); box-shadow:0 8px 18px rgba(245,158,11,0.2); ${typography}`
                    : `background:linear-gradient(135deg, #bae6fd, #60a5fa); color:#0f172a; border-color:#38bdf8; box-shadow:0 8px 18px rgba(14,165,233,0.14); ${typography}`;
            }

            if (foundColor) {
                return `background:${this.hexToRgba(foundColor, this.isMathWordSearch() ? 0.32 : 0.22)}; color:#0f172a; border-color:${foundColor}; box-shadow:inset 0 0 0 1px ${this.hexToRgba(foundColor, 0.55)}; ${typography}`;
            }

            return this.isMathWordSearch()
                ? `background:rgba(255,248,220,0.94); color:#111827; border-color:rgba(180,83,9,0.16); box-shadow:0 6px 14px rgba(120,53,15,0.06); ${typography}`
                : `background:#ffffff; color:#0f172a; border-color:#dbeafe; box-shadow:0 6px 14px rgba(14,165,233,0.06); ${typography}`;
        },

        buildWordSearchPath(start, end) {
            if (!start || !end) return [];

            const deltaRow = end.row - start.row;
            const deltaCol = end.col - start.col;
            const absRow = Math.abs(deltaRow);
            const absCol = Math.abs(deltaCol);
            const sameCell = absRow === 0 && absCol === 0;
            const validLine = sameCell || deltaRow === 0 || deltaCol === 0 || absRow === absCol;

            if (!validLine) return [];

            const stepRow = Math.sign(deltaRow);
            const stepCol = Math.sign(deltaCol);
            const length = Math.max(absRow, absCol) + 1;
            const path = [];

            for (let index = 0; index < length; index++) {
                const row = start.row + (stepRow * index);
                const col = start.col + (stepCol * index);

                if (!this.wordSearchGrid[row] || this.wordSearchGrid[row][col] === undefined) {
                    return [];
                }

                path.push({
                    row,
                    col,
                    key: this.wordSearchCellKey(row, col),
                });
            }

            return path;
        },

        startWordSearchSelection(row, col) {
            if (!this.isWordSearch() || this.gameState !== 'playing') return;

            this.wordSearchSelectionActive = true;
            this.wordSearchSelectionStart = { row, col };
            this.wordSearchSelectionPath = this.buildWordSearchPath(this.wordSearchSelectionStart, { row, col });
            this.wordSearchMessage = this.isMathWordSearch()
                ? 'Suelta y pulsa verificar para confirmar el resultado.'
                : 'Suelta y pulsa verificar para confirmar la palabra.';
        },

        updateWordSearchSelection(row, col) {
            if (!this.wordSearchSelectionActive || !this.wordSearchSelectionStart) return;

            const path = this.buildWordSearchPath(this.wordSearchSelectionStart, { row, col });
            if (path.length > 0) {
                this.wordSearchSelectionPath = path;
            }
        },

        trackWordSearchPointer(event) {
            if (!this.wordSearchSelectionActive) return;

            const target = document.elementFromPoint(event.clientX, event.clientY);
            const cell = target?.closest?.('[data-word-cell="true"]');
            if (!cell) return;

            const row = Number(cell.dataset.row);
            const col = Number(cell.dataset.col);

            if (Number.isNaN(row) || Number.isNaN(col)) return;
            this.updateWordSearchSelection(row, col);
        },

        finishWordSearchSelection() {
            if (!this.wordSearchSelectionActive) return;
            this.wordSearchSelectionActive = false;
        },

        clearWordSearchSelection() {
            this.wordSearchSelectionActive = false;
            this.wordSearchSelectionStart = null;
            this.wordSearchSelectionPath = [];
        },

        verifyWordSearchSelection() {
            if (!this.canVerifyWordSearch()) return;

            const attempt = this.wordSearchLettersFromPath(this.wordSearchSelectionPath);
            const reversedAttempt = attempt.split('').reverse().join('');
            const existingWord = this.wordSearchWords.find((word) => word.palabra === attempt || word.palabra === reversedAttempt);

            if (existingWord?.found) {
                this.wordSearchMessage = this.isMathWordSearch()
                    ? `${existingWord.palabra} ya fue descubierta.`
                    : `${existingWord.palabra} ya fue encontrada.`;
                this.clearWordSearchSelection();
                return;
            }

            const matchedWord = this.wordSearchWords.find((word) => !word.found && (word.palabra === attempt || word.palabra === reversedAttempt));

            if (!matchedWord) {
                this.wordSearchMessage = this.isMathWordSearch()
                    ? 'Esa ruta no coincide con un resultado pendiente.'
                    : 'Esa ruta no coincide con una palabra pendiente.';
                this.clearWordSearchSelection();
                return;
            }

            this.wordSearchSelectionPath.forEach((cell) => {
                this.wordSearchFoundCells[cell.key] = matchedWord.color;
            });

            matchedWord.found = true;
            this.responses[matchedWord.id] = matchedWord.palabra;
            this.correctAnswers++;
            this.score += Number(matchedWord.puntaje || 10);
            this.wordSearchCoins += Math.max(5, Number(matchedWord.puntaje || 10));

            this.wordSearchMessage = this.isMathWordSearch()
                ? `Encontraste ${matchedWord.palabra}. Sigue asi.`
                : `Encontraste ${matchedWord.palabra}.`;

            this.clearWordSearchSelection();

            if (this.wordSearchFoundCount() >= this.wordSearchTotalWords) {
                this.wordSearchMessage = this.isMathWordSearch()
                    ? 'Tesoro completo. Encontraste todas las palabras.'
                    : 'Excelente. Encontraste todas las palabras.';
                setTimeout(() => this.finishGame(), 700);
            }
        },

        goalLabel() {
            const labels = {
                puente: 'Cruza el puente numerico',
                cofre: 'Abre el cofre del tesoro',
                obstaculo: 'Vence el obstaculo del camino',
            };

            return labels[this.adventureConfig.objetivo_aventura] ?? labels.puente;
        },

        operationLabel() {
            const labels = {
                suma: 'Sumas',
                resta: 'Restas',
                multiplicacion: 'Multiplicaciones',
                division: 'Divisiones',
                mixto: 'Mixto',
            };

            return labels[this.adventureConfig.operacion_principal] ?? labels.mixto;
        },

        rewardLabel() {
            const monedas = Number(this.adventureConfig.monedas_por_acierto || 0);
            const energia = Number(this.adventureConfig.energia_por_acierto || 0);
            const reward = this.adventureConfig.recompensa_principal || 'monedas';

            if (reward === 'energia') {
                return `+${energia} energia`;
            }

            if (reward === 'ambas') {
                return `+${monedas} monedas y +${energia} energia`;
            }

            return `+${monedas} monedas`;
        },

        rewardFeedback() {
            if (!this.isAdventure()) return '';
            return `Ganaste ${this.rewardLabel()}`;
        },

        progressPercent() {
            if (this.totalQuestions === 0) return 0;
            const answered = this.currentQuestion + (this.answerSubmitted ? 1 : 0);
            return Math.round((answered / this.totalQuestions) * 100);
        },

        resetAdventureState() {
            this.coinsEarned = 0;
            this.energyEarned = 0;
        },

        applyAdventureReward() {
            if (!this.isAdventure()) return;

            const reward = this.adventureConfig.recompensa_principal || 'monedas';
            const monedas = Number(this.adventureConfig.monedas_por_acierto || 0);
            const energia = Number(this.adventureConfig.energia_por_acierto || 0);

            if (reward === 'monedas' || reward === 'ambas') {
                this.coinsEarned += monedas;
            }

            if (reward === 'energia' || reward === 'ambas') {
                this.energyEarned += energia;
            }
        },

        applyMemoryReward(card) {
            const puntos = Number(card?.puntaje || 10);
            this.score += puntos;
            this.correctAnswers++;
            this.memoryMatchedPairs++;
            this.memoryCoins += Math.max(5, puntos);

            if (this.memoryMatchedPairs >= this.memoryTotalPairs) {
                this.memoryCelebration = 'Tesoro completo';
                setTimeout(() => this.finishGame(), 700);
            } else {
                this.memoryCelebration = 'Muy bien';
            }
        },

        recordMemoryMatch(card) {
            if (!card || !card.pregunta_id) return;
            this.responses[card.pregunta_id] = card.respuesta ?? card.contenido ?? '';
        },

        flipMemoryCard(cardId) {
            if (!this.isMemory() || this.memoryLockBoard) return;

            const card = this.memoryCards.find((item) => item.id === cardId);
            if (!card || card.flipped || card.matched) return;

            card.flipped = true;
            this.memoryFlippedCardIds.push(cardId);

            if (this.memoryFlippedCardIds.length < 2) {
                this.memoryCelebration = 'Busca la pareja';
                return;
            }

            this.memoryLockBoard = true;

            const [firstId, secondId] = this.memoryFlippedCardIds;
            const firstCard = this.memoryCards.find((item) => item.id === firstId);
            const secondCard = this.memoryCards.find((item) => item.id === secondId);

            if (!firstCard || !secondCard) {
                this.memoryFlippedCardIds = [];
                this.memoryLockBoard = false;
                return;
            }

            if (firstCard.match_id === secondCard.match_id && firstCard.id !== secondCard.id) {
                setTimeout(() => {
                    firstCard.matched = true;
                    secondCard.matched = true;
                    this.recordMemoryMatch(firstCard);
                    this.applyMemoryReward(firstCard);
                    this.memoryFlippedCardIds = [];
                    this.memoryLockBoard = false;
                }, 500);
                return;
            }

            setTimeout(() => {
                firstCard.flipped = false;
                secondCard.flipped = false;
                this.memoryLives = Math.max(0, this.memoryLives - 1);
                this.memoryCelebration = this.memoryLives === 0 ? 'Sin vidas' : 'Intenta de nuevo';
                this.memoryFlippedCardIds = [];
                this.memoryLockBoard = false;

                if (this.memoryLives === 0) {
                    this.finishGame();
                }
            }, 850);
        },

        startGame() {
            this.gameState = 'playing';
            this.currentQuestion = 0;
            this.score = 0;
            this.correctAnswers = 0;
            this.responses = {};
            this.isSavingResult = false;
            this.startedAt = Date.now();
            this.resetAdventureState();
            if (this.isMemory()) {
                this.resetMemoryState();
                this.startTimer();
                return;
            }

            if (this.isEnglishWorksheet()) {
                this.resetEnglishWorksheetState();
                this.startTimer();
                return;
            }

            if (this.isEnglishMatchBoard()) {
                this.resetEnglishMatchBoardState();
                this.startTimer();
                return;
            }

            if (this.isWordSearch()) {
                this.resetWordSearchState();
                this.startTimer();
                return;
            }

            this.loadQuestion();
            this.startTimer();
        },

        loadQuestion() {
            this.currentQuestionData = this.questions[this.currentQuestion] || null;
            this.selectedAnswer = null;
            this.textAnswer = '';
            this.answerSubmitted = false;
            this.isCorrect = false;
        },

        startTimer() {
            if (this.timeLeft <= 0) return;
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    this.finishGame();
                }
            }, 1000);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        selectAnswer(index) {
            if (!this.answerSubmitted) this.selectedAnswer = index;
        },

        canSubmit() {
            if (!this.currentQuestionData) return false;
            if (this.currentQuestionData.opciones_normalizadas.length > 0) {
                return this.selectedAnswer !== null;
            }
            return this.textAnswer.trim() !== '';
        },

        normalize(v) {
            return String(v ?? '').trim().toLowerCase();
        },

        isCorrectAnswer(question, responseValue) {
            let correcta = question.respuesta_correcta;
            if (typeof correcta === 'string') {
                const raw = correcta.trim();
                if ((raw.startsWith('[') && raw.endsWith(']')) || (raw.startsWith('{') && raw.endsWith('}'))) {
                    try { correcta = JSON.parse(raw); } catch (_) {}
                }
            }

            if (Array.isArray(correcta)) {
                return correcta.map(v => this.normalize(v)).includes(this.normalize(responseValue));
            }
            return this.normalize(correcta) === this.normalize(responseValue);
        },

        submitAnswer() {
            if (!this.canSubmit()) return;

            this.answerSubmitted = true;

            let responseValue = this.textAnswer;
            if (this.currentQuestionData.opciones_normalizadas.length > 0) {
                const selected = this.currentQuestionData.opciones_normalizadas[this.selectedAnswer];
                responseValue = selected ? selected.valor : '';
            }

            const correct = this.isCorrectAnswer(this.currentQuestionData, responseValue);
            this.isCorrect = correct;
            if (correct) {
                this.correctAnswers++;
                this.score += this.currentQuestionData.puntaje || 10;
                this.applyAdventureReward();
            }

            this.responses[this.currentQuestionData.id] = responseValue;
        },

        nextQuestion() {
            this.currentQuestion++;
            if (this.currentQuestion >= this.totalQuestions) {
                this.finishGame();
                return;
            }
            this.loadQuestion();
        },

        finishGame() {
            if (this.isSavingResult || this.gameState === 'finished') return;

            this.isSavingResult = true;
            if (this.timer) clearInterval(this.timer);
            this.timer = null;
            this.gameState = 'finished';

            const elapsedSeconds = Math.max(0, Math.round((Date.now() - (this.startedAt || Date.now())) / 1000));

            fetch('{{ route('estudiante.juegos.guardar', $juego->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    respuestas: this.responses,
                    duracion_segundos: elapsedSeconds
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(() => {
                this.isSavingResult = false;
            });
        },

        restartGame() {
            if (this.timer) clearInterval(this.timer);
            this.timeLeft = {{ $juego->tiempo_limite_segundos ?? 0 }};
            this.gameState = 'start';
            this.currentQuestion = 0;
            this.score = 0;
            this.correctAnswers = 0;
            this.responses = {};
            this.isSavingResult = false;
            this.resetAdventureState();
            if (this.isMemory()) {
                this.resetMemoryState();
            } else if (this.isEnglishWorksheet()) {
                this.resetEnglishWorksheetState();
            } else if (this.isEnglishMatchBoard()) {
                this.resetEnglishMatchBoardState();
            } else if (this.isWordSearch()) {
                this.resetWordSearchState();
            }
        }
    }
}
</script>
@endsection
