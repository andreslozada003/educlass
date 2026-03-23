@extends('layouts.app')

@section('title', 'Crear Tema')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('docente.temas.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Tema</h1>
            <p class="text-gray-500">Agrega contenido educativo a una asignatura</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('docente.temas.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        
        <div class="p-6 space-y-6">
            <!-- Asignatura -->
            <div>
                <label for="asignatura_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Asignatura <span class="text-red-500">*</span>
                </label>
                <select name="asignatura_id" id="asignatura_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('asignatura_id') border-red-500 @enderror">
                    <option value="">Selecciona una asignatura</option>
                    @foreach($asignaturas as $asignatura)
                        <option value="{{ $asignatura->id }}" {{ old('asignatura_id') == $asignatura->id ? 'selected' : '' }}>
                            {{ $asignatura->icono }} {{ $asignatura->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('asignatura_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Título -->
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                    Título del Tema <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('titulo') border-red-500 @enderror"
                       placeholder="Ej: Sumas y Restas Básicas">
                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contenido -->
            <div>
                <label for="contenido" class="block text-sm font-medium text-gray-700 mb-2">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea name="contenido" id="contenido" rows="10" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('contenido') border-red-500 @enderror"
                          placeholder="Escribe el contenido del tema aquí... Puedes usar HTML.">{{ old('contenido') }}</textarea>
                @error('contenido')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Puedes usar etiquetas HTML para dar formato al contenido.</p>
            </div>

            <!-- Grid de configuración -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Dificultad -->
                <div>
                    <label for="dificultad" class="block text-sm font-medium text-gray-700 mb-2">
                        Nivel de Dificultad <span class="text-red-500">*</span>
                    </label>
                    <select name="dificultad" id="dificultad" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="1" {{ old('dificultad') == '1' ? 'selected' : '' }}>⭐ Básico</option>
                        <option value="2" {{ old('dificultad') == '2' ? 'selected' : '' }}>⭐⭐ Intermedio</option>
                        <option value="3" {{ old('dificultad') == '3' ? 'selected' : '' }}>⭐⭐⭐ Avanzado</option>
                        <option value="4" {{ old('dificultad') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ Experto</option>
                    </select>
                </div>

                <!-- Período -->
                <div>
                    <label for="periodo_academico" class="block text-sm font-medium text-gray-700 mb-2">
                        Período Académico <span class="text-red-500">*</span>
                    </label>
                    <select name="periodo_academico" id="periodo_academico" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="1" {{ old('periodo_academico') == '1' ? 'selected' : '' }}>Período 1</option>
                        <option value="2" {{ old('periodo_academico') == '2' ? 'selected' : '' }}>Período 2</option>
                        <option value="3" {{ old('periodo_academico') == '3' ? 'selected' : '' }}>Período 3</option>
                    </select>
                </div>

                <!-- Tiempo estimado -->
                <div>
                    <label for="tiempo_estimado_minutos" class="block text-sm font-medium text-gray-700 mb-2">
                        Tiempo Estimado (min) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tiempo_estimado_minutos" id="tiempo_estimado_minutos" 
                           value="{{ old('tiempo_estimado_minutos', 15) }}" required min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Imagen destacada -->
            <div>
                <label for="imagen_destacada" class="block text-sm font-medium text-gray-700 mb-2">
                    Imagen Destacada (opcional)
                </label>
                <input type="file" name="imagen_destacada" id="imagen_destacada" accept="image/*"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('imagen_destacada') border-red-500 @enderror">
                @error('imagen_destacada')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formatos: JPG, PNG. Máximo 2MB.</p>
            </div>

            <!-- Video URL -->
            <div>
                <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                    URL de Video (opcional)
                </label>
                <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('video_url') border-red-500 @enderror"
                       placeholder="https://youtube.com/watch?v=...">
                @error('video_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="activo" class="text-sm font-medium text-gray-700">
                    Tema activo (visible para estudiantes)
                </label>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('docente.temas.index') }}" class="px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Tema
            </button>
        </div>
    </form>
</div>
@endsection
