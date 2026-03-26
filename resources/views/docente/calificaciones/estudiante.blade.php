@extends('layouts.app')

@section('title', 'Detalle de Calificaciones')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('docente.calificaciones.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Volver a calificaciones
            </a>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">
                {{ trim($estudiante->nombre . ' ' . ($estudiante->apellido ?? '')) }}
            </h1>
            <p class="text-gray-600 mt-1">{{ $estudiante->email }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm px-5 py-4">
            <p class="text-sm text-gray-500">Promedio general</p>
            <p class="text-2xl font-bold {{ $promedioGeneral >= 3 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($promedioGeneral, 1) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm text-gray-500">Estado general</p>
            <p class="text-xl font-semibold mt-2 {{ $promedioGeneral >= 3 ? 'text-green-600' : 'text-red-600' }}">
                {{ $promedioGeneral >= 3 ? 'Aprobado' : 'Reprobado' }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm text-gray-500">Colegio</p>
            <p class="text-xl font-semibold text-gray-800 mt-2">{{ optional($estudiante->colegio)->nombre ?? 'Sin colegio' }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm text-gray-500">Asignaturas con registro</p>
            <p class="text-xl font-semibold text-gray-800 mt-2">{{ count($calificaciones) }}</p>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($calificaciones as $item)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $item['asignatura']->nombre }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Resumen por periodos del anio academico actual</p>
                    </div>

                    <div class="text-left md:text-right">
                        <p class="text-sm text-gray-500">Promedio anual</p>
                        <p class="text-xl font-semibold {{ $item['aprobo_anual'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($item['promedio_anual'], 1) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juegos (30%)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones (70%)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota Final</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($item['calificaciones_periodo'] as $calificacion)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Periodo {{ $calificacion->periodo }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($calificacion->promedio_juegos, 1) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($calificacion->promedio_evaluaciones, 1) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $calificacion->aprobo ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($calificacion->promedio_ponderado, 1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $calificacion->aprobo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $calificacion->aprobo ? 'Aprobado' : 'Reprobado' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No hay calificaciones registradas para esta asignatura.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500">
                No se encontraron calificaciones para este estudiante.
            </div>
        @endforelse
    </div>
</div>
@endsection
