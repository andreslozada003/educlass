@extends('layouts.app')

@section('title', 'Crear Juego')

@php
    $temasCatalogo = $temas->map(function ($tema) {
        return [
            'id' => (string) $tema->id,
            'titulo' => $tema->titulo,
            'asignatura_slug' => optional($tema->asignatura)->slug,
            'asignatura_nombre' => optional($tema->asignatura)->nombre,
        ];
    })->values();
@endphp

@section('content')
<div class="max-w-7xl mx-auto" x-data="juegoBuilder()" x-init="init()">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('docente.juegos.index') }}" class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">Crear nuevo juego</h1>
            <p class="mt-1 text-gray-500">Configura la experiencia y mira una vista previa en tiempo real antes de guardarla.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
            <p class="mb-2 font-semibold">Revisa estos datos antes de guardar:</p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('docente.juegos.store') }}" class="grid grid-cols-1 gap-8 xl:grid-cols-[1.15fr_0.85fr]">
        @csrf

        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Paso 1</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Datos principales</h2>
                </div>

                <div class="space-y-6 p-6">
                    <div>
                        <label for="tema_id" class="mb-2 block text-sm font-medium text-gray-700">Tema <span class="text-red-500">*</span></label>
                        <select
                            name="tema_id"
                            id="tema_id"
                            x-model="form.tema_id"
                            @change="applyRecommendedType()"
                            required
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                        >
                            <option value="">Selecciona un tema</option>
                            @foreach($temas as $tema)
                                <option value="{{ $tema->id }}" {{ old('tema_id', $temaPreseleccionado) == $tema->id ? 'selected' : '' }}>
                                    {{ $tema->asignatura->icono }} {{ $tema->titulo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="titulo" class="mb-2 block text-sm font-medium text-gray-700">Titulo <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="titulo"
                            id="titulo"
                            x-model="form.titulo"
                            value="{{ old('titulo') }}"
                            required
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="Ej: Matematica aventura del tesoro"
                        >
                    </div>

                    <div>
                        <label for="descripcion" class="mb-2 block text-sm font-medium text-gray-700">Descripcion</label>
                        <textarea
                            name="descripcion"
                            id="descripcion"
                            rows="4"
                            x-model="form.descripcion"
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="Describe el objetivo del juego y la experiencia del estudiante..."
                        >{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Paso 2</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Elige el tipo de juego</h2>
                    <p class="mt-1 text-sm text-gray-500">Cada tipo cambia la experiencia del estudiante. Al escoger uno, la vista previa y el constructor se adaptan a esa forma de jugar.</p>
                </div>

                <div class="p-6">
                    <div
                        x-show="selectedSubjectRecommendation()"
                        x-cloak
                        class="mb-6 overflow-hidden rounded-3xl border border-cyan-100 bg-cyan-50/60"
                    >
                        <div class="grid grid-cols-1 gap-0 lg:grid-cols-[1.05fr_0.95fr]">
                            <div class="border-b border-cyan-100 p-6 lg:border-b-0 lg:border-r">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-700">Recomendacion por asignatura</p>
                                <h3 class="mt-3 text-2xl font-display font-semibold text-slate-900" x-text="recommendedTitle()"></h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    Las mejores categorias son:
                                </p>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <template x-for="categoria in recommendedCategories()" :key="categoria">
                                        <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-cyan-700 shadow-sm" x-text="categoria"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="p-6">
                                <p class="text-sm font-semibold text-slate-800">Porque ayudan con:</p>
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <template x-for="habilidad in recommendedSkills()" :key="habilidad">
                                        <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                                                <i class="fas fa-check text-xs"></i>
                                            </span>
                                            <span class="text-sm font-medium text-slate-700" x-text="habilidad"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($tipos as $key => $tipo)
                            <label class="group cursor-pointer">
                                <input
                                    type="radio"
                                    name="tipo"
                                    value="{{ $key }}"
                                    x-model="form.tipo"
                                    @change="markTypeSelection()"
                                    {{ old('tipo', $tipoPredeterminado) === $key ? 'checked' : '' }}
                                    required
                                    class="peer sr-only"
                                >
                                <div class="h-full rounded-3xl border border-gray-200 p-5 transition-all hover:border-cyan-300 hover:shadow-md peer-checked:border-cyan-500 peer-checked:bg-cyan-50/60">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-3xl leading-none">{{ $tipo['icono'] }}</div>
                                            <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $tipo['nombre'] }}</h3>
                                        </div>
                                        @if($key === 'matematica_aventura')
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                                Nuevo
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-gray-500">{{ $tipo['descripcion'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-6 overflow-hidden rounded-3xl border border-gray-200 bg-slate-900 text-white shadow-lg">
                        <div class="grid grid-cols-1 gap-0 lg:grid-cols-[1.1fr_0.9fr]">
                            <div class="border-b border-white/10 p-6 lg:border-b-0 lg:border-r">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-300">Experiencia seleccionada</p>
                                <h3 class="mt-3 text-2xl font-display font-semibold" x-text="selectedExperience().headline"></h3>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300" x-text="selectedExperience().description"></p>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <template x-for="highlight in selectedExperience().highlights" :key="highlight">
                                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-100" x-text="highlight"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Lo que crearas despues</p>
                                <div class="mt-4 space-y-3">
                                    <template x-for="step in selectedExperience().builderSteps" :key="step">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200" x-text="step"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Paso 3</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Configuracion general</h2>
                </div>

                <div class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="dificultad" class="mb-2 block text-sm font-medium text-gray-700">Dificultad</label>
                            <select
                                name="dificultad"
                                id="dificultad"
                                x-model="form.dificultad"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                                required
                            >
                                <option value="1">1 - Basico</option>
                                <option value="2">2 - Intermedio</option>
                                <option value="3">3 - Avanzado</option>
                                <option value="4">4 - Experto</option>
                            </select>
                        </div>

                        <div>
                            <label for="intentos_maximos" class="mb-2 block text-sm font-medium text-gray-700">Intentos maximos</label>
                            <input
                                type="number"
                                name="intentos_maximos"
                                id="intentos_maximos"
                                x-model="form.intentos_maximos"
                                value="{{ old('intentos_maximos', 5) }}"
                                min="1"
                                max="10"
                                required
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            >
                        </div>

                        <div>
                            <label for="puntaje_base" class="mb-2 block text-sm font-medium text-gray-700">Puntaje base</label>
                            <input
                                type="number"
                                name="puntaje_base"
                                id="puntaje_base"
                                x-model="form.puntaje_base"
                                value="{{ old('puntaje_base', 100) }}"
                                min="10"
                                required
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="tiempo_limite_segundos" class="mb-2 block text-sm font-medium text-gray-700">Tiempo limite en segundos</label>
                            <input
                                type="number"
                                name="tiempo_limite_segundos"
                                id="tiempo_limite_segundos"
                                x-model="form.tiempo_limite_segundos"
                                value="{{ old('tiempo_limite_segundos') }}"
                                min="30"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                                placeholder="Ej: 180"
                            >
                            <p class="mt-2 text-sm text-gray-500">Dejalo vacio si quieres jugar sin limite de tiempo.</p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <label for="activo" class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="activo"
                                    id="activo"
                                    value="1"
                                    x-model="form.activo"
                                    {{ old('activo') ? 'checked' : '' }}
                                    class="mt-1 h-5 w-5 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500"
                                >
                                <div>
                                    <p class="font-medium text-gray-800">Publicar al guardar</p>
                                    <p class="mt-1 text-sm text-gray-500">Si lo activas, los estudiantes lo veran disponible apenas termines de agregar el contenido.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div
                x-show="form.tipo === 'matematica_aventura'"
                x-cloak
                class="overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-sm"
            >
                <div class="border-b border-emerald-100 bg-emerald-50 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Modo especial</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Matematica aventura</h2>
                    <p class="mt-1 text-sm text-gray-600">Define la operacion principal, la meta del recorrido y la recompensa por cada acierto.</p>
                </div>

                <div class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="operacion_principal" class="mb-2 block text-sm font-medium text-gray-700">Operacion principal</label>
                            <select
                                name="operacion_principal"
                                id="operacion_principal"
                                x-model="form.operacion_principal"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="suma">Sumas</option>
                                <option value="resta">Restas</option>
                                <option value="multiplicacion">Multiplicaciones</option>
                                <option value="division">Divisiones</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>

                        <div>
                            <label for="objetivo_aventura" class="mb-2 block text-sm font-medium text-gray-700">Objetivo de la aventura</label>
                            <select
                                name="objetivo_aventura"
                                id="objetivo_aventura"
                                x-model="form.objetivo_aventura"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="puente">Cruzar puentes</option>
                                <option value="cofre">Abrir cofres</option>
                                <option value="obstaculo">Derrotar obstaculos</option>
                            </select>
                        </div>

                        <div>
                            <label for="recompensa_principal" class="mb-2 block text-sm font-medium text-gray-700">Recompensa principal</label>
                            <select
                                name="recompensa_principal"
                                id="recompensa_principal"
                                x-model="form.recompensa_principal"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="monedas">Monedas</option>
                                <option value="energia">Energia</option>
                                <option value="ambas">Monedas y energia</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="monedas_por_acierto" class="mb-2 block text-sm font-medium text-gray-700">Monedas por acierto</label>
                            <input
                                type="number"
                                name="monedas_por_acierto"
                                id="monedas_por_acierto"
                                x-model="form.monedas_por_acierto"
                                value="{{ old('monedas_por_acierto', 15) }}"
                                min="0"
                                max="500"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div>
                            <label for="energia_por_acierto" class="mb-2 block text-sm font-medium text-gray-700">Energia por acierto</label>
                            <input
                                type="number"
                                name="energia_por_acierto"
                                id="energia_por_acierto"
                                x-model="form.energia_por_acierto"
                                value="{{ old('energia_por_acierto', 10) }}"
                                min="0"
                                max="100"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>
                    </div>

                    <div class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
                        <p class="font-semibold">Sugerencia de uso</p>
                        <p class="mt-1">Despues de guardar, crea operaciones cortas con contexto de puente, cofre u obstaculo para que la aventura se sienta real.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('docente.juegos.index') }}" class="px-6 py-3 font-medium text-gray-700 transition-colors hover:text-gray-900">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 font-medium text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5 hover:bg-cyan-600">
                    <i class="fas fa-save"></i>
                    Crear juego
                </button>
            </div>
        </div>

        <aside class="self-start space-y-6 xl:sticky xl:top-8">
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Vista previa en vivo</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-900">Asi se vera el juego</h2>
                        </div>
                        <div class="rounded-2xl px-4 py-2 text-sm font-semibold text-white" :style="`background:${selectedType().color}`" x-text="selectedType().icono"></div>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div class="rounded-3xl p-5 text-white" :style="previewBackground()">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-white/70">Tipo</p>
                                <h3 class="mt-2 text-2xl font-display font-semibold" x-text="selectedType().nombre"></h3>
                                <p class="mt-2 text-sm leading-6 text-white/80" x-text="form.titulo || 'Tu titulo aparecera aqui'"></p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-4 py-3 text-2xl" x-text="selectedType().icono"></div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/60">Nivel</p>
                                <p class="mt-2 text-lg font-semibold" x-text="form.dificultad"></p>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/60">Intentos</p>
                                <p class="mt-2 text-lg font-semibold" x-text="form.intentos_maximos"></p>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/60">Puntos</p>
                                <p class="mt-2 text-lg font-semibold" x-text="form.puntaje_base"></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Experiencia del estudiante</p>
                                    <h4 class="mt-2 text-xl font-semibold text-slate-900" x-text="selectedExperience().headline"></h4>
                                    <p class="mt-2 text-sm leading-6 text-slate-600" x-text="selectedExperience().description"></p>
                                </div>
                                <div class="rounded-2xl px-4 py-3 text-sm font-semibold text-white shadow-sm" :style="`background:${selectedType().color}`" x-text="selectedType().nombre"></div>
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <template x-for="step in selectedExperience().steps" :key="step.label">
                                    <div class="rounded-2xl border border-white/70 bg-white/90 px-3 py-4 text-center shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" x-text="step.label"></p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900" x-text="step.value"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-900 p-5 text-white shadow-lg shadow-slate-200">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em] text-cyan-300" x-text="selectedExperience().sampleEyebrow"></p>
                                    <h4 class="mt-3 text-2xl font-display font-semibold" x-text="selectedExperience().sampleTitle"></h4>
                                    <p class="mt-2 text-sm text-slate-300" x-text="selectedExperience().sampleDescription"></p>
                                </div>
                                <div class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold" x-text="selectedExperience().badge"></div>
                            </div>

                            <template x-if="selectedExperience().layout === 'choices'">
                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <template x-for="option in selectedExperience().options" :key="option">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center font-medium text-slate-100" x-text="option"></div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="selectedExperience().layout === 'cards'">
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
                                        <template x-for="index in 6" :key="index">
                                            <div class="aspect-[0.8] rounded-[1.3rem] border border-violet-300/50 bg-gradient-to-br from-violet-500 to-fuchsia-700 p-2 shadow-lg">
                                                <div class="flex h-full items-center justify-center rounded-[1rem] border border-white/10 bg-white/10 text-4xl font-black text-white">?</div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedExperience().layout === 'buckets'">
                                <div class="mt-5 space-y-3">
                                    <template x-for="bucket in selectedExperience().buckets" :key="bucket.title">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="font-semibold text-white" x-text="bucket.title"></span>
                                                <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-semibold text-cyan-200" x-text="bucket.sample"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="selectedExperience().layout === 'sequence'">
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <template x-for="(item, index) in selectedExperience().sequence" :key="`${item}-${index}`">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-slate-100" x-text="`${index + 1}. ${item}`"></div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="selectedExperience().layout === 'grid'">
                                <div class="mt-5 space-y-4">
                                    <div class="grid grid-cols-5 gap-2">
                                        <template x-for="(cell, index) in selectedExperience().grid" :key="`${cell}-${index}`">
                                            <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 text-center text-sm font-semibold text-slate-100" x-text="cell"></div>
                                        </template>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="word in selectedExperience().words" :key="word">
                                            <span class="rounded-full bg-rose-400/15 px-3 py-1 text-xs font-semibold text-rose-200" x-text="word"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedExperience().layout === 'fill'">
                                <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 px-4 py-5 text-lg font-medium text-slate-100" x-text="selectedExperience().fillText"></div>
                            </template>

                            <template x-if="selectedExperience().layout === 'falling'">
                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-emerald-200">Categoria A</p>
                                        <p class="mt-2 font-semibold text-white" x-text="selectedExperience().buckets[0].title"></p>
                                    </div>
                                    <div class="rounded-2xl border border-sky-400/20 bg-sky-400/10 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-sky-200">Categoria B</p>
                                        <p class="mt-2 font-semibold text-white" x-text="selectedExperience().buckets[1].title"></p>
                                    </div>
                                    <template x-for="item in selectedExperience().fallingItems" :key="item">
                                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center font-medium text-slate-100" x-text="item"></div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="form.tipo === 'matematica_aventura'">
                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl bg-amber-400/15 px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-amber-200">Monedas</p>
                                        <p class="mt-2 text-xl font-semibold text-white" x-text="`+${form.monedas_por_acierto || 0}`"></p>
                                    </div>
                                    <div class="rounded-2xl bg-emerald-400/15 px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-emerald-200">Energia</p>
                                        <p class="mt-2 text-xl font-semibold text-white" x-text="`+${form.energia_por_acierto || 0}`"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-500">
                        Despues de guardar, la pantalla de contenido se adaptara automaticamente al tipo que acabas de escoger.
                    </div>
                </div>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function juegoBuilder() {
        return {
            form: {
                tema_id: @json((string) old('tema_id', $temaPreseleccionado)),
                tipo: @json(old('tipo', $tipoPredeterminado)),
                titulo: @json(old('titulo', '')),
                descripcion: @json(old('descripcion', '')),
                dificultad: @json((string) old('dificultad', 1)),
                intentos_maximos: @json((string) old('intentos_maximos', 5)),
                puntaje_base: @json((string) old('puntaje_base', 100)),
                tiempo_limite_segundos: @json(old('tiempo_limite_segundos', '')),
                activo: @json((bool) old('activo', false)),
                operacion_principal: @json(old('operacion_principal', 'mixto')),
                objetivo_aventura: @json(old('objetivo_aventura', 'puente')),
                recompensa_principal: @json(old('recompensa_principal', 'monedas')),
                monedas_por_acierto: @json((string) old('monedas_por_acierto', 15)),
                energia_por_acierto: @json((string) old('energia_por_acierto', 10)),
            },
            tipos: @json($tipos),
            temasCatalogo: @json($temasCatalogo),
            recomendacionesAsignatura: @json($recomendacionesAsignatura),
            userSelectedType: @json(old('tipo') !== null),

            init() {
                this.applyRecommendedType(true);
            },

            markTypeSelection() {
                this.userSelectedType = true;
            },

            selectedTheme() {
                return this.temasCatalogo.find((tema) => String(tema.id) === String(this.form.tema_id)) ?? null;
            },

            selectedSubjectRecommendation() {
                const slug = this.selectedTheme()?.asignatura_slug;
                if (!slug) return null;
                return this.recomendacionesAsignatura[slug] ?? null;
            },

            recommendedTitle() {
                return this.selectedSubjectRecommendation()?.titulo ?? '';
            },

            recommendedCategories() {
                return this.selectedSubjectRecommendation()?.categorias ?? [];
            },

            recommendedSkills() {
                return this.selectedSubjectRecommendation()?.habilidades ?? [];
            },

            applyRecommendedType(force = false) {
                const recommendedType = this.selectedSubjectRecommendation()?.tipo_disponible_recomendado;

                if (!recommendedType || !this.tipos[recommendedType]) {
                    return;
                }

                if (force || !this.userSelectedType || this.form.tipo === 'matematica_aventura') {
                    this.form.tipo = recommendedType;
                }
            },

            selectedType() {
                return this.tipos[this.form.tipo] ?? {
                    nombre: 'Juego',
                    descripcion: 'Configura el juego para ver la vista previa.',
                    icono: 'J',
                    color: '#334155'
                };
            },

            previewBackground() {
                const color = this.selectedType().color ?? '#334155';
                return `background: linear-gradient(135deg, ${color} 0%, #0f172a 100%)`;
            },

            operationLabel() {
                const labels = {
                    suma: 'Sumas',
                    resta: 'Restas',
                    multiplicacion: 'Multiplicaciones',
                    division: 'Divisiones',
                    mixto: 'Mixto',
                };

                return labels[this.form.operacion_principal] ?? 'Mixto';
            },

            goalTitle() {
                const labels = {
                    puente: 'Cruza los puentes numericos',
                    cofre: 'Abre los cofres del tesoro',
                    obstaculo: 'Vence los obstaculos del camino',
                };

                return labels[this.form.objetivo_aventura] ?? labels.puente;
            },

            rewardSummary() {
                const reward = this.form.recompensa_principal;

                if (reward === 'energia') {
                    return `+${this.form.energia_por_acierto || 0} energia`;
                }

                if (reward === 'ambas') {
                    return `+${this.form.monedas_por_acierto || 0} monedas + ${this.form.energia_por_acierto || 0} energia`;
                }

                return `+${this.form.monedas_por_acierto || 0} monedas`;
            },

            sampleQuestion() {
                const samples = {
                    suma: { question: '8 + 7 = ?', options: [13, 15, 16, 18] },
                    resta: { question: '19 - 6 = ?', options: [11, 12, 13, 14] },
                    multiplicacion: { question: '4 x 6 = ?', options: [20, 22, 24, 26] },
                    division: { question: '24 / 6 = ?', options: [3, 4, 5, 6] },
                    mixto: { question: '9 + 5 - 3 = ?', options: [9, 10, 11, 12] },
                };

                return samples[this.form.operacion_principal] ?? samples.mixto;
            },

            selectedExperience() {
                const experiences = {
                    quiz: {
                        headline: 'Preguntas con opciones y respuesta inmediata',
                        description: 'Ideal para repasar conceptos con preguntas rapidas, respuestas visibles y puntaje por cada acierto.',
                        highlights: ['Opciones visibles', 'Feedback inmediato', 'Repaso directo'],
                        builderSteps: [
                            'Crear enunciados y respuestas correctas',
                            'Agregar opciones visibles para cada pregunta',
                            'Organizar el recorrido del estudiante por puntaje'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Lee' },
                            { label: 'Paso 2', value: 'Responde' },
                            { label: 'Paso 3', value: 'Gana' },
                        ],
                        sampleEyebrow: 'Pregunta ejemplo',
                        sampleTitle: 'Selecciona la opcion correcta',
                        sampleDescription: 'El estudiante vera una pregunta con varias respuestas posibles.',
                        badge: 'Quiz',
                        layout: 'choices',
                        options: ['Opcion A', 'Opcion B', 'Opcion C', 'Opcion D'],
                    },
                    memoria: {
                        headline: 'Cartas para recordar y encontrar parejas',
                        description: 'El estudiante observa cartas, recuerda posiciones y encuentra pares correctos para avanzar.',
                        highlights: ['Memoria visual', 'Pares de contenido', 'Reconocimiento rapido'],
                        builderSteps: [
                            'Crear una tarjeta principal y su pareja correcta',
                            'Usar contenidos cortos y faciles de reconocer',
                            'Preparar un tablero de memoria visual'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Observa' },
                            { label: 'Paso 2', value: 'Recuerda' },
                            { label: 'Paso 3', value: 'Empareja' },
                        ],
                        sampleEyebrow: 'Tablero ejemplo',
                        sampleTitle: 'Encuentra cada pareja',
                        sampleDescription: 'Las cartas se mezclan y el nino debe recordar donde esta cada respuesta.',
                        badge: 'Memoria',
                        layout: 'cards',
                        cards: ['7 + 3', '10', 'Perro', 'Animal'],
                    },
                    arrastrar: {
                        headline: 'Mover elementos a su lugar correcto',
                        description: 'El estudiante arrastra cada elemento hasta la categoria adecuada y gana puntos por clasificar bien.',
                        highlights: ['Arrastrar y soltar', 'Categorias claras', 'Aprendizaje visual'],
                        builderSteps: [
                            'Definir una consigna clara',
                            'Agregar elementos con su categoria correcta',
                            'Preparar zonas donde el estudiante soltara cada elemento'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Lee' },
                            { label: 'Paso 2', value: 'Arrastra' },
                            { label: 'Paso 3', value: 'Ubica' },
                        ],
                        sampleEyebrow: 'Ejemplo de clasificacion',
                        sampleTitle: 'Lleva cada elemento a su categoria',
                        sampleDescription: 'La experiencia muestra elementos sueltos y zonas de destino.',
                        badge: 'Arrastrar',
                        layout: 'buckets',
                        buckets: [
                            { title: 'Mamiferos', sample: 'Perro' },
                            { title: 'Aves', sample: 'Loro' },
                        ],
                    },
                    completar: {
                        headline: 'Completar frases, palabras u operaciones',
                        description: 'El estudiante lee una frase o ejercicio y completa la parte faltante con la respuesta correcta.',
                        highlights: ['Respuesta directa', 'Palabras faltantes', 'Comprension rapida'],
                        builderSteps: [
                            'Escribir frases o ejercicios con espacios por completar',
                            'Agregar la respuesta correcta en cada bloque',
                            'Trabajar lectura, lenguaje o calculo'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Lee' },
                            { label: 'Paso 2', value: 'Completa' },
                            { label: 'Paso 3', value: 'Verifica' },
                        ],
                        sampleEyebrow: 'Frase ejemplo',
                        sampleTitle: 'Completa la idea',
                        sampleDescription: 'La pantalla muestra una frase con una parte faltante.',
                        badge: 'Completar',
                        layout: 'fill',
                        fillText: '2 + 3 = ____',
                    },
                    ordenar: {
                        headline: 'Ordenar pasos, eventos o secuencias',
                        description: 'El estudiante debe colocar cada elemento en la posicion correcta de una secuencia.',
                        highlights: ['Secuencias logicas', 'Pasos ordenados', 'Pensamiento cronologico'],
                        builderSteps: [
                            'Agregar eventos o pasos uno por uno',
                            'Definir la posicion correcta de cada elemento',
                            'Construir una linea de orden para el estudiante'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Observa' },
                            { label: 'Paso 2', value: 'Ordena' },
                            { label: 'Paso 3', value: 'Confirma' },
                        ],
                        sampleEyebrow: 'Secuencia ejemplo',
                        sampleTitle: 'Ordena los eventos',
                        sampleDescription: 'Cada bloque debe quedar en la posicion correcta.',
                        badge: 'Ordenar',
                        layout: 'sequence',
                        sequence: ['Semilla', 'Brote', 'Planta', 'Flor'],
                    },
                    sopa: {
                        headline: 'Buscar palabras escondidas con pistas',
                        description: 'El estudiante explora una cuadricula y encuentra palabras a partir de pistas cortas.',
                        highlights: ['Busqueda visual', 'Pistas tematicas', 'Reconocimiento de palabras'],
                        builderSteps: [
                            'Escribir la pista de cada palabra',
                            'Agregar la palabra correcta que se escondera',
                            'Preparar un reto de observacion y lectura'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Busca' },
                            { label: 'Paso 2', value: 'Encuentra' },
                            { label: 'Paso 3', value: 'Marca' },
                        ],
                        sampleEyebrow: 'Sopa ejemplo',
                        sampleTitle: 'Encuentra las palabras del tema',
                        sampleDescription: 'La cuadricula esconde palabras y el estudiante las detecta con apoyo de pistas.',
                        badge: 'Sopa',
                        layout: 'grid',
                        grid: ['P', 'E', 'R', 'R', 'O', 'A', 'R', 'B', 'O', 'L', 'L', 'U', 'N', 'A', 'S', 'O', 'L', 'M', 'A', 'R', 'C', 'A', 'S', 'A', 'R'],
                        words: ['PERRO', 'ARBOL', 'LUNA'],
                    },
                    clasificar: {
                        headline: 'Clasificar rapido mientras caen elementos',
                        description: 'El estudiante decide rapidamente a que categoria pertenece cada item y gana puntos por velocidad y precision.',
                        highlights: ['Decision rapida', 'Categorias visuales', 'Ritmo dinamico'],
                        builderSteps: [
                            'Definir categorias faciles de distinguir',
                            'Agregar elementos con una unica respuesta correcta',
                            'Preparar una experiencia de clasificacion agil'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Observa' },
                            { label: 'Paso 2', value: 'Clasifica' },
                            { label: 'Paso 3', value: 'Avanza' },
                        ],
                        sampleEyebrow: 'Pantalla ejemplo',
                        sampleTitle: 'Decide rapido donde va cada elemento',
                        sampleDescription: 'Los items aparecen y el estudiante debe enviarlos a la categoria correcta.',
                        badge: 'Clasificar',
                        layout: 'falling',
                        buckets: [
                            { title: 'Seres vivos', sample: 'Perro' },
                            { title: 'Objetos', sample: 'Mesa' },
                        ],
                        fallingItems: ['Perro', 'Mesa', 'Arbol', 'Libro'],
                    },
                    matematica_aventura: {
                        headline: 'Resolver operaciones para avanzar en una aventura',
                        description: 'Cada acierto ayuda a cruzar puentes, abrir cofres o vencer obstaculos mientras el estudiante gana recompensas.',
                        highlights: ['Narrativa matematica', 'Recompensas por acierto', 'Operacion configurable'],
                        builderSteps: [
                            'Elegir la operacion principal de la aventura',
                            'Definir si el estudiante cruzara puentes, abrira cofres o vencera obstaculos',
                            'Configurar monedas y energia por cada acierto'
                        ],
                        steps: [
                            { label: 'Paso 1', value: 'Resuelve' },
                            { label: 'Paso 2', value: 'Avanza' },
                            { label: 'Paso 3', value: 'Gana' },
                        ],
                        sampleEyebrow: 'Mision matematica',
                        sampleTitle: this.goalTitle(),
                        sampleDescription: `Operacion principal: ${this.operationLabel()}`,
                        badge: this.rewardSummary(),
                        layout: 'choices',
                        options: this.sampleQuestion().options,
                    },
                };

                return experiences[this.form.tipo] ?? experiences.quiz;
            },
        };
    }
</script>
@endpush
