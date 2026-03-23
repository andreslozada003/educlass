@extends('layouts.app')

@section('title', 'Mis Asignaturas')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mis Asignaturas</h1>
        <p class="text-gray-600 mt-2">Selecciona una asignatura para comenzar tu aprendizaje</p>
    </div>

    <!-- Grid de Asignaturas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($asignaturas as $asignatura)
            @php
                $progreso = $progresos[$asignatura->id] ?? null;
                $porcentaje = $progreso ? $progreso->porcentaje_completado : 0;
                $nivelActual = $progreso ? $progreso->nivel_actual : 1;
                $temasCompletados = $progreso ? $progreso->temas_completados : 0;
                $totalTemas = $asignatura->temasActivos->count();
            @endphp
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="h-32 bg-gradient-to-r {{ $asignatura->color ?? 'from-blue-500 to-purple-600' }} relative">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-6xl">{{ $asignatura->icono ?? '📚' }}</span>
                    </div>
                    <div class="absolute top-4 right-4">
                        <span class="bg-white/90 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                            Nivel {{ $nivelActual }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $asignatura->nombre }}</h2>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($asignatura->descripcion, 100) }}</p>
                    
                    <!-- Progreso -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Progreso</span>
                            <span class="font-medium text-gray-800">{{ $porcentaje }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $temasCompletados }} de {{ $totalTemas }} temas completados</p>
                    </div>

                    <!-- Stats -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            {{ $progreso ? $progreso->puntos_acumulados : 0 }} pts
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $asignatura->temasActivos->where('dificultad', $nivelActual)->count() }} temas disponibles
                        </div>
                    </div>

                    <!-- Botón -->
                    <a href="{{ route('estudiante.asignaturas.show', $asignatura->slug) }}" 
                        class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                        @if($porcentaje == 0)
                            Comenzar
                        @elseif($porcentaje == 100)
                            Ver Progreso
                        @else
                            Continuar
                        @endif
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
