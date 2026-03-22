<aside class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white border-r border-gray-200 overflow-y-auto z-20 transform transition-transform duration-300 lg:translate-x-0"
       :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
       @click.away="sidebarOpen = false">
    
    <div class="p-4">
        @if(auth()->user()->esEstudiante())
            <!-- Menú Estudiante -->
            <nav class="space-y-1">
                <a href="{{ route('estudiante.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-home w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="{{ route('estudiante.asignaturas.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.asignaturas.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-book w-5"></i>
                    <span class="font-medium">Mis Asignaturas</span>
                </a>
                
                <a href="{{ route('estudiante.progreso.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.progreso.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="font-medium">Mi Progreso</span>
                </a>
                
                <a href="{{ route('estudiante.juegos.historial') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.juegos.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-gamepad w-5"></i>
                    <span class="font-medium">Mis Juegos</span>
                </a>
                
                {{-- ✅ CORREGIDO: Eliminado $evaluacion->id que causaba el error --}}
                <a href="{{ route('estudiante.evaluaciones.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.evaluaciones.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-clipboard-check w-5"></i>
                    <span class="font-medium">Mis Evaluaciones</span>
                </a>
                
                <a href="{{ route('estudiante.perfil.show') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('estudiante.perfil.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-user w-5"></i>
                    <span class="font-medium">Mi Perfil</span>
                </a>
            </nav>
            
            <!-- Progreso rápido -->
            <div class="mt-8">
                <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Progreso General</h3>
                <div class="px-4">
                    @php
                        $progresionService = app(\App\Services\ProgresionService::class);
                        $resumen = $progresionService->getResumenProgreso(auth()->user());
                    @endphp
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Completado</span>
                            <span class="text-sm font-semibold text-blue-600">{{ $resumen['porcentaje_general'] ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $resumen['porcentaje_general'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $resumen['temas_completados'] ?? 0 }} de {{ $resumen['total_temas'] ?? 0 }} temas
                        </p>
                    </div>
                </div>
            </div>
            
        @else
            <!-- Menú Docente -->
            <nav class="space-y-1">
                <a href="{{ route('docente.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="{{ route('docente.temas.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.temas.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-book-open w-5"></i>
                    <span class="font-medium">Gestión de Temas</span>
                </a>
                
                <a href="{{ route('docente.juegos.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.juegos.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-gamepad w-5"></i>
                    <span class="font-medium">Gestión de Juegos</span>
                </a>
                
                <a href="{{ route('docente.evaluaciones.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.evaluaciones.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-clipboard-list w-5"></i>
                    <span class="font-medium">Evaluaciones</span>
                </a>
                
                <a href="{{ route('docente.calificaciones.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.calificaciones.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-graduation-cap w-5"></i>
                    <span class="font-medium">Calificaciones</span>
                </a>
                
                <a href="{{ route('docente.rankings.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.rankings.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-trophy w-5"></i>
                    <span class="font-medium">Rankings</span>
                </a>
                
                <a href="{{ route('docente.estudiantes.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.estudiantes.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-users w-5"></i>
                    <span class="font-medium">Estudiantes</span>
                </a>

                <a href="{{ route('docente.recuperaciones.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('docente.recuperaciones.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="fas fa-key w-5"></i>
                    <span class="font-medium">Recuperaciones</span>
                </a>
            </nav>
            
            <!-- Estadísticas rápidas -->
            <div class="mt-8">
                <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Resumen Hoy</h3>
                <div class="px-4 space-y-3">
                    <div class="bg-blue-50 rounded-xl p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-gamepad text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-blue-700">{{ \App\Models\IntentosJuego::whereDate('fecha_intento', today())->count() }}</p>
                                <p class="text-xs text-blue-600">Juegos jugados</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-xl p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clipboard-check text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-green-700">{{ \App\Models\ResultadosEvaluacion::whereDate('fecha_realizacion', today())->count() }}</p>
                                <p class="text-xs text-green-600">Evaluaciones</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</aside>

<!-- Overlay para móvil -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/50 z-10 lg:hidden"
     x-transition.opacity></div>
