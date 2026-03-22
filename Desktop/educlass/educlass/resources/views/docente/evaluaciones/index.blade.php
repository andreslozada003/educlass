@extends('layouts.app')

@section('title', 'Gestión de Evaluaciones')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Evaluaciones</h1>
            <p class="text-gray-500">Crea y administra evaluaciones para los temas</p>
        </div>
        <a href="{{ route('docente.evaluaciones.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl transition-colors">
            <i class="fas fa-plus"></i>
            <span>Nueva Evaluación</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Evaluación</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tema</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Preguntas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tiempo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($evaluaciones as $evaluacion)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $evaluacion->titulo }}</p>
                                <p class="text-sm text-gray-500">Umbral: {{ $evaluacion->umbral_aprobacion }}%</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ $evaluacion->tema->titulo }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium" style="background-color: {{ $evaluacion->tipo_color }}20; color: {{ $evaluacion->tipo_color }}">
                                    {{ $evaluacion->tipo_nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600">{{ $evaluacion->preguntas->count() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600">{{ $evaluacion->tiempo_limite_minutos }} min</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $evaluacion->activa ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $evaluacion->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('docente.evaluaciones.show', $evaluacion->id) }}" class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('docente.evaluaciones.edit', $evaluacion->id) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('docente.evaluaciones.destroy', $evaluacion->id) }}" class="inline" onsubmit="return confirm('¿Eliminar esta evaluación?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                <p>No hay evaluaciones creadas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($evaluaciones->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $evaluaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
