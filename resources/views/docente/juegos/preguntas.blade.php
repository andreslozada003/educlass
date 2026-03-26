@extends('layouts.app')

@section('title', 'Contenido del Juego')

@section('content')
@php
    $tipoConfig = config("juegos.tipos.{$juego->tipo}", []);
    $tipoNombre = $tipoConfig['nombre'] ?? ucfirst(str_replace('_', ' ', $juego->tipo));
    $accentColor = $tipoConfig['color'] ?? '#0F766E';
    $asignaturaNombre = $juego->tema->asignatura->nombre ?? 'Sin asignatura';
    $esOrdenarIngles = $juego->tipo === 'ordenar' && ($juego->tema->asignatura->slug ?? null) === 'ingles';
    $iconMap = [
        'quiz' => 'fa-circle-question',
        'memoria' => 'fa-brain',
        'arrastrar' => 'fa-hand',
        'completar' => 'fa-pen',
        'ordenar' => 'fa-arrow-down-1-9',
        'sopa' => 'fa-table-cells',
        'clasificar' => 'fa-layer-group',
        'matematica_aventura' => 'fa-route',
    ];
    $iconClass = $iconMap[$juego->tipo] ?? 'fa-shapes';

    $configuracion = $juego->configuracion ?? [];
    $objetivoLabels = [
        'puente' => 'Cruzar puentes',
        'cofre' => 'Abrir cofres',
        'obstaculo' => 'Derrotar obstaculos',
    ];
    $recompensaLabels = [
        'monedas' => 'Monedas',
        'energia' => 'Energia',
        'ambas' => 'Monedas y energia',
    ];
    $esRutaImagen = function ($valor) {
        return is_string($valor)
            && preg_match('/^(https?:\/\/|\/|storage\/).+\.(png|jpe?g|gif|webp|svg)$/i', $valor);
    };

    $oldElementos = old('elementos_categoria', []);
    $categoriaItems = [];
    if (!empty($oldElementos)) {
        foreach ($oldElementos as $index => $elemento) {
            $categoriaItems[] = [
                'elemento' => $elemento,
                'categoria' => old("categorias_elemento.{$index}", ''),
            ];
        }
    } else {
        $categoriaItems = [
            ['elemento' => '', 'categoria' => ''],
            ['elemento' => '', 'categoria' => ''],
            ['elemento' => '', 'categoria' => ''],
        ];
    }

    $distractoresIniciales = old('distractores', ['', '', '']);
    if (count($distractoresIniciales) < 3) {
        $distractoresIniciales = array_pad($distractoresIniciales, 3, '');
    }

    $opcionesIniciales = old('opciones', ['', '', '', '']);
    if (count($opcionesIniciales) < 4) {
        $opcionesIniciales = array_pad($opcionesIniciales, 4, '');
    }
@endphp

<div
    class="max-w-7xl mx-auto space-y-6"
    x-data="preguntaBuilder({
        tipo: @js($juego->tipo),
        tipoNombre: @js($tipoNombre),
        contextoAventura: @js(old('contexto_aventura', 'Cruza el puente resolviendo la operacion')),
        numeroA: @js(old('numero_a', 8)),
        operador: @js(old('operador', '+')),
        numeroB: @js(old('numero_b', 7)),
        modoRespuesta: @js(old('modo_respuesta', 'opcion_multiple')),
        distractores: @js($distractoresIniciales),
        enunciado: @js(old('enunciado', '')),
        respuesta: @js(old('respuesta_correcta', '')),
        puntaje: @js((int) old('puntaje', 10)),
        consigna: @js(old('consigna', '')),
        categoriaItems: @js($categoriaItems),
        elementoOrdenar: @js(old('elemento_ordenar', '')),
        esOrdenarIngles: @js($esOrdenarIngles),
        posicionCorrecta: @js((int) old('posicion_correcta', 1)),
        tipoPregunta: @js(old('tipo', 'opcion_multiple')),
        opciones: @js($opcionesIniciales)
    })"
