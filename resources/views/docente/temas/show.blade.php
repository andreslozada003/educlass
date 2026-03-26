@extends('layouts.app')

@section('title', $tema->titulo)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('docente.temas.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
                <span class="px-3 py-1 rounded-full text-sm"
                      style="background-color: {{ $tema->asignatura->color_secundario }}20; color: {{ $tema->asignatura->color_primario }}">
                    {{ $tema->asignatura->icono }} {{ $tema->asignatura->nombre }}
                </span>
                <span class="px-3 py-1 rounded-full text-sm
                    {{ $tema->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $tema->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $tema->titulo }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('docente.temas.edit', $tema->id) }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Nivel</p>
            <p class="text-lg font-semibold">{!! $tema->nivel_icono !!} {{ $tema->nivel_nombre }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Período</p>
            <p class="text-lg font-semibold">Período {{ $tema->periodo_academico }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Tiempo Estimado</p>
            <p class="text-lg font-semibold">{{ $tema->tiempo_estimado_minutos }} minutos</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Orden</p>
            <p class="text-lg font-semibold">#{{ $tema->orden }}</p>
        </div>
    </div>

    @if($recomendacionesCategorias)
        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em]"
                           style="color: {{ $tema->asignatura->color_primario }}">
                            Sugerencias de categorias
                        </p>
                        <h2 class="mt-2 text-2xl font-bold text-gray-900">
                            {{ $recomendacionesCategorias['titulo'] }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500">
                            Las mejores categorias para esta asignatura pueden ayudarte a diseÃ±ar experiencias mas efectivas para el tema.
                        </p>
                    </div>
                    <div class="rounded-2xl px-4 py-3 text-sm font-medium"
                         style="background-color: {{ $tema->asignatura->color_secundario }}20; color: {{ $tema->asignatura->color_primario }}">
                        {{ $tema->asignatura->icono }} Recomendacion pedagÃ³gica
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Las mejores categorÃ­as son:</h3>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach($recomendacionesCategorias['categorias'] as $categoria)
                            <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold"
                                  style="background-color: {{ $tema->asignatura->color_secundario }}20; color: {{ $tema->asignatura->color_primario }}">
                                {{ $categoria }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Porque ayudan con:</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($recomendacionesCategorias['habilidades'] as $habilidad)
                            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold"
                                      style="background-color: {{ $tema->asignatura->color_secundario }}20; color: {{ $tema->asignatura->color_primario }}">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span class="text-sm font-medium text-gray-700">{{ $habilidad }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Contenido del Tema</h2>
        </div>
        <div class="p-6 prose max-w-none">
            {!! $tema->contenido !!}
        </div>
    </div>

    <!-- Juegos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Juegos Asociados</h2>
            <a href="{{ route('docente.juegos.create', ['tema_id' => $tema->id]) }}" class="text-sm text-primary-600 hover:text-primary-700">
                <i class="fas fa-plus mr-1"></i>Agregar Juego
            </a>
        </div>
        <div class="p-6">
            @if($tema->juegos->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($tema->juegos as $juego)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">{{ $juego->tipo_icono }}</span>
                                        <h3 class="font-medium text-gray-800">{{ $juego->titulo }}</h3>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ $juego->tipo_nombre }}</p>
                                    <div class="flex gap-2 mt-2">
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $juego->preguntas->count() }} preguntas</span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $juego->intentos_maximos }} intentos</span>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded text-xs {{ $juego->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $juego->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-gamepad text-4xl mb-3"></i>
                    <p>No hay juegos asociados a este tema</p>
                    <a href="{{ route('docente.juegos.create', ['tema_id' => $tema->id]) }}" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">
                        Crear primer juego
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Evaluaciones -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Evaluaciones Asociadas</h2>
            <a href="{{ route('docente.evaluaciones.create', ['tema_id' => $tema->id]) }}" class="text-sm text-primary-600 hover:text-primary-700">
                <i class="fas fa-plus mr-1"></i>Agregar Evaluación
            </a>
        </div>
        <div class="p-6">
            @if($tema->evaluaciones->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($tema->evaluaciones as $evaluacion)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-800">{{ $evaluacion->titulo }}</h3>
                                    <p class="text-sm text-gray-500">{{ $evaluacion->tipo_nombre }}</p>
                                    <div class="flex gap-2 mt-2">
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $evaluacion->preguntas->count() }} preguntas</span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $evaluacion->tiempo_limite_minutos }} min</span>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded text-xs {{ $evaluacion->activa ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $evaluacion->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                    <p>No hay evaluaciones asociadas a este tema</p>
                    <a href="{{ route('docente.evaluaciones.create', ['tema_id' => $tema->id]) }}" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">
                        Crear primera evaluación
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
