@extends('layouts.app')

@section('title', 'Editar Juego')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('docente.juegos.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar Juego</h1>
            <p class="text-gray-500">{{ $juego->titulo }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('docente.juegos.update', $juego->id) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tema</label>
                <input type="text" disabled value="{{ $juego->tema->titulo }}"
                       class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Juego</label>
                <input type="text" disabled value="{{ $juego->tipo_nombre }}"
                       class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-500">
            </div>

            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título <span class="text-red-500">*</span></label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $juego->titulo) }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('descripcion', $juego->descripcion) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="dificultad" class="block text-sm font-medium text-gray-700 mb-2">Dificultad</label>
                    <select name="dificultad" id="dificultad" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="1" {{ old('dificultad', $juego->dificultad) == 1 ? 'selected' : '' }}>⭐ Básico</option>
                        <option value="2" {{ old('dificultad', $juego->dificultad) == 2 ? 'selected' : '' }}>⭐⭐ Intermedio</option>
                        <option value="3" {{ old('dificultad', $juego->dificultad) == 3 ? 'selected' : '' }}>⭐⭐⭐ Avanzado</option>
                        <option value="4" {{ old('dificultad', $juego->dificultad) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ Experto</option>
                    </select>
                </div>
                <div>
                    <label for="intentos_maximos" class="block text-sm font-medium text-gray-700 mb-2">Intentos Máximos</label>
                    <input type="number" name="intentos_maximos" id="intentos_maximos" value="{{ old('intentos_maximos', $juego->intentos_maximos) }}" required min="1" max="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="puntaje_base" class="block text-sm font-medium text-gray-700 mb-2">Puntaje Base</label>
                    <input type="number" name="puntaje_base" id="puntaje_base" value="{{ old('puntaje_base', $juego->puntaje_base) }}" required min="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <div>
                <label for="tiempo_limite_segundos" class="block text-sm font-medium text-gray-700 mb-2">Tiempo Límite (segundos)</label>
                <input type="number" name="tiempo_limite_segundos" id="tiempo_limite_segundos" value="{{ old('tiempo_limite_segundos', $juego->tiempo_limite_segundos) }}" min="30"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $juego->activo) ? 'checked' : '' }}
                       class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="activo" class="text-sm font-medium text-gray-700">Juego activo</label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <form method="POST" action="{{ route('docente.juegos.destroy', $juego->id) }}" 
                  onsubmit="return confirm('¿Eliminar este juego?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fas fa-trash mr-2"></i>Eliminar
                </button>
            </form>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('docente.juegos.index') }}" class="px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition-colors">Cancelar</a>
                <button type="submit" class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
