@extends('layouts.app')

@section('title', 'Gestión de Juegos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Juegos</h1>
            <p class="text-gray-500">Crea y administra juegos educativos interactivos</p>
        </div>
        <a href="{{ route('docente.juegos.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-xl transition-colors">
            <i class="fas fa-plus"></i>
            <span>Nuevo Juego</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('docente.juegos.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <select name="asignatura" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todas las asignaturas</option>
                    @foreach(\App\Models\Asignatura::activas()->get() as $asignatura)
                        <option value="{{ $asignatura->id }}" {{ request('asignatura') == $asignatura->id ? 'selected' : '' }}>
                            {{ $asignatura->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $key => $tipo)
                        <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>
                            {{ $tipo['icono'] }} {{ $tipo['nombre'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
            <a href="{{ route('docente.juegos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-undo mr-2"></i>Limpiar
            </a>
        </form>
    </div>

    <!-- Games Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($juegos as $juego)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                             style="background-color: {{ $juego->tipo_color }}20">
                            {{ $juego->tipo_icono }}
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ $juego->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $juego->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    
                    <h3 class="font-semibold text-gray-800 mb-1">{{ $juego->titulo }}</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ $juego->tipo_nombre }}</p>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                        <span class="px-2 py-1 bg-gray-100 rounded">{{ $juego->tema->asignatura->nombre }}</span>
                        <span class="px-2 py-1 bg-gray-100 rounded">{{ $juego->preguntas->count() }} preguntas</span>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-redo mr-1"></i>{{ $juego->intentos_maximos }} intentos
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('docente.juegos.preview', $juego->id) }}" 
                               class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                               title="Vista previa">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('docente.juegos.edit', $juego->id) }}" 
                               class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('docente.juegos.destroy', $juego->id) }}" 
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar este juego?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="text-gray-400">
                        <i class="fas fa-gamepad text-5xl mb-4"></i>
                        <p class="text-lg">No hay juegos creados</p>
                        <a href="{{ route('docente.juegos.create') }}" class="text-purple-500 hover:text-purple-600 mt-2 inline-block">
                            Crear primer juego
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($juegos->hasPages())
        <div class="flex justify-center">
            {{ $juegos->links() }}
        </div>
    @endif
</div>
@endsection
