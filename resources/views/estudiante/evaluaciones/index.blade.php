@extends('layouts.app')

@section('title', 'Mis Evaluaciones')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mis Evaluaciones</h1>
        <p class="text-gray-600 mt-2">Revisa tus evaluaciones y resultados</p>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Evaluaciones</p>
                    <p class="text-3xl font-bold">{{ $estadisticas['total_evaluaciones'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Aprobadas</p>
                    <p class="text-3xl font-bold">{{ $estadisticas['aprobadas'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Reprobadas</p>
                    <p class="text-3xl font-bold">{{ $estadisticas['reprobadas'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Promedio</p>
                    <p class="text-3xl font-bold">{{ number_format($estadisticas['promedio'] ?? 0, 1) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <a href="?tab=pendientes" class="px-6 py-4 border-b-2 {{ request('tab', 'pendientes') === 'pendientes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium">
                    Pendientes
                </a>
                <a href="?tab=completadas" class="px-6 py-4 border-b-2 {{ request('tab') === 'completadas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium">
                    Completadas
                </a>
            </nav>
        </div>

        <div class="p-6">
            @if(request('tab', 'pendientes') === 'pendientes')
                @if($evaluacionesPendientes->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($evaluacionesPendientes as $evaluacion)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="inline-block px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-medium mb-2">
                                            {{ $evaluacion->tipo }}
                                        </span>
                                        <h3 class="font-medium text-gray-800">{{ $evaluacion->titulo }}</h3>
                                        <p class="text-sm text-gray-500">{{ $evaluacion->tema->asignatura->nombre }}</p>
                                        <p class="text-sm text-gray-400">{{ $evaluacion->tema->titulo }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <div class="text-sm text-gray-500">
                                        <span>{{ $evaluacion->preguntas->count() }} preguntas</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $evaluacion->tiempo_limite_minutos }} min</span>
                                    </div>
                                    <a href="{{ route('estudiante.evaluaciones.take', $evaluacion) }}" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                        Realizar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg text-gray-600">¡No tienes evaluaciones pendientes!</p>
                        <p class="text-gray-400">Has completado todas tus evaluaciones</p>
                    </div>
                @endif
            @else
                @if($resultados->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluación</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intentos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($resultados as $resultado)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $resultado->evaluacion->titulo }}</div>
                                            <div class="text-sm text-gray-500">{{ $resultado->evaluacion->tipo }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $resultado->evaluacion->tema->asignatura->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $puntajeObtenido = $resultado->puntaje_obtenido ?? $resultado->puntuacion_obtenida ?? 0;
                                                $puntajeTotal = $resultado->puntaje_total
                                                    ?? $resultado->puntuacion_total
                                                    ?? ($resultado->evaluacion->puntaje_total ?? 0);
                                                $nota = $puntajeTotal > 0 ? ($puntajeObtenido / $puntajeTotal) * 100 : 0;
                                            @endphp
                                            <span class="text-lg font-bold {{ $nota >= 60 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($nota, 1) }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $resultado->aprobado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $resultado->aprobado ? 'Aprobado' : 'Reprobado' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $resultado->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $resultado->intento_numero }} / {{ $resultado->evaluacion->intentos_permitidos }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($resultados->hasPages())
                        <div class="mt-4">
                            {{ $resultados->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg text-gray-600">No has realizado evaluaciones</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
