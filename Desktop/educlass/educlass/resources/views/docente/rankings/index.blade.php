@extends('layouts.app')

@section('title', 'Rankings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Rankings de Estudiantes</h1>
        <p class="text-gray-600 mt-2">Visualiza el desempeño de los estudiantes por asignatura y nivel</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="{{ route('docente.rankings.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asignatura</label>
                <select name="asignatura_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas las asignaturas</option>
                    @foreach($asignaturas as $asignatura)
                        <option value="{{ $asignatura->id }}" {{ request('asignatura_id') == $asignatura->id ? 'selected' : '' }}>
                            {{ $asignatura->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nivel</label>
                <select name="nivel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos los niveles</option>
                    @foreach([1, 2, 3, 4] as $nivel)
                        <option value="{{ $nivel }}" {{ request('nivel') == $nivel ? 'selected' : '' }}>
                            Nivel {{ $nivel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Top 3 Podio -->
    @if($topEstudiantes->count() >= 3)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Segundo Lugar -->
            <div class="bg-white rounded-lg shadow-lg p-6 text-center order-1 md:order-1">
                <div class="relative inline-block">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-300">
                        @if($topEstudiantes[1]->estudiante->avatar)
                            <img src="{{ Storage::url($topEstudiantes[1]->estudiante->avatar) }}" class="w-full h-full rounded-full object-cover">
                        @else
                            <span class="text-3xl font-bold text-gray-600">{{ substr($topEstudiantes[1]->estudiante->nombre, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-bold text-lg">2</div>
                </div>
                <h3 class="mt-4 text-lg font-bold text-gray-800">{{ $topEstudiantes[1]->estudiante->nombre }} {{ $topEstudiantes[1]->estudiante->apellido }}</h3>
                <p class="text-gray-600">{{ $topEstudiantes[1]->puntaje_total }} puntos</p>
                <p class="text-sm text-gray-500">Nivel {{ $topEstudiantes[1]->nivel_alcanzado ?? 1 }}</p>
            </div>

            <!-- Primer Lugar -->
            <div class="bg-gradient-to-b from-yellow-50 to-yellow-100 rounded-lg shadow-xl p-6 text-center order-first md:order-2 transform md:-translate-y-4">
                <div class="relative inline-block">
                    <div class="w-32 h-32 mx-auto rounded-full bg-yellow-200 flex items-center justify-center border-4 border-yellow-400">
                        @if($topEstudiantes[0]->estudiante->avatar)
                            <img src="{{ Storage::url($topEstudiantes[0]->estudiante->avatar) }}" class="w-full h-full rounded-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-yellow-700">{{ substr($topEstudiantes[0]->estudiante->nombre, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold text-xl">1</div>
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <svg class="w-10 h-10 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-4 text-xl font-bold text-gray-800">{{ $topEstudiantes[0]->estudiante->nombre }} {{ $topEstudiantes[0]->estudiante->apellido }}</h3>
                <p class="text-yellow-700 font-semibold text-lg">{{ $topEstudiantes[0]->puntaje_total }} puntos</p>
                <p class="text-sm text-gray-600">Nivel {{ $topEstudiantes[0]->nivel_alcanzado ?? 1 }}</p>
            </div>

            <!-- Tercer Lugar -->
            <div class="bg-white rounded-lg shadow-lg p-6 text-center order-3">
                <div class="relative inline-block">
                    <div class="w-24 h-24 mx-auto rounded-full bg-orange-100 flex items-center justify-center border-4 border-orange-300">
                        @if($topEstudiantes[2]->estudiante->avatar)
                            <img src="{{ Storage::url($topEstudiantes[2]->estudiante->avatar) }}" class="w-full h-full rounded-full object-cover">
                        @else
                            <span class="text-3xl font-bold text-orange-700">{{ substr($topEstudiantes[2]->estudiante->nombre, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold text-lg">3</div>
                </div>
                <h3 class="mt-4 text-lg font-bold text-gray-800">{{ $topEstudiantes[2]->estudiante->nombre }} {{ $topEstudiantes[2]->estudiante->apellido }}</h3>
                <p class="text-gray-600">{{ $topEstudiantes[2]->puntaje_total }} puntos</p>
                <p class="text-sm text-gray-500">Nivel {{ $topEstudiantes[2]->nivel_alcanzado ?? 1 }}</p>
            </div>
        </div>
    @endif

    <!-- Tabla de Rankings -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Clasificación Completa</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posición</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos Totales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juegos Completados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($rankings as $index => $ranking)
                        <tr class="hover:bg-gray-50 {{ $index < 3 ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($index == 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-yellow-400 text-white rounded-full font-bold">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-400 text-white rounded-full font-bold">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-orange-400 text-white rounded-full font-bold">3</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-700 rounded-full font-medium">{{ $ranking->posicion }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($ranking->estudiante->avatar)
                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($ranking->estudiante->avatar) }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">{{ substr($ranking->estudiante->nombre, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ranking->estudiante->nombre }} {{ $ranking->estudiante->apellido }}</div>
                                        <div class="text-sm text-gray-500">{{ $ranking->estudiante->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $ranking->asignatura->nombre ?? 'General' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Nivel {{ $ranking->nivel_alcanzado ?? 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-blue-600">{{ $ranking->puntaje_total }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $ranking->juegos_completados ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $ranking->evaluaciones_completadas ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $porcentajeProgreso = ($ranking->puntaje_total ?? 0) > 0
                                        ? min(($ranking->nivel_alcanzado ?? 1) * 25, 100)
                                        : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5 w-24">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentajeProgreso }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 mt-1">{{ $porcentajeProgreso }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-lg">No hay rankings disponibles</p>
                                <p class="text-sm text-gray-400 mt-1">Los estudiantes deben completar actividades para aparecer en el ranking</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rankings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $rankings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
