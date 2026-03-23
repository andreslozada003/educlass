@extends('layouts.app')

@section('title', $asignatura->nombre)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('estudiante.asignaturas.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a Asignaturas
        </a>
    </div>

    <!-- Header de Asignatura -->
    <div class="bg-gradient-to-r {{ $asignatura->color ?? 'from-blue-500 to-purple-600' }} rounded-xl p-8 mb-8 text-white">
        <div class="flex items-center mb-4">
            <span class="text-6xl mr-4">{{ $asignatura->icono ?? '📚' }}</span>
            <div>
                <h1 class="text-3xl font-bold">{{ $asignatura->nombre }}</h1>
                <p class="text-white/80 mt-1">Nivel {{ $progreso->nivel_actual ?? 1 }}</p>
            </div>
        </div>
        <p class="text-white/90 max-w-2xl">{{ $asignatura->descripcion }}</p>
        
        <!-- Progreso General -->
        <div class="mt-6 bg-white/20 rounded-lg p-4">
            <div class="flex justify-between text-sm mb-2">
                <span>Tu progreso en esta asignatura</span>
                <span class="font-medium">{{ $progreso->porcentaje_completado ?? 0 }}%</span>
            </div>
            <div class="w-full bg-white/30 rounded-full h-3">
                <div class="bg-white h-3 rounded-full" style="width: {{ $progreso->porcentaje_completado ?? 0 }}%"></div>
            </div>
            <div class="flex justify-between text-sm mt-2">
                <span>{{ $progreso->temas_completados ?? 0 }} temas completados</span>
                <span>{{ $progreso->puntos_acumulados ?? 0 }} puntos ganados</span>
            </div>
        </div>
    </div>

    <!-- Niveles y Temas -->
    <div class="space-y-6">
        @foreach([1, 2, 3, 4] as $nivel)
            @php
                $temasNivel = $temas->where('dificultad', $nivel);
                $nivelBloqueado = ($progreso->nivel_actual ?? 1) < $nivel;
                $nivelCompletado = ($progreso->nivel_actual ?? 1) > $nivel;
            @endphp
            
            @if($temasNivel->count() > 0)
                <div class="bg-white rounded-xl shadow-md overflow-hidden {{ $nivelBloqueado ? 'opacity-60' : '' }}">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full {{ $nivelCompletado ? 'bg-green-500' : ($nivelBloqueado ? 'bg-gray-400' : 'bg-blue-500') }} flex items-center justify-center text-white font-bold mr-3">
                                @if($nivelCompletado)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($nivelBloqueado)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                @else
                                    {{ $nivel }}
                                @endif
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">Nivel {{ $nivel }}</h2>
                                <p class="text-sm text-gray-500">{{ $temasNivel->count() }} temas</p>
                            </div>
                        </div>
                        @if($nivelBloqueado)
                            <span class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Bloqueado
                            </span>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($temasNivel as $tema)
                            @php
                                $progresoTema = optional($tema->progresoEstudiantes->first())->estado;
                                $temaCompletado = $progresoTema === 'completado';
                                $temaBloqueado = $nivelBloqueado || $progresoTema === 'bloqueado' || !$progresoTema;
                            @endphp
                            <div class="p-4 flex items-center justify-between {{ $temaBloqueado ? 'bg-gray-50' : ($temaCompletado ? 'bg-green-50' : 'bg-white') }}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 {{ $temaCompletado ? 'bg-green-500 text-white' : ($temaBloqueado ? 'bg-gray-300 text-gray-500' : 'bg-blue-100 text-blue-600') }}">
                                        @if($temaCompletado)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @elseif($temaBloqueado)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        @else
                                            <span class="text-sm font-bold">{{ $tema->orden }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-medium {{ $temaBloqueado ? 'text-gray-500' : 'text-gray-800' }}">{{ $tema->titulo }}</h3>
                                        <p class="text-sm text-gray-500">{{ $tema->juegos_activos_count ?? 0 }} juegos • {{ $tema->evaluaciones_activas_count ?? 0 }} evaluaciones</p>
                                    </div>
                                </div>
                                
                                @if(!$temaBloqueado)
                                    <a href="{{ route('estudiante.temas.show', $tema->slug) }}" 
                                        class="px-4 py-2 {{ $temaCompletado ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg transition text-sm font-medium">
                                        {{ $temaCompletado ? 'Repasar' : 'Comenzar' }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">Bloqueado</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
