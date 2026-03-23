@extends('layouts.app')

@section('title', $tema->titulo)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <div class="flex items-center text-gray-600">
            <a href="{{ route('estudiante.asignaturas.index') }}" class="hover:text-blue-600">Asignaturas</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('estudiante.asignaturas.show', $tema->asignatura) }}" class="hover:text-blue-600">{{ $tema->asignatura->nombre }}</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-800 font-medium">{{ $tema->titulo }}</span>
        </div>
    </div>

    <!-- Header del Tema -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        <div class="flex items-start justify-between">
            <div>
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-3">
                    Nivel {{ $tema->dificultad }}
                </span>
                <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $tema->titulo }}</h1>
                <p class="text-gray-600 max-w-2xl">{{ $tema->descripcion }}</p>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-sm text-gray-600">
                        Lectura registrada: <strong>{{ $progreso->porcentaje_lectura ?? 0 }}%</strong>
                    </span>
                    <form method="POST" action="{{ route('estudiante.temas.lectura', $tema->id) }}">
                        @csrf
                        <input type="hidden" name="porcentaje" value="100">
                        <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
                            Marcar como leído (100%)
                        </button>
                    </form>
                </div>
            </div>
            @if($temaCompletado)
                <div class="flex items-center bg-green-100 text-green-800 px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium">Completado</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Contenido Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Contenido del Tema -->
            @if($tema->contenido)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Contenido</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! $tema->contenido !!}
                    </div>
                </div>
            @endif

            @if($tema->video_url)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Video del tema</h2>

                    @if($tema->video_embed_url)
                        <div class="relative overflow-hidden rounded-xl bg-black" style="padding-top: 56.25%;">
                            <iframe
                                src="{{ $tema->video_embed_url }}"
                                title="Video de {{ $tema->titulo }}"
                                class="absolute inset-0 h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @else
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-sm text-blue-900 mb-3">
                                El video fue agregado como enlace externo.
                            </p>
                            <a href="{{ $tema->video_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ver video
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Recursos -->
            @if($tema->recursos && count($tema->recursos) > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Recursos</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($tema->recursos as $recurso)
                            <a href="{{ $recurso['url'] }}" target="_blank" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $recurso['nombre'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $recurso['tipo'] ?? 'Recurso' }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Juegos -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Juegos de Aprendizaje</h2>
                @if($tema->juegos->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($tema->juegos as $juego)
                            @php
                                $juegoCompletado = $juegosCompletados->contains($juego->id);
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 {{ $juegoCompletado ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="p-2 {{ $juegoCompletado ? 'bg-green-100' : 'bg-purple-100' }} rounded-lg">
                                        <span class="text-2xl">{{ $juego->icono_tipo }}</span>
                                    </div>
                                    @if($juegoCompletado)
                                        <span class="text-green-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-medium text-gray-800 mb-1">{{ $juego->titulo }}</h3>
                                <p class="text-sm text-gray-500 mb-3">{{ $juego->tipo }} • {{ $juego->puntos_otorgados }} pts</p>
                                <a href="{{ route('estudiante.juegos.jugar', $juego->id) }}" 
                                    class="block w-full text-center py-2 {{ $juegoCompletado ? 'bg-green-600 hover:bg-green-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white rounded-lg transition text-sm font-medium">
                                    {{ $juegoCompletado ? 'Jugar de Nuevo' : 'Jugar Ahora' }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay juegos disponibles para este tema</p>
                @endif
            </div>

            <!-- Evaluación -->
            @if($tema->evaluaciones->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Evaluación</h2>
                    @foreach($tema->evaluaciones as $evaluacion)
                        @php
                            $evaluacionCompletada = $evaluacionesCompletadas->has($evaluacion->id);
                            $resultado = $evaluacionesCompletadas->get($evaluacion->id);
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 {{ $evaluacionCompletada ? ($resultado->aprobado ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="p-3 {{ $evaluacionCompletada ? ($resultado->aprobado ? 'bg-green-100' : 'bg-red-100') : 'bg-orange-100' }} rounded-lg mr-4">
                                        <svg class="w-6 h-6 {{ $evaluacionCompletada ? ($resultado->aprobado ? 'text-green-600' : 'text-red-600') : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-800">{{ $evaluacion->titulo }}</h3>
                                        <p class="text-sm text-gray-500">{{ $evaluacion->preguntas->count() }} preguntas • {{ $evaluacion->tiempo_limite_minutos }} minutos</p>
                                        @if($evaluacionCompletada)
                                            <p class="text-sm {{ $resultado->aprobado ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                Nota: {{ number_format(($resultado->puntuacion_obtenida / $resultado->puntuacion_total) * 100, 1) }}%
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('estudiante.evaluaciones.take', $evaluacion) }}" 
                                    class="px-4 py-2 {{ $evaluacionCompletada ? ($resultado->aprobado ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700') : 'bg-orange-600 hover:bg-orange-700' }} text-white rounded-lg transition text-sm font-medium">
                                    {{ $evaluacionCompletada ? 'Reintentar' : 'Realizar' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Progreso del Tema -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-gray-800 mb-4">Tu Progreso</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Juegos completados</span>
                            <span class="font-medium">{{ $juegosCompletados->count() }}/{{ $tema->juegos->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $tema->juegos->count() > 0 ? ($juegosCompletados->count() / $tema->juegos->count()) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Evaluaciones</span>
                            <span class="font-medium">{{ $evaluacionesCompletadas->count() }}/{{ $tema->evaluaciones->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $tema->evaluaciones->count() > 0 ? ($evaluacionesCompletadas->count() / $tema->evaluaciones->count()) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Siguiente Tema -->
            @if($temaSiguiente)
                <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-md p-6 text-white">
                    <h3 class="font-bold mb-2">Siguiente Tema</h3>
                    <p class="text-white/90 text-sm mb-4">{{ $temaSiguiente->titulo }}</p>
                    <a href="{{ route('estudiante.temas.show', $temaSiguiente) }}" class="block w-full text-center bg-white text-blue-600 py-2 rounded-lg font-medium hover:bg-blue-50 transition">
                        Continuar
                    </a>
                </div>
            @elseif($temaCompletado)
                <div class="bg-green-500 rounded-xl shadow-md p-6 text-white text-center">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-bold mb-2">¡Felicitaciones!</h3>
                    <p class="text-white/90 text-sm">Has completado todos los temas de este nivel</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
