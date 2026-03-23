@extends('layouts.app')

@section('title', 'Gestión de Temas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Temas</h1>
            <p class="text-gray-500">Administra el contenido educativo de las asignaturas</p>
        </div>
        <a href="{{ route('docente.temas.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-xl transition-colors">
            <i class="fas fa-plus"></i>
            <span>Nuevo Tema</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('docente.temas.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <select name="asignatura" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todas las asignaturas</option>
                    @foreach($asignaturas as $asignatura)
                        <option value="{{ $asignatura->id }}" {{ request('asignatura') == $asignatura->id ? 'selected' : '' }}>
                            {{ $asignatura->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="periodo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todos los períodos</option>
                    <option value="1" {{ request('periodo') == '1' ? 'selected' : '' }}>Período 1</option>
                    <option value="2" {{ request('periodo') == '2' ? 'selected' : '' }}>Período 2</option>
                    <option value="3" {{ request('periodo') == '3' ? 'selected' : '' }}>Período 3</option>
                </select>
            </div>
            <div>
                <select name="dificultad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todas las dificultades</option>
                    <option value="1" {{ request('dificultad') == '1' ? 'selected' : '' }}>Básico</option>
                    <option value="2" {{ request('dificultad') == '2' ? 'selected' : '' }}>Intermedio</option>
                    <option value="3" {{ request('dificultad') == '3' ? 'selected' : '' }}>Avanzado</option>
                    <option value="4" {{ request('dificultad') == '4' ? 'selected' : '' }}>Experto</option>
                </select>
            </div>
            <div>
                <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todos los estados</option>
                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
            <a href="{{ route('docente.temas.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-undo mr-2"></i>Limpiar
            </a>
        </form>
    </div>

    <!-- Themes List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Orden</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tema</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asignatura</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nivel</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contenido</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($temas as $tema)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg font-semibold text-gray-600">
                                    {{ $tema->orden }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($tema->imagen_destacada)
                                        <img src="{{ asset('storage/' . ltrim($tema->imagen_destacada, '/')) }}" 
                                             alt="{{ $tema->titulo }}" 
                                             class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl"
                                             style="background-color: {{ $tema->asignatura->color_secundario }}20">
                                            {{ $tema->asignatura->icono }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $tema->titulo }}</p>
                                        <p class="text-sm text-gray-500">{{ $tema->tiempo_estimado_minutos }} min</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm"
                                      style="background-color: {{ $tema->asignatura->color_secundario }}20; color: {{ $tema->asignatura->color_primario }}">
                                    {{ $tema->asignatura->icono }}
                                    {{ $tema->asignatura->nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm
                                    {{ $tema->dificultad == 1 ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $tema->dificultad == 2 ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $tema->dificultad == 3 ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $tema->dificultad == 4 ? 'bg-orange-100 text-orange-700' : '' }}">
                                    {!! $tema->nivel_icono !!}
                                    {{ $tema->nivel_nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600">Período {{ $tema->periodo_academico }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    @if($tema->juegos()->count() > 0)
                                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs">
                                            <i class="fas fa-gamepad mr-1"></i>{{ $tema->juegos()->count() }}
                                        </span>
                                    @endif
                                    @if($tema->evaluaciones()->count() > 0)
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">
                                            <i class="fas fa-clipboard-list mr-1"></i>{{ $tema->evaluaciones()->count() }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $tema->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $tema->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('docente.temas.show', $tema->id) }}" 
                                       class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                       title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('docente.temas.edit', $tema->id) }}" 
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('docente.temas.destroy', $tema->id) }}" 
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este tema?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-book-open text-4xl mb-4"></i>
                                    <p class="text-lg">No hay temas creados</p>
                                    <a href="{{ route('docente.temas.create') }}" class="text-primary-500 hover:text-primary-600 mt-2 inline-block">
                                        Crear primer tema
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($temas->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $temas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
