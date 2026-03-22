@extends('layouts.app')

@section('title', 'Resultados de Evaluacion')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Resultados de evaluacion</h1>
            <p class="text-gray-500">{{ $evaluacion->titulo }} | {{ $evaluacion->tema->asignatura->nombre }}</p>
        </div>
        <a href="{{ route('docente.evaluaciones.show', $evaluacion->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total intentos</p>
            <p class="text-2xl font-bold text-gray-800">{{ $resultados->total() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Aprobados</p>
            <p class="text-2xl font-bold text-green-700">{{ $resultados->where('aprobado', true)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Reprobados</p>
            <p class="text-2xl font-bold text-red-700">{{ $resultados->where('aprobado', false)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Puntaje maximo</p>
            <p class="text-2xl font-bold text-blue-700">{{ $evaluacion->puntaje_total }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Lista de resultados</h2>
        </div>

        @if($resultados->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($resultados as $resultado)
                            @php
                                $porcentaje = $evaluacion->puntaje_total > 0
                                    ? round(($resultado->puntaje_obtenido / $evaluacion->puntaje_total) * 100, 2)
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $resultado->estudiante->nombre ?? 'Estudiante' }} {{ $resultado->estudiante->apellido ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $resultado->puntaje_obtenido }} / {{ $evaluacion->puntaje_total }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $porcentaje }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $resultado->tiempo_empleado_minutos ?? 0 }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $resultado->aprobado ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $resultado->aprobado ? 'Aprobado' : 'No aprobado' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ optional($resultado->fecha_realizacion)->format('d/m/Y H:i') ?? $resultado->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form method="POST"
                                          action="{{ route('docente.evaluaciones.resultados.reiniciar-intentos', [$evaluacion->id, $resultado->estudiante_id]) }}"
                                          onsubmit="return confirm('Se eliminaran todos los intentos de este estudiante en esta evaluacion. Continuar?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                                            Reiniciar intentos
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                {{ $resultados->links() }}
            </div>
        @else
            <div class="p-10 text-center text-gray-500">
                Aun no hay resultados para esta evaluacion.
            </div>
        @endif
    </div>
</div>
@endsection
