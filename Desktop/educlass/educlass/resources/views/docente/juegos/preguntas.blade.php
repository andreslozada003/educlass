@extends('layouts.app')

@section('title', 'Preguntas del Juego')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Preguntas del juego</h1>
            <p class="text-gray-500">{{ $juego->titulo }} ({{ $juego->preguntas->count() }} preguntas)</p>
        </div>
        <a href="{{ route('docente.juegos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
            <p class="font-semibold mb-2">Revisa estos errores:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Agregar pregunta</h2>
        </div>

        <form method="POST" action="{{ route('docente.juegos.preguntas.agregar', $juego->id) }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label for="enunciado" class="block text-sm font-medium text-gray-700 mb-2">Enunciado</label>
                <textarea id="enunciado" name="enunciado" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Escribe la pregunta..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select id="tipo" name="tipo" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="opcion_multiple">Opcion multiple</option>
                        <option value="verdadero_falso">Verdadero/Falso</option>
                        <option value="emparejamiento">Emparejamiento</option>
                        <option value="ordenamiento">Ordenamiento</option>
                    </select>
                </div>

                <div>
                    <label for="respuesta_correcta" class="block text-sm font-medium text-gray-700 mb-2">Respuesta correcta</label>
                    <input type="text" id="respuesta_correcta" name="respuesta_correcta" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Ej: 4 o Verdadero">
                </div>

                <div>
                    <label for="puntaje" class="block text-sm font-medium text-gray-700 mb-2">Puntaje</label>
                    <input type="number" id="puntaje" name="puntaje" min="1" value="10" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Opciones (solo si aplica)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="opciones[]" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Opcion 1">
                    <input type="text" name="opciones[]" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Opcion 2">
                    <input type="text" name="opciones[]" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Opcion 3">
                    <input type="text" name="opciones[]" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Opcion 4">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-plus mr-2"></i>Agregar pregunta
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Preguntas creadas</h2>
            <span class="text-sm text-gray-500">{{ $juego->preguntas->count() }} total</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($juego->preguntas->sortBy('orden') as $pregunta)
                <div class="p-6 flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="text-sm text-gray-500">#{{ $pregunta->orden }} | {{ $pregunta->tipo }} | {{ $pregunta->puntaje }} pts</div>
                        <p class="text-gray-800 font-medium">{{ $pregunta->enunciado }}</p>
                        @if(!empty($pregunta->opciones))
                            <div class="text-sm text-gray-600">Opciones: {{ implode(' | ', array_filter($pregunta->opciones)) }}</div>
                        @endif
                        @if(!empty($pregunta->respuesta_correcta))
                            <div class="text-sm text-green-700">Respuesta: {{ implode(', ', $pregunta->respuesta_correcta) }}</div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('docente.juegos.preguntas.eliminar', [$juego->id, $pregunta->id]) }}" onsubmit="return confirm('Eliminar esta pregunta?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">
                    Aun no hay preguntas para este juego.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
