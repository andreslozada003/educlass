<nav class="bg-white border-b border-gray-200 fixed w-full z-30 top-0">
    <div class="px-4 lg:px-6 py-3">
        <div class="flex items-center justify-between">
            <!-- Logo y toggle -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
                
                <a href="{{ auth()->user()->esEstudiante() ? route('estudiante.dashboard') : route('docente.dashboard') }}" 
                   class="flex items-center gap-2">
                    <div class="w-20 h-15 rounded-xl overflow-hidden bg-white shadow-sm flex items-center justify-center p-0.5">
                        <img src="{{ asset('img/login.jpeg') }}" alt="Logo Educlass" class="w-full h-full object-contain rounded-lg scale-110">
                    </div>
                    <span class="font-display font-bold text-xl text-gray-800 hidden sm:block">Educlass</span>
                </a>
            </div>
            
            <!-- Búsqueda -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <input type="text" 
                           placeholder="Buscar..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="flex items-center gap-3">
                <!-- Botón de ayuda -->
                <button @click="$dispatch('open-help')" 
                        class="p-2 rounded-xl hover:bg-gray-100 transition-colors text-gray-600"
                        title="Ayuda">
                    <i class="fas fa-question-circle text-xl"></i>
                </button>
                
                <!-- Notificaciones -->
                @php
                    $tablaNotificacionesExiste = \Illuminate\Support\Facades\Schema::hasTable('notifications');
                    $notificacionesRecientes = $tablaNotificacionesExiste
                        ? auth()->user()->notifications()->latest()->take(6)->get()
                        : collect();
                    $conteoNoLeidas = $tablaNotificacionesExiste
                        ? auth()->user()->unreadNotifications()->count()
                        : 0;
                @endphp
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="p-2 rounded-xl hover:bg-gray-100 transition-colors text-gray-600 relative"
                            title="Notificaciones">
                        <i class="fas fa-bell text-xl"></i>
                        @if($conteoNoLeidas > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] leading-[18px] text-center font-semibold">
                                {{ $conteoNoLeidas > 9 ? '9+' : $conteoNoLeidas }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-96 max-w-[95vw] bg-white rounded-xl shadow-lg border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Notificaciones</h3>
                            @if($conteoNoLeidas > 0)
                                <form method="POST" action="{{ route('notificaciones.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-primary-600 hover:text-primary-700">
                                        Marcar todas
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            @forelse($notificacionesRecientes as $notificacion)
                                <a href="{{ route('notificaciones.open', $notificacion->id) }}"
                                   class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ data_get($notificacion->data, 'titulo', 'Notificacion') }}
                                        </p>
                                        @if(is_null($notificacion->read_at))
                                            <span class="w-2 h-2 bg-blue-500 rounded-full mt-1"></span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{ data_get($notificacion->data, 'mensaje', 'Tienes una nueva notificacion.') }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        {{ $notificacion->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <div class="p-4 text-center text-gray-500 text-sm">
                                    No tienes notificaciones nuevas
                                </div>
                            @endforelse
                        </div>

                        <div class="p-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                            <a href="{{ route('notificaciones.index') }}"
                               class="block text-center text-sm text-primary-600 hover:text-primary-700">
                                Ver todas las notificaciones
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Perfil -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center gap-2 p-1 pr-3 rounded-full hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-semibold text-sm">
                            {{ auth()->user()->iniciales }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->nombre }}</span>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-50 py-2">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="font-semibold text-gray-800">{{ auth()->user()->nombre }}</p>
                            <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                        
                        @if(auth()->user()->esEstudiante())
                            <a href="{{ route('estudiante.perfil.show') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                                <i class="fas fa-user mr-2"></i> Mi Perfil
                            </a>
                            <a href="{{ route('estudiante.progreso.index') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                                <i class="fas fa-chart-line mr-2"></i> Mi Progreso
                            </a>
                        @else
                            <a href="{{ route('docente.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        @endif
                        
                        <div class="border-t border-gray-100 mt-2 pt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer para el nav fijo -->
<div class="h-16"></div>
