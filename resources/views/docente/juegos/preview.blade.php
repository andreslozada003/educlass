@extends('layouts.app')

@section('title', 'Vista previa del juego')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Vista previa del juego</h1>
            <p class="text-gray-500">{{ $juego->titulo }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('docente.juegos.preguntas', $juego->id) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <i class="fas fa-list-check mr-2"></i>Preguntas
            </a>
            <a href="{{ route('docente.juegos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-lg bg-purple-50 p-4">
                <p class="text-xs text-purple-700">Tipo</p>
                <p class="font-semibold text-purple-900">{{ $juego->tipo_nombre }}</p>
            </div>
            <div class="rounded-lg bg-blue-50 p-4">
                <p class="text-xs text-blue-700">Dificultad</p>
                <p class="font-semibold text-blue-900">{{ $juego->dificultad }}/4</p>
            </div>
            <div class="rounded-lg bg-green-50 p-4">
                <p class="text-xs text-green-700">Intentos maximos</p>
                <p class="font-semibold text-green-900">{{ $juego->intentos_maximos }}</p>
            </div>
            <div class="rounded-lg bg-orange-50 p-4">
                <p class="text-xs text-orange-700">Tiempo limite</p>
                <p class="font-semibold text-orange-900">{{ $juego->tiempo_limite_formateado }}</p>
            </div>
        </div>

        <div class="px-6 pb-6">
            <div class="text-sm text-gray-600">Tema: <span class="font-medium text-gray-800">{{ $juego->tema->titulo }}</span></div>
            <div class="text-sm text-gray-600">Asignatura: <span class="font-medium text-gray-800">{{ $juego->tema->asignatura->nombre }}</span></div>
            <div class="text-sm text-gray-600">Puntaje base: <span class="font-medium text-gray-800">{{ $juego->puntaje_base }}</span></div>
            <div class="text-sm text-gray-600">Estado: <span class="font-medium {{ $juego->activo ? 'text-green-700' : 'text-red-700' }}">{{ $juego->activo ? 'Activo' : 'Inactivo' }}</span></div>
            @if($juego->descripcion)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg text-gray-700">{{ $juego->descripcion }}</div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Preguntas activas</h2>
            <span class="text-sm text-gray-500">{{ $juego->preguntasActivas->count() }} total</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($juego->preguntasActivas as $pregunta)
                <div class="p-5">
                    <div class="text-sm text-gray-500">#{{ $pregunta->orden }} | {{ $pregunta->tipo }} | {{ $pregunta->puntaje }} pts</div>
                    <p class="text-gray-800 font-medium mt-1">{{ $pregunta->enunciado }}</p>

                    @if(!empty($pregunta->opciones))
                        <p class="text-sm text-gray-600 mt-2">Opciones: {{ implode(' | ', array_filter($pregunta->opciones)) }}</p>
                    @endif

                    @if(!empty($pregunta->respuesta_correcta))
                        <p class="text-sm text-green-700 mt-1">Respuesta: {{ implode(', ', $pregunta->respuesta_correcta) }}</p>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">Aun no hay preguntas activas para este juego.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
