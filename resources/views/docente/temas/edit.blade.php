@extends('layouts.app')

@section('title', 'Editar Tema')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('docente.temas.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar Tema</h1>
            <p class="text-gray-500">{{ $tema->titulo }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('docente.temas.update', $tema->id) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asignatura</label>
                <input type="text" disabled value="{{ $tema->asignatura->nombre }}"
                       class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-500">
                <input type="hidden" name="asignatura_id" value="{{ $tema->asignatura_id }}">
            </div>

            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                    Titulo del Tema <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $tema->titulo) }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('titulo') border-red-500 @enderror">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contenido" class="block text-sm font-medium text-gray-700 mb-2">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea name="contenido" id="contenido" rows="10" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('contenido') border-red-500 @enderror">{{ old('contenido', $tema->contenido) }}</textarea>
                @error('contenido')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="dificultad" class="block text-sm font-medium text-gray-700 mb-2">Nivel de Dificultad</label>
                    <select name="dificultad" id="dificultad" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="1" {{ old('dificultad', $tema->dificultad) == '1' ? 'selected' : '' }}>Basico</option>
                        <option value="2" {{ old('dificultad', $tema->dificultad) == '2' ? 'selected' : '' }}>Intermedio</option>
                        <option value="3" {{ old('dificultad', $tema->dificultad) == '3' ? 'selected' : '' }}>Avanzado</option>
                        <option value="4" {{ old('dificultad', $tema->dificultad) == '4' ? 'selected' : '' }}>Experto</option>
                    </select>
                </div>

                <div>
                    <label for="periodo_academico" class="block text-sm font-medium text-gray-700 mb-2">Periodo Academico</label>
                    <select name="periodo_academico" id="periodo_academico" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="1" {{ old('periodo_academico', $tema->periodo_academico) == '1' ? 'selected' : '' }}>Periodo 1</option>
                        <option value="2" {{ old('periodo_academico', $tema->periodo_academico) == '2' ? 'selected' : '' }}>Periodo 2</option>
                        <option value="3" {{ old('periodo_academico', $tema->periodo_academico) == '3' ? 'selected' : '' }}>Periodo 3</option>
                    </select>
                </div>

                <div>
                    <label for="tiempo_estimado_minutos" class="block text-sm font-medium text-gray-700 mb-2">Tiempo Estimado (min)</label>
                    <input type="number" name="tiempo_estimado_minutos" id="tiempo_estimado_minutos"
                           value="{{ old('tiempo_estimado_minutos', $tema->tiempo_estimado_minutos) }}" required min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen Destacada</label>
                @if($tema->imagen_destacada)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . ltrim($tema->imagen_destacada, '/')) }}" alt="{{ $tema->titulo }}" class="w-32 h-32 object-cover rounded-lg">
                    </div>
                @endif
                <input type="file" name="imagen_destacada" id="imagen_destacada" accept="image/*"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="mt-1 text-sm text-gray-500">Deja en blanco para mantener la imagen actual.</p>
            </div>

            <div>
                <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">URL de Video</label>
                <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $tema->video_url) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="https://youtube.com/watch?v=...">
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $tema->activo) ? 'checked' : '' }}
                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="activo" class="text-sm font-medium text-gray-700">
                    Tema activo (visible para estudiantes)
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <button type="button"
                    onclick="if (confirm('¿Estas seguro de eliminar este tema? Esta accion no se puede deshacer.')) document.getElementById('delete-tema-form').submit();"
                    class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                <i class="fas fa-trash mr-2"></i>Eliminar
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('docente.temas.index') }}" class="px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

<form id="delete-tema-form" method="POST" action="{{ route('docente.temas.destroy', $tema->id) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
