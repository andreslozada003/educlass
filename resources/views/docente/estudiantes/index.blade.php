@extends('layouts.app')

@section('title', 'Gestión de Estudiantes')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Estudiantes</h1>
        <p class="text-gray-600 mt-2">Visualiza el progreso y rendimiento de tus estudiantes</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('docente.estudiantes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                       placeholder="Nombre, email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asignatura</label>
                <select name="asignatura_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Todas las asignaturas</option>
                    @foreach($asignaturas ?? [] as $asignatura)
                        <option value="{{ $asignatura->id }}">
                            {{ $asignatura->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Filtrar
                </button>
            </div>

        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Lista de Estudiantes</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puntos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                @forelse($estudiantes as $estudiante)

                    @php
                        $progresoItems = $estudiante->progresoEstudiante ?? collect();
                        $totalTemas = $progresoItems->count();
                        $temasCompletados = $progresoItems->where('estado', 'completado')->count();
                        $progresoAvg = $totalTemas > 0 ? ($temasCompletados / $totalTemas) * 100 : 0;
                        $nivelMax = $progresoAvg >= 75 ? 4 : ($progresoAvg >= 50 ? 3 : ($progresoAvg >= 25 ? 2 : 1));
                        $puntosTotal = optional($estudiante->rankings)->sum('puntaje_total') ?? 0;
                    @endphp

                    <tr class="hover:bg-gray-50">

                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $estudiante->nombre }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $estudiante->email }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">
                                Nivel {{ $nivelMax }}
                            </span>
                        </td>

                        <td class="px-6 py-4 font-bold text-gray-800">
                            {{ $puntosTotal }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="w-24 bg-gray-200 rounded-full h-2 mb-1">
                                <div class="bg-blue-600 h-2 rounded-full"
                                     style="width: {{ min($progresoAvg,100) }}%">
                                </div>
                            </div>
                            <span class="text-xs text-gray-500">
                                {{ number_format($progresoAvg,1) }}%
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <a href="{{ route('docente.estudiantes.show', $estudiante->id) }}"
                               class="text-blue-600 hover:text-blue-900">
                                Ver detalle
                            </a>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            No se encontraron estudiantes
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        @if($estudiantes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $estudiantes->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
