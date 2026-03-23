@extends('layouts.app')

@section('title', $evaluacion->titulo)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('docente.evaluaciones.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('docente.evaluaciones.edit', $evaluacion->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i class="fas fa-pen mr-2"></i>Editar
            </a>
            <a href="{{ route('docente.evaluaciones.preguntas', $evaluacion->id) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <i class="fas fa-list-check mr-2"></i>Gestionar preguntas
            </a>
            <a href="{{ route('docente.evaluaciones.resultados', $evaluacion->id) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                <i class="fas fa-chart-line mr-2"></i>Resultados
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h1 class="text-2xl font-bold text-gray-800">{{ $evaluacion->titulo }}</h1>
            <p class="text-gray-500 mt-1">{{ $evaluacion->tema->asignatura->nombre }} | {{ $evaluacion->tema->titulo }}</p>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-lg bg-purple-50 p-4">
                <p class="text-xs text-purple-700">Tipo</p>
                <p class="font-semibold text-purple-900 capitalize">{{ $evaluacion->tipo }}</p>
            </div>
            <div class="rounded-lg bg-blue-50 p-4">
                <p class="text-xs text-blue-700">Tiempo limite</p>
                <p class="font-semibold text-blue-900">{{ $evaluacion->tiempo_limite_minutos }} min</p>
            </div>
            <div class="rounded-lg bg-green-50 p-4">
                <p class="text-xs text-green-700">Intentos</p>
                <p class="font-semibold text-green-900">{{ $evaluacion->intentos_permitidos }}</p>
            </div>
            <div class="rounded-lg bg-orange-50 p-4">
                <p class="text-xs text-orange-700">Umbral</p>
                <p class="font-semibold text-orange-900">{{ $evaluacion->umbral_aprobacion }}%</p>
            </div>
        </div>

        @if($evaluacion->descripcion)
            <div class="px-6 pb-6">
                <div class="rounded-lg bg-gray-50 p-4 text-gray-700">{{ $evaluacion->descripcion }}</div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total resultados</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalResultados }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Aprobados</p>
            <p class="text-2xl font-bold text-green-700">{{ $aprobados }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Promedio puntaje</p>
            <p class="text-2xl font-bold text-blue-700">{{ $promedioPuntaje ? number_format($promedioPuntaje, 1) : '0.0' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Preguntas</h2>
            <span class="text-sm text-gray-500">{{ $evaluacion->preguntas->count() }} total</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($evaluacion->preguntas->sortBy('orden') as $pregunta)
                <div class="p-5">
                    <div class="text-sm text-gray-500">#{{ $pregunta->orden }} | {{ $pregunta->tipo_nombre ?? $pregunta->tipo }} | {{ $pregunta->puntaje }} pts</div>
                    <p class="text-gray-800 font-medium mt-1">{{ $pregunta->enunciado }}</p>

                    @if(!empty($pregunta->opciones))
                        <p class="text-sm text-gray-600 mt-2">Opciones: {{ implode(' | ', array_filter($pregunta->opciones)) }}</p>
                    @endif

                    @if(!empty($pregunta->respuesta_correcta))
                        <p class="text-sm text-green-700 mt-1">Respuesta: {{ is_array($pregunta->respuesta_correcta) ? implode(', ', $pregunta->respuesta_correcta) : $pregunta->respuesta_correcta }}</p>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">
                    No hay preguntas todavia.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
