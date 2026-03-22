@extends('layouts.app')

@section('title', 'Recuperaciones de Contrasena')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Solicitudes de Recuperacion</h1>
        <p class="text-gray-500">Atiende solicitudes de estudiantes que olvidaron su contrasena.</p>
    </div>

    <div class="rounded-xl border p-4 {{ config('whatsapp.enabled') ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
        @if(config('whatsapp.enabled'))
            <p class="text-sm text-green-700">
                WhatsApp activo. Los mensajes se intentaran enviar al telefono del estudiante.
            </p>
        @else
            <p class="text-sm text-yellow-700">
                WhatsApp desactivado. Para activarlo configura variables WHATSAPP_* en .env.
            </p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Pendientes</h2>
        </div>
        <div class="p-6">
            @forelse($pendientes as $solicitud)
                <div class="border border-gray-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $solicitud->nombre_estudiante }}</p>
                            <p class="text-sm text-gray-500">
                                Solicito: {{ optional($solicitud->solicitado_en)->format('d/m/Y H:i') ?? '-' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Telefono: {{ optional($solicitud->estudiante)->telefono ?? 'No registrado' }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('docente.recuperaciones.responder', $solicitud->id) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva clave (opcional)</label>
                            <input type="text" name="nueva_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                   placeholder="Si lo dejas vacio, se genera una clave temporal automaticamente">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje para estudiante (opcional)</label>
                            <textarea name="mensaje" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                      placeholder="Ejemplo: Tu nueva clave temporal es: ABC12345"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                                Enviar clave al estudiante
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-gray-500">No hay solicitudes pendientes.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Historial</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Docente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Respuesta</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($historial as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $item->nombre_estudiante }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full {{ $item->estado === 'atendida' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($item->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ optional($item->docente)->nombre ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $item->mensaje_docente ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ optional($item->respondido_en)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Sin historial todavia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($historial->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $historial->links() }}</div>
        @endif
    </div>
</div>
@endsection
