@extends('layouts.app')

@section('title', 'Mi Progreso')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mi Progreso</h1>
        <p class="text-gray-600 mt-2">Visualiza tu avance, tus calificaciones y tu desempeno general.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Asignaturas activas</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $resumen['asignaturas'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Temas completados</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $resumen['temas_completados'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Puntos totales</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $resumen['puntos_totales'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Nivel actual</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $resumen['nivel_maximo'] ?? 1 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-sm text-blue-700">Juegos completados</p>
            <p class="text-2xl font-bold text-blue-900 mt-2">{{ $estadisticas['juegos_completados'] ?? 0 }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5">
            <p class="text-sm text-green-700">Evaluaciones aprobadas</p>
            <p class="text-2xl font-bold text-green-900 mt-2">{{ $estadisticas['evaluaciones_aprobadas'] ?? 0 }}/{{ $estadisticas['total_evaluaciones'] ?? 0 }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-5">
            <p class="text-sm text-amber-700">Tasa de aprobacion</p>
            <p class="text-2xl font-bold text-amber-900 mt-2">{{ number_format($estadisticas['tasa_aprobacion'] ?? 0, 1) }}%</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-5">
            <p class="text-sm text-purple-700">Posicion general</p>
            <p class="text-2xl font-bold text-purple-900 mt-2">
                {{ ($estadisticas['posicion_ranking'] ?? 0) > 0 ? '#' . $estadisticas['posicion_ranking'] : 'Sin ranking' }}
            </p>
        </div>
    </div>

    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Progreso por Asignatura</h2>

        @forelse($progresoPorAsignatura as $item)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                    <div class="flex items-center">
                        <span class="text-4xl mr-4">{{ $item['asignatura']->icono ?? 'A' }}</span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $item['asignatura']->nombre }}</h3>
                            <p class="text-gray-500">Nivel {{ $item['progreso']->nivel_actual ?? 1 }}</p>
                        </div>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-3xl font-bold text-blue-600">{{ $item['progreso']->porcentaje_completado ?? 0 }}%</p>
                        <p class="text-sm text-gray-500">completado</p>
                    </div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-4 rounded-full transition-all duration-500" style="width: {{ min($item['progreso']->porcentaje_completado ?? 0, 100) }}%"></div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-800">{{ $item['progreso']->temas_completados ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Temas completados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $item['progreso']->puntos_acumulados ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Puntos ganados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $item['juegos_completados'] }}</p>
                        <p class="text-sm text-gray-500">Juegos completados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ $item['evaluaciones_aprobadas'] }}/{{ $item['evaluaciones_totales'] }}</p>
                        <p class="text-sm text-gray-500">Evaluaciones aprobadas</p>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        @foreach([1, 2, 3, 4] as $nivel)
                            @php
                                $nivelCompletado = ($item['progreso']->nivel_actual ?? 1) > $nivel;
                                $nivelActual = ($item['progreso']->nivel_actual ?? 1) === $nivel;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $nivelCompletado ? 'bg-green-500 text-white' : ($nivelActual ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                                    @if($nivelCompletado)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <span class="font-bold">{{ $nivel }}</span>
                                    @endif
                                </div>
                                @if($nivel < 4)
                                    <div class="w-10 md:w-20 h-1 {{ ($item['progreso']->nivel_actual ?? 1) > $nivel ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <a href="{{ route('estudiante.asignaturas.show', $item['asignatura']) }}"
                        class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                        Continuar aprendiendo
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-lg p-8 text-center text-gray-500">
                No hay asignaturas activas para mostrar en tu progreso.
            </div>
        @endforelse
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Calificaciones</h2>
                <span class="text-sm text-gray-500">{{ count($calificaciones) }} asignaturas</span>
            </div>

            <div class="space-y-4">
                @forelse($calificaciones as $item)
                    @php
                        $periodosRegistrados = $item['calificaciones_periodo']->count();
                    @endphp
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $item['asignatura']->nombre }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $periodosRegistrados }} periodos registrados</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item['aprobo_anual'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $item['aprobo_anual'] ? 'Aprobado' : 'Pendiente' }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Promedio anual</p>
                                <p class="text-2xl font-bold {{ $item['aprobo_anual'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($item['promedio_anual'], 1) }}
                                </p>
                            </div>
                            <a href="{{ route('estudiante.asignaturas.show', $item['asignatura']) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver asignatura
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-8">Aun no tienes calificaciones registradas.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Rankings</h2>
                <span class="text-sm text-gray-500">{{ $rankings->count() }} registros</span>
            </div>

            <div class="space-y-4">
                @forelse($rankings as $ranking)
                    <div class="border border-gray-100 rounded-xl p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-xl">
                                {{ $ranking->categoria_icono }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $ranking->categoria_nombre }}</p>
                                <p class="text-sm text-gray-500">{{ $ranking->asignatura->nombre ?? 'General' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-purple-600">#{{ $ranking->posicion }}</p>
                            <p class="text-sm text-gray-500">{{ $ranking->puntaje_total }} puntos</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-8">Aun no apareces en los rankings.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Logros Recientes</h2>
            <span class="text-sm text-gray-500">{{ $logrosRecientes->count() }} visibles</span>
        </div>

        @if($logrosRecientes->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($logrosRecientes as $logro)
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4 text-center">
                        <div class="text-4xl mb-2">{{ $logro->logro->icono }}</div>
                        <h4 class="font-medium text-gray-800">{{ $logro->logro->nombre }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ optional($logro->fecha_obtenido ?? $logro->created_at)->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-8 text-center text-gray-500">
                Aun no has obtenido logros recientes.
            </div>
        @endif
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Coleccion de Insignias</h2>
            <span class="text-sm text-gray-500">{{ count($logrosDesbloqueados) }} desbloqueados de {{ $logrosDisponibles->count() }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($logrosDisponibles as $logro)
                @php
                    $desbloqueado = in_array($logro->id, $logrosDesbloqueados);
                @endphp
                <div class="rounded-xl border p-5 transition {{ $desbloqueado ? 'bg-white shadow-lg' : 'bg-gray-50 border-dashed opacity-75' }}"
                    style="border-color: {{ $desbloqueado ? ($logro->color ?? '#E5E7EB') : '#D1D5DB' }};">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl {{ $desbloqueado ? '' : 'grayscale' }}"
                                style="background-color: {{ $desbloqueado ? (($logro->color ?? '#F3F4F6') . '20') : '#F3F4F6' }};">
                                {{ $logro->icono }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $logro->nombre }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $logro->descripcion }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $desbloqueado ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $desbloqueado ? 'Desbloqueado' : 'Bloqueado' }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-500">{{ $logro->criterio_descripcion }}</p>
                        <span class="shrink-0 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                            +{{ $logro->puntos_bonus }} pts
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