>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl text-white shadow-lg" style="background: linear-gradient(135deg, {{ $accentColor }}, #0f172a);">
                <i class="fas {{ $iconClass }} text-2xl"></i>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em]" style="color: {{ $accentColor }};">Constructor de contenido</p>
                <h1 class="mt-2 text-3xl font-display font-bold text-gray-900">Lo que aparecera dentro del juego</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">
                    Aqui defines el contenido real que vera el estudiante. Esta pantalla cambia segun la categoria del juego para que no tengas que crear preguntas genericas.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $accentColor }};">{{ $tipoNombre }}</span>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">{{ $asignaturaNombre }}</span>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">{{ $juego->preguntas->count() }} creadas</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('docente.juegos.preview', $juego->id) }}" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                <i class="fas fa-eye mr-2"></i>Visualizar juego
            </a>
            <a href="{{ route('docente.juegos.index') }}" class="inline-flex items-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-gray-700 ring-1 ring-gray-200 transition hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
            <p class="font-semibold">Revisa estos datos antes de guardar:</p>
            <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em]" style="color: {{ $accentColor }};">Editor guiado</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Crear contenido para {{ $tipoNombre }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Cada bloque que agregues aqui es parte de la experiencia del estudiante dentro del juego.</p>
                </div>

                <form method="POST" action="{{ route('docente.juegos.preguntas.agregar', $juego->id) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf

                    @switch($juego->tipo)
                        @case('matematica_aventura')
                            <div class="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Mision del reto</p>
                                        <p class="mt-2 text-sm leading-6 text-emerald-900">
                                            Este juego esta configurado para
                                            <span class="font-semibold">{{ $objetivoLabels[$configuracion['objetivo_aventura'] ?? 'puente'] ?? 'Cruzar puentes' }}</span>
                                            y premiar con
                                            <span class="font-semibold">{{ $recompensaLabels[$configuracion['recompensa_principal'] ?? 'monedas'] ?? 'Monedas' }}</span>.
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
                                        +{{ $configuracion['monedas_por_acierto'] ?? 15 }} monedas / +{{ $configuracion['energia_por_acierto'] ?? 10 }} energia
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <label for="contexto_aventura" class="block text-sm font-medium text-gray-700 mb-2">Contexto de la escena</label>
                                    <input type="text" id="contexto_aventura" name="contexto_aventura" x-model="contextoAventura" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Ej: Abre el cofre resolviendo la operacion" required>
                                    <p class="mt-2 text-sm text-gray-500">Usa una frase corta para que el nino entienda por que debe resolver la operacion.</p>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_180px_1fr]">
                                    <div>
                                        <label for="numero_a" class="block text-sm font-medium text-gray-700 mb-2">Primer numero</label>
                                        <input type="number" step="any" id="numero_a" name="numero_a" x-model="numeroA" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                                    </div>
                                    <div>
                                        <label for="operador" class="block text-sm font-medium text-gray-700 mb-2">Operacion</label>
                                        <select id="operador" name="operador" x-model="operador" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                                            <option value="+">Suma (+)</option>
                                            <option value="-">Resta (-)</option>
                                            <option value="x">Multiplicacion (x)</option>
                                            <option value="/">Division (/)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="numero_b" class="block text-sm font-medium text-gray-700 mb-2">Segundo numero</label>
                                        <input type="number" step="any" id="numero_b" name="numero_b" x-model="numeroB" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="modo_respuesta" class="block text-sm font-medium text-gray-700 mb-2">Modo de respuesta</label>
                                        <select id="modo_respuesta" name="modo_respuesta" x-model="modoRespuesta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                                            <option value="opcion_multiple">Opcion multiple</option>
                                            <option value="respuesta_corta">Respuesta corta</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje del reto</label>
                                        <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                                    </div>
                                </div>

                                <div x-show="modoRespuesta === 'opcion_multiple'" x-cloak class="space-y-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-800">Distractores</h3>
                                            <p class="text-sm text-gray-500">Agrega respuestas cercanas al resultado correcto para hacerlo mas divertido.</p>
                                        </div>
                                        <button type="button" @click="addDistractor()" class="inline-flex items-center rounded-xl bg-emerald-100 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-200">
                                            <i class="fas fa-plus mr-2"></i>Agregar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <template x-for="(distractor, index) in distractores" :key="index">
                                            <div class="flex items-center gap-2">
                                                <input type="number" step="any" name="distractores[]" x-model="distractores[index]" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" :placeholder="`Distractor ${index + 1}`">
                                                <button type="button" @click="removeDistractor(index)" class="rounded-xl bg-red-50 px-3 py-3 text-red-600 transition hover:bg-red-100">
                                                    <i class="fas fa-xmark"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('memoria')
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="enunciado" class="block text-sm font-medium text-gray-700 mb-2">Tarjeta principal</label>
                                    <input type="text" id="enunciado" name="enunciado" x-model="enunciado" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" placeholder="Ej: 7 + 3" required>
                                </div>
                                <div>
                                    <label for="respuesta_correcta" class="block text-sm font-medium text-gray-700 mb-2">Pareja correcta</label>
                                    <input type="text" id="respuesta_correcta" name="respuesta_correcta" x-model="respuesta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" placeholder="Ej: 10" required>
                                </div>
                            </div>

                            <div>
                                <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje por pareja</label>
                                <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                            </div>
                            @break

                        @case('arrastrar')
                        @case('clasificar')
                            <div class="space-y-5">
                                <div>
                                    <label for="consigna" class="block text-sm font-medium text-gray-700 mb-2">Consigna</label>
                                    <textarea id="consigna" name="consigna" rows="3" x-model="consigna" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="Ej: Arrastra cada animal a su habitat correcto" required></textarea>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-800">Elementos y categorias</h3>
                                            <p class="text-sm text-gray-500">Cada fila representa algo que el estudiante debera ubicar en su categoria.</p>
                                        </div>
                                        <button type="button" @click="addCategoriaItem()" class="inline-flex items-center rounded-xl bg-cyan-100 px-3 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-200">
                                            <i class="fas fa-plus mr-2"></i>Agregar fila
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(item, index) in categoriaItems" :key="index">
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_1fr_auto]">
                                                <input type="text" name="elementos_categoria[]" x-model="item.elemento" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" :placeholder="`Elemento ${index + 1}`">
                                                <input type="text" name="categorias_elemento[]" x-model="item.categoria" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" :placeholder="`Categoria ${index + 1}`">
                                                <button type="button" @click="removeCategoriaItem(index)" class="rounded-2xl bg-red-50 px-4 py-3 text-red-600 transition hover:bg-red-100">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje del bloque</label>
                                    <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" required>
                                </div>
                            </div>
                            @break

                        @case('ordenar')
                            @if($esOrdenarIngles)
                                <div class="rounded-3xl border border-sky-200 bg-sky-50/70 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">Modo especial para ingles</p>
                                    <p class="mt-2 text-sm leading-6 text-sky-900">
                                        Crea una palabra en ingles y su pareja visual. Puedes usar emoji, texto corto o subir una imagen, por ejemplo:
                                        <span class="font-semibold">Apple</span> y <span class="font-semibold">apple image</span>.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="elemento_ordenar" class="block text-sm font-medium text-gray-700 mb-2">Palabra en ingles</label>
                                        <input type="text" id="elemento_ordenar" name="elemento_ordenar" x-model="elementoOrdenar" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" placeholder="Ej: Apple" required>
                                    </div>
                                    <div>
                                        <label for="respuesta_correcta" class="block text-sm font-medium text-gray-700 mb-2">Pareja visual en texto</label>
                                        <input type="text" id="respuesta_correcta" name="respuesta_correcta" x-model="respuesta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" placeholder="Ej: Apple">
                                        <p class="mt-2 text-xs text-gray-500">Opcional si vas a subir una imagen.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-[1fr_auto] md:items-end">
                                    <div>
                                        <label for="imagen_pareja" class="block text-sm font-medium text-gray-700 mb-2">Subir imagen para la pareja</label>
                                        <input type="file" id="imagen_pareja" name="imagen_pareja" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml" @change="setImagenParejaPreview($event)" class="w-full rounded-2xl border border-dashed border-pink-300 bg-pink-50/40 px-4 py-3 text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-pink-500 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-pink-600">
                                        <p class="mt-2 text-xs text-gray-500">Opcional si ya escribiste la pareja en texto. Formatos recomendados: JPG, PNG o WEBP.</p>
                                    </div>

                                    <div x-show="imagenParejaPreview" x-cloak class="rounded-3xl border border-pink-100 bg-white p-3 shadow-sm">
                                        <img :src="imagenParejaPreview" alt="Vista previa de la imagen" class="h-24 w-24 rounded-2xl object-cover">
                                    </div>
                                </div>

                                <div>
                                    <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje de la pareja</label>
                                    <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" required>
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="elemento_ordenar" class="block text-sm font-medium text-gray-700 mb-2">Elemento o evento</label>
                                        <input type="text" id="elemento_ordenar" name="elemento_ordenar" x-model="elementoOrdenar" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" placeholder="Ej: Nace la planta" required>
                                    </div>
                                    <div>
                                        <label for="posicion_correcta" class="block text-sm font-medium text-gray-700 mb-2">Posicion correcta</label>
                                        <input type="number" min="1" id="posicion_correcta" name="posicion_correcta" x-model="posicionCorrecta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje del elemento</label>
                                    <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200" required>
                                </div>
                            @endif
                            @break

                        @case('sopa')
                        @case('completar')
                            <div class="space-y-5">
                                <div>
                                    <label for="enunciado" class="block text-sm font-medium text-gray-700 mb-2">{{ $juego->tipo === 'sopa' ? 'Pista de la palabra' : 'Frase o consigna' }}</label>
                                    <textarea id="enunciado" name="enunciado" rows="3" x-model="enunciado" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="{{ $juego->tipo === 'sopa' ? 'Ej: Animal que ladra' : 'Ej: 2 + 3 = ____' }}" required></textarea>
                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="respuesta_correcta" class="block text-sm font-medium text-gray-700 mb-2">{{ $juego->tipo === 'sopa' ? 'Palabra escondida' : 'Respuesta correcta' }}</label>
                                        <input type="text" id="respuesta_correcta" name="respuesta_correcta" x-model="respuesta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="{{ $juego->tipo === 'sopa' ? 'Ej: perro' : 'Ej: 5' }}" required>
                                    </div>
                                    <div>
                                        <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje</label>
                                        <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
                                    </div>
                                </div>
                            </div>
                            @break

                        @default
                            <div class="space-y-5">
                                <div>
                                    <label for="enunciado" class="block text-sm font-medium text-gray-700 mb-2">Pregunta o enunciado</label>
                                    <textarea id="enunciado" name="enunciado" rows="3" x-model="enunciado" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Escribe la pregunta que vera el estudiante" required></textarea>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo de respuesta</label>
                                        <select id="tipo" name="tipo" x-model="tipoPregunta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" required>
                                            <option value="opcion_multiple">Opcion multiple</option>
                                            <option value="verdadero_falso">Verdadero o falso</option>
                                            <option value="emparejamiento">Emparejamiento</option>
                                            <option value="ordenamiento">Ordenamiento</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="respuesta_correcta" class="block text-sm font-medium text-gray-700 mb-2">Respuesta correcta</label>
                                        <input type="text" id="respuesta_correcta" name="respuesta_correcta" x-model="respuesta" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Ej: Verdadero o 12" required>
                                    </div>
                                    <div>
                                        <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje</label>
                                        <input type="number" min="1" id="puntaje" name="puntaje" x-model="puntaje" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" required>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-800">Opciones</h3>
                                            <p class="text-sm text-gray-500">Solo son necesarias si el juego usa opciones visibles.</p>
                                        </div>
                                        <button type="button" @click="addOpcion()" class="inline-flex items-center rounded-xl bg-blue-100 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-200">
                                            <i class="fas fa-plus mr-2"></i>Agregar opcion
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <template x-for="(opcion, index) in opciones" :key="index">
                                            <div class="flex items-center gap-2">
                                                <input type="text" name="opciones[]" x-model="opciones[index]" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" :placeholder="`Opcion ${index + 1}`">
                                                <button type="button" @click="removeOpcion(index)" class="rounded-xl bg-red-50 px-3 py-3 text-red-600 transition hover:bg-red-100">
                                                    <i class="fas fa-xmark"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                    @endswitch

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-md transition hover:opacity-95" style="background-color: {{ $accentColor }};">
                            <i class="fas fa-plus mr-2"></i>Guardar contenido en el juego
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 text-white shadow-lg shadow-slate-100">
                <div class="border-b border-white/10 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-300">Vista previa</p>
                    <h2 class="mt-2 text-xl font-semibold">Asi se sentira este bloque en el juego</h2>
                </div>

                <div class="p-6">
                    @switch($juego->tipo)
                        @case('matematica_aventura')
                            <div class="space-y-5">
                                <div class="rounded-3xl bg-white/5 p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs uppercase tracking-[0.24em] text-emerald-300">Reto matematico</p>
                                        <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-200" x-text="`${puntaje || 0} pts`"></span>
                                    </div>
                                    <p class="mt-4 text-2xl font-semibold leading-tight" x-text="enunciadoMatematica()"></p>
                                </div>

                                <div x-show="modoRespuesta === 'opcion_multiple'" x-cloak class="grid grid-cols-2 gap-3">
                                    <template x-for="(opcion, index) in opcionesMatematicas()" :key="`${opcion}-${index}`">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center text-lg font-semibold text-slate-100" x-text="opcion"></div>
                                    </template>
                                </div>

                                <div x-show="modoRespuesta !== 'opcion_multiple'" x-cloak class="rounded-2xl border border-dashed border-white/15 bg-white/5 px-4 py-6 text-center text-slate-300">
                                    El estudiante escribira la respuesta correcta.
                                </div>

                                <div class="rounded-2xl bg-emerald-400/10 px-4 py-4 text-sm text-emerald-100">
                                    Resultado esperado: <span class="font-semibold" x-text="resultadoOperacion()"></span>
                                </div>
                            </div>
                            @break

                        @case('memoria')
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-3xl bg-violet-400/15 p-6">
                                        <p class="text-xs uppercase tracking-[0.24em] text-violet-200">Carta A</p>
                                        <p class="mt-4 text-2xl font-semibold" x-text="enunciado || 'Contenido de la carta'"></p>
                                    </div>
                                    <div class="rounded-3xl bg-cyan-400/15 p-6">
                                        <p class="text-xs uppercase tracking-[0.24em] text-cyan-200">Carta B</p>
                                        <p class="mt-4 text-2xl font-semibold" x-text="respuesta || 'Pareja correcta'"></p>
                                    </div>
                                </div>
                                <div class="rounded-2xl bg-white/5 px-4 py-4 text-sm text-slate-300">
                                    El estudiante debera recordar y encontrar esta pareja para sumar <span class="font-semibold text-white" x-text="puntaje || 0"></span> puntos.
                                </div>
                            </div>
                            @break

                        @case('arrastrar')
                        @case('clasificar')
                            <div class="space-y-4">
                                <div class="rounded-3xl bg-white/5 p-5">
                                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Consigna</p>
                                    <p class="mt-3 text-lg font-medium text-white" x-text="consigna || 'Aqui se mostrara la consigna del bloque'"></p>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(item, index) in categoriaItemsValidos()" :key="`${item.elemento}-${index}`">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="font-semibold text-white" x-text="item.elemento"></span>
                                                <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-semibold text-cyan-200" x-text="item.categoria"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="categoriaItemsValidos().length === 0" class="rounded-2xl border border-dashed border-white/15 bg-white/5 px-4 py-6 text-center text-slate-300">
                                    Agrega elementos y categorias para ver la experiencia del bloque.
                                </div>
                            </div>
                            @break

                        @case('ordenar')
                            @if($esOrdenarIngles)
                                <div class="space-y-4">
                                    <div class="rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_rgba(15,23,42,0.96)_24%,_rgba(30,41,59,0.96)_100%)] p-6 shadow-lg">
                                        <p class="text-xs uppercase tracking-[0.24em] text-sky-200">Vista previa del match</p>
                                        <h3 class="mt-3 text-3xl font-display font-semibold text-white">Match the Words!</h3>

                                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div class="rounded-3xl bg-white/10 p-5">
                                                <p class="text-xs uppercase tracking-[0.24em] text-white/70">Word card</p>
                                                <p class="mt-4 text-3xl font-black text-white" x-text="elementoOrdenar || 'Apple'"></p>
                                            </div>
                                            <div class="rounded-3xl bg-amber-50 p-5 text-slate-900">
                                                <p class="text-xs uppercase tracking-[0.24em] text-amber-700">Picture card</p>
                                                <template x-if="imagenParejaPreview">
                                                    <img :src="imagenParejaPreview" alt="Vista previa de la pareja" class="mt-4 h-32 w-full rounded-2xl object-cover shadow-sm">
                                                </template>
                                                <template x-if="!imagenParejaPreview">
                                                    <p class="mt-4 text-3xl font-black" x-text="respuesta || 'Apple picture'"></p>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mt-4 rounded-2xl bg-white/10 px-4 py-4 text-sm text-slate-200">
                                            El estudiante hara clic en la palabra y luego en la pareja visual correcta para conectar ambas tarjetas.
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    <div class="rounded-3xl bg-pink-400/15 p-6">
                                        <p class="text-xs uppercase tracking-[0.24em] text-pink-200">Elemento a ordenar</p>
                                        <p class="mt-4 text-2xl font-semibold" x-text="elementoOrdenar || 'Elemento pendiente'"></p>
                                    </div>
                                    <div class="rounded-2xl bg-white/5 px-4 py-4 text-sm text-slate-300">
                                        Posicion correcta: <span class="font-semibold text-white" x-text="posicionCorrecta || 1"></span>
                                    </div>
                                </div>
                            @endif
                            @break

                        @case('sopa')
                        @case('completar')
                            <div class="space-y-4">
                                <div class="rounded-3xl bg-amber-400/15 p-6">
                                    <p class="text-xs uppercase tracking-[0.24em] text-amber-200">{{ $juego->tipo === 'sopa' ? 'Pista del nivel' : 'Enunciado' }}</p>
                                    <p class="mt-4 text-xl font-semibold leading-8" x-text="enunciado || 'La consigna se vera aqui'"></p>
                                </div>
                                <div class="rounded-2xl bg-white/5 px-4 py-4 text-sm text-slate-300">
                                    {{ $juego->tipo === 'sopa' ? 'Palabra que debe encontrar:' : 'Respuesta correcta:' }}
                                    <span class="font-semibold text-white" x-text="respuesta || 'Pendiente'"></span>
                                </div>
                            </div>
                            @break

                        @default
                            <div class="space-y-4">
                                <div class="rounded-3xl bg-blue-400/15 p-6">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs uppercase tracking-[0.24em] text-blue-200">Pregunta del juego</p>
                                        <span class="rounded-full bg-blue-400/15 px-3 py-1 text-xs font-semibold text-blue-100" x-text="tipoPreguntaLabel()"></span>
                                    </div>
                                    <p class="mt-4 text-xl font-semibold leading-8" x-text="enunciado || 'Aqui se mostrara la pregunta'"></p>
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                    <template x-for="(opcion, index) in opcionesValidas()" :key="`${opcion}-${index}`">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-slate-100" x-text="opcion"></div>
                                    </template>
                                </div>

                                <div class="rounded-2xl bg-white/5 px-4 py-4 text-sm text-slate-300">
                                    Respuesta esperada: <span class="font-semibold text-white" x-text="respuesta || 'Pendiente'"></span>
                                </div>
                            </div>
                    @endswitch
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em]" style="color: {{ $accentColor }};">Ayuda rapida</p>
                    <h2 class="mt-2 text-lg font-semibold text-gray-900">Que conviene crear en esta categoria</h2>
                </div>
                <div class="p-6 text-sm leading-6 text-gray-600">
                    @switch($juego->tipo)
                        @case('matematica_aventura')
                            <p>Crea escenas cortas, operaciones claras y distractores cercanos al resultado correcto para mantener la atencion del nino.</p>
                            <p class="mt-3">Puedes variar entre puentes, cofres y obstaculos usando la frase del contexto para que cada reto se sienta parte de una aventura.</p>
                            @break
                        @case('memoria')
                            <p>Funciona muy bien con pares visuales o conceptuales: palabra y significado, operacion y resultado, animal y habitat.</p>
                            <p class="mt-3">Mientras mas cortas sean las tarjetas, mas facil sera reconocerlas rapido en pantalla.</p>
                            @break
                        @case('arrastrar')
                        @case('clasificar')
                            <p>Usa categorias faciles de distinguir y elementos breves. Lo ideal es que cada elemento tenga una sola categoria correcta.</p>
                            <p class="mt-3">Este formato es excelente para clasificar animales, figuras, tipos de palabras, objetos o ecosistemas.</p>
                            @break
                        @case('ordenar')
                            @if($esOrdenarIngles)
                                <p>En ingles puedes usar esta categoria como juego de emparejar: palabra a un lado y pareja visual al otro.</p>
                                <p class="mt-3">Funciona muy bien con vocabulario basico, animales, objetos, colores, transportes o partes del cuerpo.</p>
                            @else
                                <p>Agrega eventos, pasos o elementos que formen una secuencia logica. Cada registro indica la posicion correcta en el orden final.</p>
                                <p class="mt-3">Es util para ciclos de vida, pasos de un proceso o secuencias numericas.</p>
                            @endif
                            @break
                        @case('sopa')
                        @case('completar')
                            <p>Usa pistas cortas, respuestas simples y palabras faciles de reconocer. Eso hace que el juego sea mas claro y motivante.</p>
                            <p class="mt-3">En completar puedes trabajar frases, operaciones o palabras faltantes. En sopa conviene usar palabras concretas.</p>
                            @break
                        @default
                            <p>Este tipo permite construir preguntas clasicas con opciones, verdadero o falso, emparejamiento u ordenamiento.</p>
                            <p class="mt-3">Si quieres una experiencia mas especializada, crea el juego desde una categoria como memoria, clasificar o matematica aventura.</p>
                    @endswitch
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em]" style="color: {{ $accentColor }};">Contenido guardado</p>
                <h2 class="mt-2 text-xl font-semibold text-gray-900">Bloques ya creados para este juego</h2>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">{{ $juego->preguntas->count() }} total</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($juego->preguntas->sortBy('orden') as $pregunta)
                <div class="p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">#{{ $pregunta->orden }}</span>
                                <span class="rounded-full px-3 py-1 font-semibold text-white" style="background-color: {{ $accentColor }};">{{ $pregunta->puntaje }} pts</span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">{{ $pregunta->tipo }}</span>
                            </div>

                            @switch($juego->tipo)
                                @case('matematica_aventura')
                                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                                        <p class="font-semibold text-emerald-900">{{ $pregunta->enunciado }}</p>
                                        @if(!empty($pregunta->opciones))
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach($pregunta->opciones as $opcion)
                                                    <span class="rounded-full bg-white px-3 py-1 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">{{ $opcion }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-3 text-sm text-emerald-700">Modo de respuesta: texto libre</p>
                                        @endif
                                        <p class="mt-4 text-sm text-emerald-800">
                                            Resultado correcto:
                                            <span class="font-semibold">{{ $pregunta->respuesta_correcta[0] ?? 'Sin definir' }}</span>
                                        </p>
                                    </div>
                                    @break

                                @case('memoria')
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div class="rounded-3xl border border-violet-100 bg-violet-50 p-5">
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-violet-700">Carta A</p>
                                            <p class="mt-3 text-lg font-semibold text-violet-950">{{ $pregunta->enunciado }}</p>
                                        </div>
                                        <div class="rounded-3xl border border-cyan-100 bg-cyan-50 p-5">
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Carta B</p>
                                            <p class="mt-3 text-lg font-semibold text-cyan-950">{{ $pregunta->respuesta_correcta[0] ?? 'Sin pareja' }}</p>
                                        </div>
                                    </div>
                                    @break

                                @case('arrastrar')
                                @case('clasificar')
                                    <div class="rounded-3xl border border-cyan-100 bg-cyan-50 p-5">
                                        <p class="font-semibold text-cyan-950">{{ $pregunta->enunciado }}</p>
                                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                            @foreach($pregunta->opciones ?? [] as $opcion)
                                                <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-cyan-100">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <span class="font-medium text-gray-800">{{ $opcion['elemento'] ?? 'Elemento' }}</span>
                                                        <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">{{ $opcion['categoria'] ?? 'Categoria' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @break

                                @case('ordenar')
                                    @if($esOrdenarIngles)
                                        @php
                                            $visualPair = $pregunta->respuesta_correcta[0] ?? 'Sin definir';
                                            $visualPairEsImagen = $esRutaImagen($visualPair);
                                            $visualPairUrl = $visualPairEsImagen
                                                ? (\Illuminate\Support\Str::startsWith($visualPair, ['http://', 'https://', '/']) ? $visualPair : asset($visualPair))
                                                : null;
                                        @endphp
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                            <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5">
                                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">Word</p>
                                                <p class="mt-3 text-2xl font-black text-sky-950">{{ $pregunta->enunciado }}</p>
                                            </div>
                                            <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5">
                                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Visual pair</p>
                                                @if($visualPairEsImagen)
                                                    <img src="{{ $visualPairUrl }}" alt="Pareja visual" class="mt-3 h-36 w-full rounded-2xl object-cover shadow-sm">
                                                @else
                                                    <p class="mt-3 text-2xl font-black text-amber-950">{{ $visualPair }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-3xl border border-pink-100 bg-pink-50 p-5">
                                            <p class="font-semibold text-pink-950">{{ $pregunta->enunciado }}</p>
                                            <p class="mt-3 text-sm text-pink-800">
                                                Posicion correcta:
                                                <span class="font-semibold">{{ $pregunta->respuesta_correcta['orden'] ?? 'Sin definir' }}</span>
                                            </p>
                                        </div>
                                    @endif
                                    @break

                                @case('sopa')
                                @case('completar')
                                    <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5">
                                        <p class="font-semibold text-amber-950">{{ $pregunta->enunciado }}</p>
                                        <p class="mt-3 text-sm text-amber-800">
                                            {{ $juego->tipo === 'sopa' ? 'Palabra correcta:' : 'Respuesta correcta:' }}
                                            <span class="font-semibold">{{ $pregunta->respuesta_correcta[0] ?? 'Sin definir' }}</span>
                                        </p>
                                    </div>
                                    @break

                                @default
                                    <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5">
                                        <p class="font-semibold text-blue-950">{{ $pregunta->enunciado }}</p>
                                        @if(!empty($pregunta->opciones))
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach($pregunta->opciones as $opcion)
                                                    <span class="rounded-full bg-white px-3 py-1 text-sm font-medium text-blue-700 ring-1 ring-blue-100">{{ $opcion }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($pregunta->respuesta_correcta))
                                            <p class="mt-4 text-sm text-blue-800">
                                                Respuesta:
                                                <span class="font-semibold">{{ implode(', ', $pregunta->respuesta_correcta) }}</span>
                                            </p>
                                        @endif
                                    </div>
                            @endswitch
                        </div>

                        <form method="POST" action="{{ route('docente.juegos.preguntas.eliminar', [$juego->id, $pregunta->id]) }}" onsubmit="return confirm('Eliminar este bloque del juego?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                                <i class="fas fa-trash mr-2"></i>Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">Aun no has creado contenido para este juego.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function preguntaBuilder(config) {
        return {
            tipo: config.tipo,
            tipoNombre: config.tipoNombre,
            contextoAventura: config.contextoAventura,
            numeroA: config.numeroA,
            operador: config.operador,
            numeroB: config.numeroB,
            modoRespuesta: config.modoRespuesta,
            distractores: Array.isArray(config.distractores) ? config.distractores : ['', '', ''],
            enunciado: config.enunciado,
            respuesta: config.respuesta,
            puntaje: config.puntaje,
            consigna: config.consigna,
            categoriaItems: Array.isArray(config.categoriaItems) ? config.categoriaItems : [],
            elementoOrdenar: config.elementoOrdenar,
            imagenParejaPreview: '',
            posicionCorrecta: config.posicionCorrecta,
            tipoPregunta: config.tipoPregunta,
            opciones: Array.isArray(config.opciones) ? config.opciones : ['', '', '', ''],

            addDistractor() {
                this.distractores.push('');
            },

            removeDistractor(index) {
                if (this.distractores.length === 1) {
                    this.distractores[0] = '';
                    return;
                }

                this.distractores.splice(index, 1);
            },

            addCategoriaItem() {
                this.categoriaItems.push({ elemento: '', categoria: '' });
            },

            removeCategoriaItem(index) {
                if (this.categoriaItems.length === 1) {
                    this.categoriaItems[0] = { elemento: '', categoria: '' };
                    return;
                }

                this.categoriaItems.splice(index, 1);
            },

            addOpcion() {
                this.opciones.push('');
            },

            removeOpcion(index) {
                if (this.opciones.length === 1) {
                    this.opciones[0] = '';
                    return;
                }

                this.opciones.splice(index, 1);
            },

            setImagenParejaPreview(event) {
                const file = event?.target?.files?.[0];

                if (!file) {
                    this.imagenParejaPreview = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (loadEvent) => {
                    this.imagenParejaPreview = loadEvent?.target?.result || '';
                };
                reader.readAsDataURL(file);
            },

            formatearNumero(valor) {
                const numero = Number(valor);

                if (!Number.isFinite(numero)) {
                    return '?';
                }

                if (Number.isInteger(numero)) {
                    return String(numero);
                }

                return String(Math.round(numero * 100) / 100);
            },

            resultadoOperacion() {
                const a = Number(this.numeroA);
                const b = Number(this.numeroB);

                if (!Number.isFinite(a) || !Number.isFinite(b)) {
                    return '?';
                }

                let resultado = 0;

                switch (this.operador) {
                    case '+':
                        resultado = a + b;
                        break;
                    case '-':
                        resultado = a - b;
                        break;
                    case 'x':
                        resultado = a * b;
                        break;
                    case '/':
                        resultado = b === 0 ? 0 : a / b;
                        break;
                    default:
                        resultado = a + b;
                        break;
                }

                return this.formatearNumero(resultado);
            },

            enunciadoMatematica() {
                const contexto = String(this.contextoAventura || 'Resuelve la operacion').trim();
                return `${contexto}. Resuelve: ${this.formatearNumero(this.numeroA)} ${this.operador} ${this.formatearNumero(this.numeroB)} = ?`;
            },

            opcionesMatematicas() {
                const base = this.distractores
                    .map((item) => String(item).trim())
                    .filter((item) => item !== '');

                const respuestaCorrecta = this.resultadoOperacion();

                if (!base.includes(respuestaCorrecta)) {
                    base.push(respuestaCorrecta);
                }

                return [...new Set(base)];
            },

            categoriaItemsValidos() {
                return this.categoriaItems.filter((item) => {
                    return String(item.elemento || '').trim() !== '' && String(item.categoria || '').trim() !== '';
                });
            },

            opcionesValidas() {
                return this.opciones
                    .map((item) => String(item).trim())
                    .filter((item) => item !== '');
            },

            tipoPreguntaLabel() {
                const labels = {
                    opcion_multiple: 'Opcion multiple',
                    verdadero_falso: 'Verdadero o falso',
                    emparejamiento: 'Emparejamiento',
                    ordenamiento: 'Ordenamiento',
                };

                return labels[this.tipoPregunta] || 'Pregunta';
            },
        };
    }
</script>
@endpush
