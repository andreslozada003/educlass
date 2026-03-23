@extends('layouts.app')

@section('title', 'Crear Juego')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('docente.juegos.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Juego</h1>
            <p class="text-gray-500">Diseña un juego educativo interactivo</p>
        </div>
    </div>

    <form method="POST" action="{{ route('docente.juegos.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        
        <div class="p-6 space-y-6">
            <!-- Tema -->
            <div>
                <label for="tema_id" class="block text-sm font-medium text-gray-700 mb-2">Tema <span class="text-red-500">*</span></label>
                <select name="tema_id" id="tema_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Selecciona un tema</option>
                    @foreach($temas as $tema)
                        <option value="{{ $tema->id }}" {{ $temaPreseleccionado == $tema->id ? 'selected' : '' }}>
                            {{ $tema->asignatura->icono }} {{ $tema->titulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo de juego -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Juego <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($tipos as $key => $tipo)
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo" value="{{ $key }}" {{ old('tipo') == $key ? 'checked' : '' }} required class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all">
                                <div class="text-3xl mb-2">{{ $tipo['icono'] }}</div>
                                <p class="text-sm font-medium">{{ $tipo['nombre'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Título -->
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título <span class="text-red-500">*</span></label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="Ej: Quiz de Sumas">
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                          placeholder="Describe el objetivo del juego...">{{ old('descripcion') }}</textarea>
            </div>

            <!-- Configuración -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="dificultad" class="block text-sm font-medium text-gray-700 mb-2">Dificultad</label>
                    <select name="dificultad" id="dificultad" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="1">⭐ Básico</option>
                        <option value="2">⭐⭐ Intermedio</option>
                        <option value="3">⭐⭐⭐ Avanzado</option>
                        <option value="4">⭐⭐⭐⭐ Experto</option>
                    </select>
                </div>
                <div>
                    <label for="intentos_maximos" class="block text-sm font-medium text-gray-700 mb-2">Intentos Máximos</label>
                    <input type="number" name="intentos_maximos" id="intentos_maximos" value="{{ old('intentos_maximos', 5) }}" required min="1" max="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="puntaje_base" class="block text-sm font-medium text-gray-700 mb-2">Puntaje Base</label>
                    <input type="number" name="puntaje_base" id="puntaje_base" value="{{ old('puntaje_base', 100) }}" required min="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <!-- Tiempo límite -->
            <div>
                <label for="tiempo_limite_segundos" class="block text-sm font-medium text-gray-700 mb-2">Tiempo Límite (segundos, opcional)</label>
                <input type="number" name="tiempo_limite_segundos" id="tiempo_limite_segundos" value="{{ old('tiempo_limite_segundos') }}" min="30"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="Ej: 300 (5 minutos)">
                <p class="mt-1 text-sm text-gray-500">Deja en blanco para sin límite de tiempo.</p>
            </div>

            <!-- Activo -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo') ? 'checked' : '' }}
                       class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="activo" class="text-sm font-medium text-gray-700">Juego activo</label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('docente.juegos.index') }}" class="px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition-colors">Cancelar</a>
            <button type="submit" class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-xl transition-colors">
                <i class="fas fa-save mr-2"></i>Crear Juego
            </button>
        </div>
    </form>
</div>
@endsection
