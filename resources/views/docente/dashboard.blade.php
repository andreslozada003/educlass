@extends('layouts.app')

@section('title', 'Dashboard Docente')

@section('content')
<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-700 rounded-2xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    ¡Bienvenido, {{ auth()->user()->nombre }}! 👋
                </h1>
                <p class="text-primary-100">
                    Panel de control y gestión educativa
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('docente.temas.create') }}" 
                   class="bg-white text-primary-600 px-4 py-2 rounded-xl font-medium hover:bg-primary-50 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Nuevo Tema
                </a>
                <a href="{{ route('docente.juegos.create') }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-xl font-medium hover:bg-white/30 transition-colors">
                    <i class="fas fa-gamepad mr-2"></i>Nuevo Juego
                </a>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Estudiantes</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalEstudiantes }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('docente.estudiantes.index') }}" class="text-sm text-blue-600 mt-4 inline-block hover:underline">
                Ver todos →
            </a>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Mis Temas</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalTemas }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-open text-green-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('docente.temas.index') }}" class="text-sm text-green-600 mt-4 inline-block hover:underline">
                Gestionar →
            </a>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Juegos Creados</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalJuegos }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-gamepad text-purple-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('docente.juegos.index') }}" class="text-sm text-purple-600 mt-4 inline-block hover:underline">
                Gestionar →
            </a>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Evaluaciones</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalEvaluaciones }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-orange-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('docente.evaluaciones.index') }}" class="text-sm text-orange-600 mt-4 inline-block hover:underline">
                Gestionar →
            </a>
        </div>
    </div>
    
    <!-- Today's Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-gamepad text-2xl"></i>
                </div>
                <div>
                    <p class="text-blue-100">Juegos Jugados Hoy</p>
                    <p class="text-4xl font-bold">{{ $intentosHoy }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-green-100">Evaluaciones Hoy</p>
                    <p class="text-4xl font-bold">{{ $evaluacionesHoy }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Asignaturas Stats -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-4">Rendimiento por Asignatura</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($asignaturasStats as $item)
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                             style="background-color: {{ $item['asignatura']->color_secundario }}20">
                            {{ $item['asignatura']->icono }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $item['asignatura']->nombre }}</h3>
                            <p class="text-sm text-gray-500">{{ $item['resumen']['total_estudiantes'] }} estudiantes</p>
                        </div>
                    </div>
                    
                    @if($item['resumen']['total_estudiantes'] > 0)
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-lg font-bold text-gray-800">{{ $item['resumen']['promedio_general'] }}</p>
                                <p class="text-xs text-gray-500">Promedio</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-lg font-bold text-green-600">{{ $item['resumen']['aprobados'] }}</p>
                                <p class="text-xs text-green-600">Aprobados</p>
                            </div>
                            <div class="text-center p-3 bg-red-50 rounded-lg">
                                <p class="text-lg font-bold text-red-600">{{ $item['resumen']['reprobados'] }}</p>
                                <p class="text-xs text-red-600">Reprobados</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Tasa de aprobación</span>
                            <span class="font-semibold" style="color: {{ $item['asignatura']->color_primario }}">
                                {{ $item['resumen']['tasa_aprobacion'] }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $item['resumen']['tasa_aprobacion'] }}%; background-color: {{ $item['asignatura']->color_primario }}"></div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Sin datos disponibles</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Recent Students & Popular Topics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Students -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Estudiantes Recientes</h3>
                <a href="{{ route('docente.estudiantes.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    Ver todos
                </a>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($ultimosEstudiantes as $estudiante)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-semibold">
                                {{ $estudiante->iniciales }}
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $estudiante->nombre }}</p>
                                <p class="text-sm text-gray-500">{{ $estudiante->colegio?->nombre ?? 'Sin colegio' }}</p>
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ $estudiante->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Popular Topics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Temas Más Completados</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($temasPopulares as $tema)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                 style="background-color: {{ $tema->asignatura->color_secundario }}20">
                                {{ $tema->asignatura->icono }}
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $tema->titulo }}</p>
                                <p class="text-sm text-gray-500">{{ $tema->asignatura->nombre }}</p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                {{ $tema->completados_count }} completados
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
