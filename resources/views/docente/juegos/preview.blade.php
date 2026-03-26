@extends('layouts.app')

@section('title', 'Vista previa del juego')

@section('content')
@php
    $config = $juego->configuracion ?? [];
    $esMatematicaAventura = $juego->tipo === 'matematica_aventura';
    $objetivo = $config['objetivo_aventura'] ?? 'puente';
    $operacion = $config['operacion_principal'] ?? 'mixto';
    $recompensa = $config['recompensa_principal'] ?? 'monedas';
    $monedas = $config['monedas_por_acierto'] ?? 15;
    $energia = $config['energia_por_acierto'] ?? 10;

    $labelsOperacion = [
        'suma' => 'Sumas',
        'resta' => 'Restas',
        'multiplicacion' => 'Multiplicaciones',
        'division' => 'Divisiones',
        'mixto' => 'Mixto',
    ];

    $labelsObjetivo = [
        'puente' => 'Cruzar puentes',
        'cofre' => 'Abrir cofres',
        'obstaculo' => 'Derrotar obstaculos',
    ];

    $sampleByOperacion = [
        'suma' => ['pregunta' => '8 + 7 = ?', 'opciones' => [13, 15, 16, 18]],
        'resta' => ['pregunta' => '19 - 6 = ?', 'opciones' => [11, 12, 13, 14]],
        'multiplicacion' => ['pregunta' => '4 x 6 = ?', 'opciones' => [20, 22, 24, 26]],
        'division' => ['pregunta' => '24 / 6 = ?', 'opciones' => [3, 4, 5, 6]],
        'mixto' => ['pregunta' => '9 + 5 - 3 = ?', 'opciones' => [9, 10, 11, 12]],
    ];

    $sampleQuestion = $sampleByOperacion[$operacion] ?? $sampleByOperacion['mixto'];
    $previewSamples = [
        'quiz' => [
            'eyebrow' => 'Pregunta ejemplo',
            'title' => 'Selecciona la opcion correcta',
            'description' => 'Vista rapida de una pregunta con respuestas visibles.',
            'layout' => 'choices',
            'options' => ['Opcion A', 'Opcion B', 'Opcion C', 'Opcion D'],
        ],
        'memoria' => [
            'eyebrow' => 'Tablero ejemplo',
            'title' => 'Encuentra cada pareja',
            'description' => 'El estudiante recuerda cartas y busca sus coincidencias.',
            'layout' => 'cards',
            'cards' => ['7 + 3', '10', 'Perro', 'Animal'],
        ],
        'arrastrar' => [
            'eyebrow' => 'Escena ejemplo',
            'title' => 'Lleva cada elemento a su categoria',
            'description' => 'La experiencia muestra zonas para arrastrar y clasificar.',
            'layout' => 'buckets',
            'buckets' => [
                ['title' => 'Mamiferos', 'sample' => 'Perro'],
                ['title' => 'Aves', 'sample' => 'Loro'],
            ],
        ],
        'completar' => [
            'eyebrow' => 'Frase ejemplo',
            'title' => 'Completa la respuesta',
            'description' => 'El estudiante escribe o identifica la parte faltante.',
            'layout' => 'fill',
            'fill' => '2 + 3 = ____',
        ],
        'ordenar' => [
            'eyebrow' => 'Secuencia ejemplo',
            'title' => 'Ordena los eventos',
            'description' => 'Cada bloque debe quedar en su posicion correcta.',
            'layout' => 'sequence',
            'sequence' => ['Semilla', 'Brote', 'Planta', 'Flor'],
        ],
        'sopa' => [
            'eyebrow' => 'Sopa ejemplo',
            'title' => 'Encuentra las palabras',
            'description' => 'El estudiante buscara palabras a partir de pistas.',
            'layout' => 'grid',
            'grid' => ['P', 'E', 'R', 'R', 'O', 'A', 'R', 'B', 'O', 'L', 'L', 'U', 'N', 'A', 'S', 'O', 'L', 'M', 'A', 'R', 'C', 'A', 'S', 'A', 'R'],
            'words' => ['PERRO', 'ARBOL', 'LUNA'],
        ],
        'clasificar' => [
            'eyebrow' => 'Pantalla ejemplo',
            'title' => 'Decide rapido donde va cada elemento',
            'description' => 'Los elementos se clasifican segun su categoria.',
            'layout' => 'falling',
            'buckets' => [
                ['title' => 'Seres vivos', 'sample' => 'Perro'],
                ['title' => 'Objetos', 'sample' => 'Mesa'],
            ],
            'items' => ['Perro', 'Mesa', 'Arbol', 'Libro'],
        ],
    ];
    $preview = $previewSamples[$juego->tipo] ?? $previewSamples['quiz'];
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">Vista previa del juego</h1>
            <p class="mt-1 text-gray-500">{{ $juego->titulo }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('docente.juegos.preguntas', $juego->id) }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-white transition-colors hover:bg-cyan-700">
                <i class="fas fa-list-check mr-2"></i>Preguntas
            </a>
            <a href="{{ route('docente.juegos.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
        <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-4">
            <div class="rounded-2xl bg-cyan-50 p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-cyan-700">Tipo</p>
                <p class="mt-2 font-semibold text-cyan-950">{{ $juego->tipo_nombre }}</p>
            </div>
            <div class="rounded-2xl bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-blue-700">Dificultad</p>
                <p class="mt-2 font-semibold text-blue-950">{{ $juego->dificultad }}/4</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-emerald-700">Intentos</p>
                <p class="mt-2 font-semibold text-emerald-950">{{ $juego->intentos_maximos }}</p>
            </div>
            <div class="rounded-2xl bg-amber-50 p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-amber-700">Tiempo</p>
                <p class="mt-2 font-semibold text-amber-950">{{ $juego->tiempo_limite_formateado }}</p>
            </div>
        </div>

        <div class="space-y-2 px-6 pb-6 text-sm text-gray-600">
            <div>Tema: <span class="font-medium text-gray-900">{{ $juego->tema->titulo }}</span></div>
            <div>Asignatura: <span class="font-medium text-gray-900">{{ $juego->tema->asignatura->nombre }}</span></div>
            <div>Puntaje base: <span class="font-medium text-gray-900">{{ $juego->puntaje_base }}</span></div>
            <div>Estado: <span class="font-medium {{ $juego->activo ? 'text-green-700' : 'text-red-700' }}">{{ $juego->activo ? 'Activo' : 'Inactivo' }}</span></div>
            @if($juego->descripcion)
                <div class="mt-3 rounded-2xl bg-gray-50 p-4 text-gray-700">{{ $juego->descripcion }}</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 text-white shadow-xl shadow-slate-100">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-white/70">Experiencia seleccionada</p>
                        <h2 class="mt-2 text-3xl font-display font-semibold">{{ $juego->tipo_nombre }}</h2>
                        <p class="mt-3 max-w-2xl text-white/85">
                            @if($esMatematicaAventura)
                                Cada respuesta correcta ayuda al estudiante a avanzar en una mision matematica y ganar recompensas mientras resuelve operaciones.
                            @else
                                {{ $preview['description'] }}
                            @endif
                        </p>
                    </div>
                    <div class="rounded-3xl bg-white/15 px-5 py-4 text-4xl">{{ $juego->tipo_icono }}</div>
                </div>

                @if($esMatematicaAventura)
                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Operacion</p>
                            <p class="mt-2 text-lg font-semibold">{{ $labelsOperacion[$operacion] ?? 'Mixto' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Objetivo</p>
                            <p class="mt-2 text-lg font-semibold">{{ $labelsObjetivo[$objetivo] ?? 'Cruzar puentes' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Recompensa</p>
                            <p class="mt-2 text-lg font-semibold">
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
                @else
                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Mecanica</p>
                            <p class="mt-2 text-lg font-semibold">{{ $preview['title'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Dificultad</p>
                            <p class="mt-2 text-lg font-semibold">{{ $juego->dificultad }}/4</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Intentos</p>
                            <p class="mt-2 text-lg font-semibold">{{ $juego->intentos_maximos }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-lg shadow-slate-100">
            @if($esMatematicaAventura)
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Ejemplo de pantalla</p>
                        <h3 class="mt-3 text-2xl font-display font-semibold">{{ $sampleQuestion['pregunta'] }}</h3>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold">
                        @if($recompensa === 'energia')
                            +{{ $energia }} energia
                        @elseif($recompensa === 'ambas')
                            +{{ $monedas }} monedas y +{{ $energia }} energia
                        @else
                            +{{ $monedas }} monedas
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    @foreach($sampleQuestion['opciones'] as $opcion)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center font-medium text-slate-100">{{ $opcion }}</div>
                    @endforeach
                </div>
            @elseif($preview['layout'] === 'cards')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 overflow-hidden rounded-[1.8rem] border border-white/10 bg-[radial-gradient(circle_at_top,_rgba(245,235,178,0.2),_rgba(94,58,155,0.78)_58%,_rgba(29,23,54,0.96)_100%)] p-4">
                    <div class="mb-4 flex items-center justify-between gap-3 rounded-2xl bg-black/20 px-4 py-3">
                        <div class="inline-flex items-center gap-2 rounded-full bg-rose-400/15 px-3 py-1 text-sm font-semibold text-rose-100">
                            <span>❤️</span>
                            <span>3</span>
                        </div>
                        <p class="text-lg font-display font-semibold text-white">Memoria Matematica</p>
                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-400/15 px-3 py-1 text-sm font-semibold text-amber-100">
                            <span>💰</span>
                            <span>150</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <div class="aspect-[0.8] rounded-[1.3rem] border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100 p-2 shadow-lg">
                            <div class="flex h-full items-center justify-center rounded-[1rem] border border-white/60 bg-white/70 text-2xl font-black text-slate-900">5 + 2</div>
                        </div>
                        <div class="aspect-[0.8] rounded-[1.3rem] border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100 p-2 shadow-lg">
                            <div class="flex h-full items-center justify-center rounded-[1rem] border border-white/60 bg-white/70 text-3xl font-black text-indigo-700">7</div>
                        </div>
                        @for($i = 0; $i < 6; $i++)
                            <div class="aspect-[0.8] rounded-[1.3rem] border border-violet-300/50 bg-gradient-to-br from-violet-500 to-fuchsia-700 p-2 shadow-lg">
                                <div class="flex h-full items-center justify-center rounded-[1rem] border border-white/10 bg-white/10 text-4xl font-black text-white">?</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @elseif($preview['layout'] === 'buckets')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 space-y-3">
                    @foreach($preview['buckets'] as $bucket)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-white">{{ $bucket['title'] }}</span>
                                <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-semibold text-cyan-200">{{ $bucket['sample'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($preview['layout'] === 'sequence')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($preview['sequence'] as $index => $item)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-slate-100">{{ $index + 1 }}. {{ $item }}</div>
                    @endforeach
                </div>
            @elseif($preview['layout'] === 'grid')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 grid grid-cols-5 gap-2">
                    @foreach($preview['grid'] as $cell)
                        <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 text-center text-sm font-semibold text-slate-100">{{ $cell }}</div>
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($preview['words'] as $word)
                        <span class="rounded-full bg-rose-400/15 px-3 py-1 text-xs font-semibold text-rose-200">{{ $word }}</span>
                    @endforeach
                </div>
            @elseif($preview['layout'] === 'fill')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 px-4 py-5 text-lg font-medium text-slate-100">{{ $preview['fill'] }}</div>
            @elseif($preview['layout'] === 'falling')
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-emerald-200">Categoria A</p>
                        <p class="mt-2 font-semibold text-white">{{ $preview['buckets'][0]['title'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-sky-400/20 bg-sky-400/10 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-sky-200">Categoria B</p>
                        <p class="mt-2 font-semibold text-white">{{ $preview['buckets'][1]['title'] }}</p>
                    </div>
                    @foreach($preview['items'] as $item)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center font-medium text-slate-100">{{ $item }}</div>
                    @endforeach
                </div>
            @else
                <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">{{ $preview['eyebrow'] }}</p>
                <h3 class="mt-3 text-2xl font-display font-semibold">{{ $preview['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ $preview['description'] }}</p>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    @foreach($preview['options'] as $option)
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center font-medium text-slate-100">{{ $option }}</div>
                    @endforeach
                </div>
            @endif

            <div class="mt-5 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                El contenido real se vera aqui una vez agregues preguntas, operaciones o elementos propios de esta categoria.
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Preguntas activas</h2>
            <span class="text-sm text-gray-500">{{ $juego->preguntasActivas->count() }} total</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($juego->preguntasActivas as $pregunta)
                <div class="p-5">
                    <div class="text-sm text-gray-500">#{{ $pregunta->orden }} | {{ $pregunta->tipo }} | {{ $pregunta->puntaje }} pts</div>
                    <p class="mt-1 font-medium text-gray-800">{{ $pregunta->enunciado }}</p>

                    @if(!empty($pregunta->opciones))
                        @if(isset($pregunta->opciones[0]) && is_array($pregunta->opciones[0]))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($pregunta->opciones as $opcion)
                                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-sm font-medium text-cyan-700">{{ ($opcion['elemento'] ?? 'Elemento') . ' -> ' . ($opcion['categoria'] ?? 'Categoria') }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-sm text-gray-600">Opciones: {{ implode(' | ', array_filter($pregunta->opciones)) }}</p>
                        @endif
                    @endif

                    @if(!empty($pregunta->respuesta_correcta))
                        @if(is_array($pregunta->respuesta_correcta) && isset($pregunta->respuesta_correcta['orden']))
                            <p class="mt-1 text-sm text-green-700">Orden correcto: {{ $pregunta->respuesta_correcta['orden'] }}</p>
                        @else
                            <p class="mt-1 text-sm text-green-700">Respuesta: {{ implode(', ', (array) $pregunta->respuesta_correcta) }}</p>
                        @endif
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">Aun no hay preguntas activas para este juego.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
