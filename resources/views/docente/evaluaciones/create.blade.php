@extends('layouts.app')

@section('title', 'Crear Evaluación')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('docente.evaluaciones.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Crear Nueva Evaluación</h1>
            <p class="text-gray-500">Diseña una evaluación para evaluar el aprendizaje</p>
        </div>
    </div>

    <form method="POST" action="{{ route('docente.evaluaciones.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        
        <div class="p-6 space-y-6">
            <div>
                <label for="tema_id" class="block text-sm font-medium text-gray-700 mb-2">Tema <span class="text-red-500">*</span></label>
                <select name="tema_id" id="tema_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Selecciona un tema</option>
                    @foreach($temas as $tema)
                        <option value="{{ $tema->id }}" {{ $temaPreseleccionado == $tema->id ? 'selected' : '' }}>
                            {{ $tema->asignatura->icono }} {{ $tema->titulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título <span class="text-red-500">*</span></label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                       placeholder="Ej: Evaluación de Sumas y Restas">
            </div>

            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                          placeholder="Describe el objetivo de la evaluación...">{{ old('descripcion') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Evaluación <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($tipos as $key => $nombre)
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo" value="{{ $key }}" {{ old('tipo', 'formativa') == $key ? 'checked' : '' }} required class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center hover:border-orange-300 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition-all">
                                <p class="text-sm font-medium">{{ $nombre }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="tiempo_limite_minutos" class="block text-sm font-medium text-gray-700 mb-2">Tiempo Límite (min) <span class="text-red-500">*</span></label>
                    <input type="number" name="tiempo_limite_minutos" id="tiempo_limite_minutos" value="{{ old('tiempo_limite_minutos', 30) }}" required min="5" max="180"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div>
                    <label for="intentos_permitidos" class="block text-sm font-medium text-gray-700 mb-2">Intentos Permitidos <span class="text-red-500">*</span></label>
                    <input type="number" name="intentos_permitidos" id="intentos_permitidos" value="{{ old('intentos_permitidos', 3) }}" required min="1" max="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div>
                    <label for="umbral_aprobacion" class="block text-sm font-medium text-gray-700 mb-2">Umbral de Aprobación (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="umbral_aprobacion" id="umbral_aprobacion" value="{{ old('umbral_aprobacion', 60) }}" required min="50" max="100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="activa" id="activa" value="1" {{ old('activa') ? 'checked' : '' }}
                       class="h-5 w-5 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                <label for="activa" class="text-sm font-medium text-gray-700">Evaluación activa</label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('docente.evaluaciones.index') }}" class="px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition-colors">Cancelar</a>
            <button type="submit" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-xl transition-colors">
                <i class="fas fa-save mr-2"></i>Crear Evaluación
            </button>
        </div>
    </form>
</div>
@endsection
