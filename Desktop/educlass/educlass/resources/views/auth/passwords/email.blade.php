@extends('layouts.app')

@section('title', 'Recuperar Contrasena')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 via-white to-primary-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl w-full grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Recuperacion de Estudiante</h2>
            <p class="text-sm text-gray-500 mb-4">Ingresa tu nombre completo. Se enviara una solicitud al docente.</p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="tipo_recuperacion" value="estudiante">

                <div>
                    <label for="nombre_estudiante" class="block text-sm font-medium text-gray-700 mb-1">Nombre del estudiante</label>
                    <input id="nombre_estudiante" name="nombre_estudiante" type="text" required
                           value="{{ old('nombre_estudiante', request('nombre_estudiante')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('nombre_estudiante') border-red-500 @enderror"
                           placeholder="Ejemplo: Luisa Gomez">
                    @error('nombre_estudiante')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white bg-primary-600 hover:bg-primary-700">
                    Solicitar recuperacion al docente
                </button>
            </form>

            @if(isset($solicitud) && $solicitud)
                <div class="mt-5 p-4 rounded-xl border {{ $solicitud->estado === 'atendida' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                    <p class="text-sm font-semibold {{ $solicitud->estado === 'atendida' ? 'text-green-700' : 'text-yellow-700' }}">
                        Estado: {{ ucfirst($solicitud->estado) }}
                    </p>
                    @if($solicitud->estado === 'atendida' && $solicitud->mensaje_docente)
                        <p class="text-sm text-green-700 mt-2">{{ $solicitud->mensaje_docente }}</p>
                    @else
                        <p class="text-sm text-yellow-700 mt-2">Tu solicitud esta en revision del docente.</p>
                    @endif
                </div>
            @endif

            <a href="{{ route('login') }}"
               class="mt-4 inline-flex items-center justify-center w-full py-2 px-4 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
                Volver al login
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Recuperacion de Docente</h2>
            <p class="text-sm text-gray-500 mb-4">Ingresa tu celular docente y recibiras una clave temporal por WhatsApp.</p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="tipo_recuperacion" value="docente">

                <div>
                    <label for="telefono_docente" class="block text-sm font-medium text-gray-700 mb-1">Celular del docente</label>
                    <input id="telefono_docente" name="telefono_docente" type="text" required value="{{ old('telefono_docente') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('telefono_docente') border-red-500 @enderror"
                           placeholder="3001234567 o 573001234567">
                    @error('telefono_docente')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white bg-green-600 hover:bg-green-700">
                    Enviar clave al celular
                </button>
            </form>

            <div class="mt-6 text-sm text-gray-500">
                <p>Si eres estudiante, usa el bloque izquierdo para solicitar apoyo a tu docente.</p>
            </div>

            <a href="{{ route('login') }}"
               class="mt-4 inline-flex items-center justify-center w-full py-2 px-4 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
                Volver al login
            </a>
        </div>
    </div>
</div>
@endsection
