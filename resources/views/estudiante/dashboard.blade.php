@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-700 rounded-2xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    ¡Hola, {{ auth()->user()->nombre }}! 👋
                </h1>
                <p class="text-primary-100">
                    Continúa tu aprendizaje. Tienes {{ $resumenProgreso['temas_completados'] }} temas completados.
                </p>
            </div>
            <div class="flex items-center gap-4">
                <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2">
                    <span class="text-sm text-primary-100">Nivel</span>
                    <p class="text-2xl font-bold">{{ $resumenProgreso['nivel_global'] }}</p>
                </div>
                @if($posicionRanking)
                    <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2">
                        <span class="text-sm text-primary-100">Ranking</span>
                        <p class="text-2xl font-bold">#{{ $posicionRanking->posicion }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Temas Completados</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $resumenProgreso['temas_completados'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $resumenProgreso['porcentaje_general'] }}%"></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Juegos Completados</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $estadisticas['total_juegos'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-gamepad text-blue-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                Puntaje total: <span class="font-semibold text-blue-600">{{ $estadisticas['puntaje_total_juegos'] }}</span>
            </p>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Evaluaciones</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $estadisticas['evaluaciones_aprobadas'] }}/{{ $estadisticas['total_evaluaciones'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-purple-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                Tasa de aprobación: <span class="font-semibold text-purple-600">{{ $estadisticas['tasa_aprobacion'] }}%</span>
            </p>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Logros</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $estadisticas['logros_obtenidos'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trophy text-yellow-500 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">
                Sigue aprendiendo para ganar más
            </p>
        </div>
    </div>
    
    <!-- Asignaturas -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-4">Mis Asignaturas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($progresoAsignaturas as $item)
                <a href="{{ route('estudiante.asignaturas.show', $item['asignatura']->slug) }}" 
                   class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl"
                             style="background-color: {{ $item['asignatura']->color_secundario }}20">
                            {{ $item['asignatura']->icono }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 group-hover:text-primary-600 transition-colors">
                                {{ $item['asignatura']->nombre }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $item['progreso']['temas_completados'] }} de {{ $item['progreso']['total_temas'] }} temas
                            </p>
                            <div class="mt-3">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-500">Progreso</span>
                                    <span class="font-semibold" style="color: {{ $item['asignatura']->color_primario }}">
                                        {{ $item['progreso']['porcentaje'] }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500" 
                                         style="width: {{ $item['progreso']['porcentaje'] }}%; background-color: {{ $item['asignatura']->color_primario }}"></div>
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary-500 transition-colors"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    
    <!-- Recent Activity & Achievements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Topics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Actividad Reciente</h3>
            </div>
            <div class="p-6">
                @if($temasRecientes->count() > 0)
                    <div class="space-y-4">
                        @foreach($temasRecientes as $progreso)
                            @php
                                $tema = $progreso->tema;
                                $asignatura = $tema?->asignatura;
                            @endphp

                            @if($tema && $asignatura)
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                         style="background-color: {{ $asignatura->color_secundario }}20">
                                        {{ $asignatura->icono }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">{{ $tema->titulo }}</p>
                                        <p class="text-sm text-gray-500">{{ $asignatura->nombre }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        {{ $progreso->estado === 'completado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $progreso->estado === 'completado' ? 'Completado' : 'En progreso' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay actividad reciente</p>
                @endif
            </div>
        </div>
        
        <!-- Recent Achievements -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Logros Recientes</h3>
                <a href="{{ route('estudiante.progreso.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    Ver todos
                </a>
            </div>
            <div class="p-6">
                @if($logrosRecientes->count() > 0)
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($logrosRecientes as $logroEstudiante)
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <div class="text-4xl mb-2">{{ $logroEstudiante->logro->icono }}</div>
                                <p class="font-medium text-gray-800 text-sm">{{ $logroEstudiante->logro->nombre }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $logroEstudiante->tiempo_transcurrido }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">🏆</div>
                        <p class="text-gray-500">Aún no has ganado logros</p>
                        <p class="text-sm text-gray-400 mt-1">¡Completa temas y juegos para desbloquearlos!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
