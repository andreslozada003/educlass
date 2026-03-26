@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
@php
    $nombreCompleto = trim($estudiante->nombre . ' ' . ($estudiante->apellido ?? ''));
    $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : 'Estudiante';
@endphp

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 p-6 text-center text-white">
                    <div class="w-32 h-32 mx-auto rounded-full bg-white/20 border-4 border-white/30 flex items-center justify-center overflow-hidden text-3xl font-bold">
                        @if($estudiante->avatar)
                            <img src="{{ $estudiante->avatar_url }}" alt="{{ $nombreCompleto }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $estudiante->iniciales }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold mt-4">{{ $nombreCompleto }}</h1>
                    <p class="text-blue-100 mt-1">{{ $estudiante->email }}</p>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Colegio</span>
                        <span class="font-medium text-right">{{ $estudiante->colegio->nombre ?? 'No registrado' }}</span>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Telefono</span>
                        <span class="font-medium text-right">{{ $estudiante->telefono ?: 'No registrado' }}</span>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Miembro desde</span>
                        <span class="font-medium text-right">{{ optional($estudiante->created_at)->format('d/m/Y') ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Ultimo acceso</span>
                        <span class="font-medium text-right">{{ optional($estudiante->ultimo_acceso)->diffForHumans() ?: 'Sin registros' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Resumen</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg bg-purple-50 p-4 text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $estadisticas['puntos'] ?? 0 }}</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Puntos</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $estadisticas['nivel_max'] ?? 1 }}</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Nivel</p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $estadisticas['juegos'] ?? 0 }}</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Juegos</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-4 text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ $estadisticas['evaluaciones'] ?? 0 }}</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Evaluaciones</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-4 text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ $estadisticas['logros'] ?? 0 }}</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Logros</p>
                    </div>
                    <div class="rounded-lg bg-sky-50 p-4 text-center">
                        <p class="text-2xl font-bold text-sky-600">{{ number_format($estadisticas['porcentaje_general'] ?? 0, 0) }}%</p>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Progreso</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-700 p-[1px] shadow-xl shadow-slate-200/80">
                <div class="rounded-[27px] bg-white p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-cyan-600">Personaliza tu perfil</p>
                            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-900">Cambiar avatar</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Sube una imagen clara en formato JPG, PNG o GIF de hasta 2 MB.
                            </p>
                        </div>
                        <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                    </div>

                    <form action="{{ route('estudiante.perfil.avatar') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="avatar-upload-form">
                        @csrf

                        <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden">

                        <label for="avatar-input" class="group flex cursor-pointer items-center gap-4 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 transition hover:border-cyan-400 hover:bg-cyan-50/70">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-cyan-600 shadow-sm ring-1 ring-slate-200 transition group-hover:bg-cyan-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800">Seleccionar imagen</p>
                                <p id="avatar-file-name" class="mt-1 truncate text-sm text-slate-500">Ningun archivo seleccionado</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white transition group-hover:bg-cyan-600">
                                Elegir
                            </span>
                        </label>

                        <div class="flex items-center gap-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-lg font-bold text-slate-600">
                                @if($estudiante->avatar)
                                    <img
                                        id="avatar-preview-image"
                                        src="{{ $estudiante->avatar_url }}"
                                        alt="{{ $nombreCompleto }}"
                                        class="h-full w-full object-cover"
                                    >
                                    <span id="avatar-preview-fallback" class="hidden">{{ $estudiante->iniciales }}</span>
                                @else
                                    <img
                                        id="avatar-preview-image"
                                        src=""
                                        alt="{{ $nombreCompleto }}"
                                        class="hidden h-full w-full object-cover"
                                    >
                                    <span id="avatar-preview-fallback">{{ $estudiante->iniciales }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Vista previa del avatar</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Usa una imagen centrada para que se vea bien en el perfil y en los rankings.
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="group inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-cyan-200">
                            <svg class="h-5 w-5 transition group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Actualizar avatar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Datos del perfil</h2>
                        <p class="text-sm text-gray-500 mt-1">Actualiza tu nombre, telefono y colegio.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Temas completados</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $estadisticas['temas_completados'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Progreso general</span>
                        <span>{{ number_format($estadisticas['porcentaje_general'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500" style="width: {{ min(100, max(0, $estadisticas['porcentaje_general'] ?? 0)) }}%"></div>
                    </div>
                </div>

                <form action="{{ route('estudiante.perfil.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $estudiante->nombre) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        >
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">Telefono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="{{ old('telefono', $estudiante->telefono) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label for="colegio_id" class="block text-sm font-medium text-gray-700 mb-2">Colegio</label>
                        <select
                            id="colegio_id"
                            name="colegio_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        >
                            <option value="">Selecciona un colegio</option>
                            @foreach($colegios as $colegio)
                                <option value="{{ $colegio->id }}" @selected(old('colegio_id', $estudiante->colegio_id) == $colegio->id)>
                                    {{ $colegio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700 transition">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Mis rankings</h2>
                    @if($rankings->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($rankings as $ranking)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-gray-800">
                                                {{ $ranking->asignatura->nombre ?? 'Ranking General' }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $ranking->categoria_nombre }} · Nivel {{ $ranking->nivel_alcanzado ?? 1 }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-purple-600">#{{ $ranking->posicion ?? '-' }}</p>
                                            <p class="text-sm text-gray-500">{{ $ranking->puntaje_total ?? 0 }} puntos</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Aun no apareces en los rankings.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Seguridad</h2>
                    <form action="{{ route('estudiante.perfil.password') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Contrasena actual</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nueva contrasena</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar contrasena</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required
                            >
                        </div>

                        <button type="submit" class="rounded-lg bg-slate-800 px-5 py-3 font-medium text-white hover:bg-slate-900 transition">
                            Actualizar contrasena
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Mis logros</h2>
                    <span class="text-sm text-gray-500">{{ $logros->count() }} desbloqueados</span>
                </div>

                @if($logros->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($logros as $logroEstudiante)
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-200 text-lg font-bold text-yellow-700">
                                        {{ strtoupper(substr(optional($logroEstudiante->logro)->nombre ?? 'L', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800">{{ optional($logroEstudiante->logro)->nombre ?? 'Logro' }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ optional($logroEstudiante->logro)->descripcion ?? 'Logro desbloqueado' }}</p>
                                        <p class="text-xs text-gray-400 mt-2">
                                            {{ optional($logroEstudiante->fecha_obtenido)->diffForHumans() ?: 'Fecha no disponible' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Aun no has desbloqueado logros. Completa juegos y evaluaciones para ganar los primeros.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Actividad reciente</h2>

                @if($actividades->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($actividades as $actividad)
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-lg border border-gray-100 p-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">
                                        {{ $actividad->tipo === 'juego' ? 'Juego completado' : 'Evaluacion realizada' }}
                                    </p>
                                    <p class="text-gray-700 mt-1">{{ $actividad->titulo }}</p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $actividad->detalle }}</p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ optional($actividad->created_at)->diffForHumans() ?: '-' }}
                                    </p>
                                </div>
                                <div class="text-left md:text-right">
                                    <p class="text-lg font-bold {{ ($actividad->puntuacion ?? 0) >= 60 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $actividad->puntuacion ?? 0 }}%
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $actividad->puntaje_obtenido ?? 0 }}/{{ $actividad->puntaje_total ?? 0 }} pts
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No hay actividad reciente para mostrar.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('avatar-input');
        const fileName = document.getElementById('avatar-file-name');
        const previewImage = document.getElementById('avatar-preview-image');
        const previewFallback = document.getElementById('avatar-preview-fallback');

        if (!input || !fileName || !previewImage || !previewFallback) {
            return;
        }

        input.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];

            if (!file) {
                fileName.textContent = 'Ningun archivo seleccionado';
                return;
            }

            fileName.textContent = file.name;

            if (file.type.startsWith('image/')) {
                const objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                previewImage.classList.remove('hidden');
                previewFallback.classList.add('hidden');

                previewImage.onload = function () {
                    URL.revokeObjectURL(objectUrl);
                };
            }
        });
    });
</script>
@endpush
