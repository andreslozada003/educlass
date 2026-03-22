@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Perfil Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-center">
                    <div class="w-32 h-32 mx-auto rounded-full bg-white flex items-center justify-center text-4xl font-bold text-blue-600 mb-4 overflow-hidden">
                        @if($estudiante->avatar)
                            <img src="{{ Storage::url('avatars/' . $estudiante->avatar) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($estudiante->nombre, 0, 1) }}{{ substr($estudiante->apellido, 0, 1) }}
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-white">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</h2>
                    <p class="text-blue-100">{{ $estudiante->email }}</p>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Colegio</span>
                            <span class="font-medium">{{ $estudiante->colegio->nombre ?? 'No especificado' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Grado</span>
                            <span class="font-medium">{{ $estudiante->grado ?? 'N/A' }}°</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Edad</span>
                            <span class="font-medium">{{ $estudiante->edad ?? 'N/A' }} años</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Miembro desde</span>
                            <span class="font-medium">{{ $estudiante->created_at->format('M Y') }}</span>
                        </div>
                    </div>

                    {{-- ✅ CORREGIDO: Ruta correcta --}}
                    <a href="{{ route('estudiante.perfil.show') }}" class="mt-6 block w-full text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                        Editar Perfil
                    </a>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                <h3 class="font-bold text-gray-800 mb-4">Mis Estadísticas</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600">{{ $estadisticas['puntos'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Puntos</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ $estadisticas['nivel_max'] ?? 1 }}</p>
                        <p class="text-xs text-gray-500">Nivel Máx</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ $estadisticas['juegos'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Juegos</p>
                    </div>
                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                        <p class="text-2xl font-bold text-orange-600">{{ $estadisticas['evaluaciones'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Evaluaciones</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Rankings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Mis Rankings</h3>
                @if(isset($rankings) && $rankings->count() > 0)
                    <div class="space-y-4">
                        @foreach($rankings as $ranking)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="text-3xl mr-4">{{ $ranking->asignatura->icono ?? '📚' }}</span>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $ranking->asignatura->nombre ?? 'Asignatura' }}</p>
                                        {{-- ✅ CORREGIDO: usar nivel_alcanzado --}}
                                        <p class="text-sm text-gray-500">Nivel {{ $ranking->nivel_alcanzado }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center justify-end">
                                        <span class="text-2xl font-bold text-purple-600 mr-2">#{{ $ranking->posicion }}</span>
                                    </div>
                                    {{-- ✅ CORREGIDO: usar puntaje_total --}}
                                    <p class="text-sm text-gray-500">{{ $ranking->puntaje_total }} puntos</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Aún no apareces en los rankings</p>
                @endif
            </div>

            <!-- Logros -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Mis Logros</h3>
                    <span class="text-sm text-gray-500">{{ isset($logros) ? $logros->count() : 0 }} desbloqueados</span>
                </div>
                @if(isset($logros) && $logros->count() > 0)
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($logros as $logro)
                            <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200" title="{{ $logro->logro->descripcion ?? '' }}">
                                <div class="text-4xl mb-2">{{ $logro->logro->icono ?? '🏆' }}</div>
                                <p class="text-sm font-medium text-gray-800">{{ $logro->logro->nombre ?? 'Logro' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <p class="text-gray-500">Aún no has desbloqueado logros</p>
                        <p class="text-sm text-gray-400">Completa juegos y evaluaciones para ganar logros</p>
                    </div>
                @endif
            </div>

            <!-- Actividad Reciente -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Actividad Reciente</h3>
                @if(isset($actividades) && $actividades->count() > 0)
                    <div class="space-y-4">
                        @foreach($actividades as $actividad)
                            <div class="flex items-center p-4 border border-gray-100 rounded-lg">
                                @if($actividad->tipo == 'juego')
                                    <div class="p-2 bg-purple-100 rounded-lg mr-4">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="p-2 bg-blue-100 rounded-lg mr-4">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">
                                        {{ $actividad->tipo == 'juego' ? 'Completó el juego' : 'Realizó la evaluación' }}
                                        "{{ $actividad->titulo }}"
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $actividad->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold {{ $actividad->puntuacion >= 60 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $actividad->puntuacion }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No hay actividad reciente</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection