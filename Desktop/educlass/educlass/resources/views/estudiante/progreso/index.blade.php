@extends('layouts.app')

@section('title', 'Mi Progreso')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mi Progreso</h1>
        <p class="text-gray-600 mt-2">Visualiza tu avance en todas las asignaturas</p>
    </div>

    <!-- Resumen General -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Asignaturas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $resumen['asignaturas'] ?? 0  }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Temas Completados</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $resumen['temas_completados'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Puntos Totales</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $resumen['puntos_totales'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Nivel Máximo</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $resumen['nivel_maximo'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progreso por Asignatura -->
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Progreso por Asignatura</h2>
      

        @foreach($progresoPorAsignatura as $item)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <span class="text-4xl mr-4">{{ $item['asignatura']->icono ?? '📚' }}</span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $item['asignatura']->nombre }}</h3>
                            <p class="text-gray-500">Nivel {{ $item['progreso']->nivel_actual ?? 1 }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-blue-600">{{ $item['progreso']->porcentaje_completado ?? 0 }}%</p>
                        <p class="text-sm text-gray-500">completado</p>
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-4 rounded-full transition-all duration-500" style="width: {{ $item['progreso']->porcentaje_completado ?? 0 }}%"></div>
                </div>

                <!-- Detalles -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-800">{{ $item['progreso']->temas_completados ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Temas completados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $item['progreso']->puntos_acumulados ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Puntos ganados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $item['juegos_completados'] }}</p>
                        <p class="text-sm text-gray-500">Juegos completados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ $item['evaluaciones_aprobadas'] }}/{{ $item['evaluaciones_totales'] }}</p>
                        <p class="text-sm text-gray-500">Evaluaciones aprobadas</p>
                    </div>
                </div>

                <!-- Niveles -->
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        @foreach([1, 2, 3, 4] as $nivel)
                            @php
                                $nivelCompletado = ($item['progreso']->nivel_actual ?? 1) > $nivel;
                                $nivelActual = ($item['progreso']->nivel_actual ?? 1) == $nivel;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $nivelCompletado ? 'bg-green-500 text-white' : ($nivelActual ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                                    @if($nivelCompletado)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <span class="font-bold">{{ $nivel }}</span>
                                    @endif
                                </div>
                                @if($nivel < 4)
                                    <div class="w-16 md:w-24 h-1 {{ ($item['progreso']->nivel_actual ?? 1) > $nivel ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Acción -->
                <div class="mt-6 text-right">
                    <a href="{{ route('estudiante.asignaturas.show', $item['asignatura']) }}" 
                        class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                        Continuar aprendiendo
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Logros Recientes -->
    @if($logrosRecientes->count() > 0)
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Logros Recientes</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($logrosRecientes as $logro)
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4 text-center">
                        <div class="text-4xl mb-2">{{ $logro->logro->icono }}</div>
                        <h4 class="font-medium text-gray-800">{{ $logro->logro->nombre }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $logro->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
