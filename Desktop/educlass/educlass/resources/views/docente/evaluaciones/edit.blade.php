@extends('layouts.app')

@section('title', 'Editar Evaluación')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('docente.evaluaciones.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a Evaluaciones
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Editar Evaluación</h1>
            <p class="text-purple-200 mt-1">Modifica los datos de la evaluación</p>
        </div>

        <form action="{{ route('docente.evaluaciones.update', $evaluacion) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tema -->
                <div class="md:col-span-2">
                    <label for="tema_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tema <span class="text-red-500">*</span>
                    </label>
                    <select name="tema_id" id="tema_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('tema_id') border-red-500 @enderror">
                        <option value="">Selecciona un tema</option>
                        @foreach($temas as $tema)
                            <option value="{{ $tema->id }}" {{ old('tema_id', $evaluacion->tema_id) == $tema->id ? 'selected' : '' }}>
                                {{ $tema->asignatura->nombre ?? 'Asignatura' }} - {{ $tema->titulo }}
                            </option>
                        @endforeach
                    </select>
                    @error('tema_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Título -->
                <div class="md:col-span-2">
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                        Título de la Evaluación <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="titulo" id="titulo" 
                        value="{{ old('titulo', $evaluacion->titulo) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('titulo') border-red-500 @enderror"
                        placeholder="Ej: Evaluación de Números Enteros">
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('descripcion') border-red-500 @enderror"
                        placeholder="Instrucciones o descripción de la evaluación">{{ old('descripcion', $evaluacion->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Evaluación -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Evaluación <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo" id="tipo" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('tipo') border-red-500 @enderror">
                        <option value="diagnostica" {{ old('tipo', $evaluacion->tipo) == 'diagnostica' ? 'selected' : '' }}>Diagnóstica</option>
                        <option value="formativa" {{ old('tipo', $evaluacion->tipo) == 'formativa' ? 'selected' : '' }}>Formativa</option>
                        <option value="sumativa" {{ old('tipo', $evaluacion->tipo) == 'sumativa' ? 'selected' : '' }}>Sumativa</option>
                    </select>
                    @error('tipo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tiempo Límite -->
                <div>
                    <label for="tiempo_limite_minutos" class="block text-sm font-medium text-gray-700 mb-2">
                        Tiempo Límite (minutos) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tiempo_limite_minutos" id="tiempo_limite_minutos" 
                        value="{{ old('tiempo_limite_minutos', $evaluacion->tiempo_limite_minutos) }}" required min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('tiempo_limite_minutos') border-red-500 @enderror">
                    @error('tiempo_limite_minutos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Intentos Permitidos -->
                <div>
                    <label for="intentos_permitidos" class="block text-sm font-medium text-gray-700 mb-2">
                        Intentos Permitidos <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="intentos_permitidos" id="intentos_permitidos" 
                        value="{{ old('intentos_permitidos', $evaluacion->intentos_permitidos) }}" required min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('intentos_permitidos') border-red-500 @enderror">
                    @error('intentos_permitidos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Umbral de Aprobación -->
                <div>
                    <label for="umbral_aprobacion" class="block text-sm font-medium text-gray-700 mb-2">
                        Nota Mínima para Aprobar (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="umbral_aprobacion" id="umbral_aprobacion" 
                        value="{{ old('umbral_aprobacion', $evaluacion->umbral_aprobacion) }}" required min="0" max="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('umbral_aprobacion') border-red-500 @enderror">
                    @error('umbral_aprobacion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="activa" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado
                    </label>
                    <select name="activa" id="activa"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="1" {{ old('activa', $evaluacion->activa) ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ !old('activa', $evaluacion->activa) ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('docente.evaluaciones.index') }}" 
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Actualizar Evaluación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
