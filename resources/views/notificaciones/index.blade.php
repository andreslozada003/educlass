@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-display font-bold text-gray-800">Notificaciones</h1>
                <p class="text-gray-500 mt-1">Aqui veras tus alertas de temas, juegos y evaluaciones.</p>
            </div>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notificaciones.read-all') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm">
                        Marcar todas como leidas
                    </button>
                </form>
            @endif
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($notificaciones as $notificacion)
                <div class="p-5 {{ is_null($notificacion->read_at) ? 'bg-blue-50/40' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800">
                                {{ data_get($notificacion->data, 'titulo', 'Notificacion') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ data_get($notificacion->data, 'mensaje', '') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $notificacion->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('notificaciones.open', $notificacion->id) }}"
                               class="px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm text-gray-700">
                                Abrir
                            </a>
                            @if(is_null($notificacion->read_at))
                                <form method="POST" action="{{ route('notificaciones.read', $notificacion->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-sm text-white">
                                        Leida
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-500">
                    No hay notificaciones todavia.
                </div>
            @endforelse
        </div>

        @if($notificaciones->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

