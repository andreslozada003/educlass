@extends('layouts.app')

@section('title', 'Resultado del Juego')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="text-6xl mb-3">??</div>
                <h1 class="text-3xl font-bold text-gray-800">Resultado del juego</h1>
                <p class="text-gray-600 mt-2">{{ $juego->titulo }}</p>
                <p class="text-sm text-gray-500">{{ $juego->tema->asignatura->nombre }} - {{ $juego->tema->titulo }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-purple-700">Puntaje obtenido</p>
                    <p class="text-2xl font-bold text-purple-800">{{ $intento->puntaje_obtenido }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-blue-700">Mejor puntaje</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $mejorPuntaje ?? 0 }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-orange-700">Duración</p>
                    <p class="text-2xl font-bold text-orange-800">{{ $intento->duracion_formateada ?? '00:00' }}</p>
                </div>
            </div>

            @if($esMejorPuntaje)
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 text-center font-medium">
                    ¡Nuevo mejor puntaje!
                </div>
            @endif

            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('estudiante.juegos.jugar', $juego->id) }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-medium">
                    Jugar de nuevo
                </a>
                <a href="{{ route('estudiante.temas.show', $juego->tema->slug) }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition font-medium">
                    Volver al tema
                </a>
                <a href="{{ route('estudiante.juegos.historial') }}" class="px-6 py-3 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition font-medium">
                    Ver historial
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
