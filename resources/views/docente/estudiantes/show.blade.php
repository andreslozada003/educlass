@extends('layouts.app')

@section('title', $estudiante->nombre . ' ' . $estudiante->apellido)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('docente.estudiantes.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a Estudiantes
        </a>
    </div>

    <!-- Perfil del Estudiante -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center text-3xl font-bold text-blue-600 mb-4 md:mb-0 md:mr-6">
                    @if($estudiante->avatar)
                        <img src="{{ Storage::url($estudiante->avatar) }}" class="w-full h-full rounded-full object-cover">
                    @else
                        {{ substr($estudiante->nombre, 0, 1) }}{{ substr($estudiante->apellido, 0, 1) }}
                    @endif
                </div>
                <div class="text-center md:text-left text-white">
                    <h1 class="text-3xl font-bold">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</h1>
                    <p class="text-blue-100 mt-1">{{ $estudiante->email }}</p>
                    <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-3">
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">{{ $estudiante->colegio->nombre ?? 'Sin colegio' }}</span>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Grado {{ $estudiante->grado }}°</span>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">{{ $estudiante->edad }} años</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-600 font-medium">Puntos Totales</p>
                <p class="text-3xl font-bold text-blue-800">{{ $estadisticas['puntos_totales'] ?? 0 }}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-green-600 font-medium">Juegos Completados</p>
                <p class="text-3xl font-bold text-green-800">{{ $estadisticas['juegos_completados'] ?? 0 }}</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-purple-600 font-medium">Evaluaciones</p>
                <p class="text-3xl font-bold text-purple-800">{{ $estadisticas['evaluaciones_completadas'] ?? 0 }}</p>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <p class="text-sm text-orange-600 font-medium">Logros Obtenidos</p>
                <p class="text-3xl font-bold text-orange-800">{{ $estadisticas['logros'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Mensaje al estudiante -->
        <div class="px-6 pb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Enviar mensaje al estudiante</h3>
                <form method="POST" action="{{ route('docente.estudiantes.mensaje', $estudiante->id) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="info">Informacion</option>
                                <option value="success">Exito</option>
                                <option value="warning">Advertencia</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
                            <textarea name="mensaje" rows="3" required minlength="5" maxlength="1000"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                      placeholder="Ejemplo: Ya se te habilitaron mas intentos en la evaluacion."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            Enviar mensaje
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Progreso por Asignatura -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Progreso por Asignatura</h2>
            </div>
            <div class="p-6">
                @if($progreso->count() > 0)
                    <div class="space-y-4">
                        @foreach($progreso as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-medium text-gray-700">{{ $item->asignatura->nombre }}</span>
                                    <span class="text-sm text-gray-500">Nivel {{ $item->nivel_actual }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full" style="width: {{ min($item->porcentaje_completado, 100) }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>{{ $item->temas_completados }} temas completados</span>
                                    <span>{{ $item->porcentaje_completado }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p>Sin progreso registrado</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Logros -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Logros Obtenidos</h2>
            </div>
            <div class="p-6">
                @if($logros->count() > 0)
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($logros as $logro)
                            <div class="flex items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="text-3xl mr-3">{{ $logro->logro->icono }}</div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">{{ $logro->logro->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $logro->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <p>Sin logros aún</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Historial de Actividades -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Historial de Actividades Recientes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actividad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntuación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($actividades as $actividad)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($actividad->tipo == 'juego')
                                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $actividad->juego->titulo ?? 'Juego' }}</p>
                                            <p class="text-xs text-gray-500">Juego completado</p>
                                        </div>
                                    @else
                                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $actividad->evaluacion->titulo ?? 'Evaluación' }}</p>
                                            <p class="text-xs text-gray-500">Evaluación completada</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $actividad->asignatura->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold {{ ($actividad->puntuacion_obtenida / $actividad->puntuacion_total) >= 0.6 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $actividad->puntuacion_obtenida }}/{{ $actividad->puntuacion_total }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $actividad->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                <p>No hay actividades recientes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Calificaciones por Período -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Calificaciones por Período</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juegos (30%)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones (70%)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota Final</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($calificaciones as $calificacion)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $calificacion->asignatura->nombre }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                                {{ str_replace('_', ' ', $calificacion->periodo) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($calificacion->promedio_juegos, 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($calificacion->promedio_evaluaciones, 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold {{ $calificacion->nota_final >= 60 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($calificacion->nota_final, 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $calificacion->nota_final >= 60 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $calificacion->nota_final >= 60 ? 'Aprobado' : 'Reprobado' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <p>No hay calificaciones registradas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
