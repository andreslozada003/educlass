@php
    $esEstudiante = auth()->check() && auth()->user()->esEstudiante();
    $abrirAlIniciar = session('show_manual', false) ? 'true' : 'false';

    if ($esEstudiante) {
        $manualTitulo = 'Manual de uso - Estudiante';
        $manualTexto = 'Bienvenido a Educlass. Para avanzar, entra a una asignatura y abre el primer tema desbloqueado. '
            . 'Lee el contenido del tema y marca avance de lectura hasta completar al menos el ochenta por ciento. '
            . 'Despues realiza el juego y la evaluacion del tema. '
            . 'Cuando apruebas, se desbloquea el siguiente tema. '
            . 'En Mi Progreso puedes ver tu porcentaje, puntos, logros y avance por asignatura. '
            . 'En Notificaciones recibiras avisos de nuevos temas, juegos, evaluaciones y mensajes del docente.';
        $secciones = [
            ['icon' => 'fas fa-book text-primary-500', 'titulo' => 'Ruta de aprendizaje', 'texto' => 'Ingresa a una asignatura y completa los temas en orden.'],
            ['icon' => 'fas fa-eye text-blue-500', 'titulo' => 'Lectura minima', 'texto' => 'Debes leer al menos el 80% del tema para habilitar evaluacion.'],
            ['icon' => 'fas fa-gamepad text-green-500', 'titulo' => 'Juego del tema', 'texto' => 'Completa el juego para sumar puntos y cumplir requisitos del tema.'],
            ['icon' => 'fas fa-clipboard-check text-purple-500', 'titulo' => 'Evaluacion', 'texto' => 'Aprueba la evaluacion para cerrar el tema y desbloquear el siguiente.'],
            ['icon' => 'fas fa-bell text-yellow-500', 'titulo' => 'Notificaciones', 'texto' => 'Revisa mensajes y avisos del sistema o de tu docente.'],
        ];
    } else {
        $manualTitulo = 'Manual de uso - Docente';
        $manualTexto = 'Bienvenido a Educlass. Primero crea temas por asignatura y ordenalos segun el plan academico. '
            . 'Luego agrega juegos y evaluaciones para cada tema y activalos cuando esten listos. '
            . 'En Estudiantes puedes revisar progreso, puntajes, resultados y enviar mensajes directos. '
            . 'Tambien puedes reiniciar intentos cuando sea necesario. '
            . 'Usa rankings y calificaciones para seguimiento del rendimiento del curso.';
        $secciones = [
            ['icon' => 'fas fa-book-open text-primary-500', 'titulo' => 'Crear temas', 'texto' => 'Crea contenido por asignatura y organiza por orden y dificultad.'],
            ['icon' => 'fas fa-gamepad text-green-500', 'titulo' => 'Configurar juegos', 'texto' => 'Agrega preguntas, puntaje, tiempo e intentos permitidos.'],
            ['icon' => 'fas fa-clipboard-list text-purple-500', 'titulo' => 'Configurar evaluaciones', 'texto' => 'Define preguntas, umbral de aprobacion e intentos por estudiante.'],
            ['icon' => 'fas fa-user-graduate text-blue-500', 'titulo' => 'Gestionar estudiantes', 'texto' => 'Consulta progreso y envia mensajes como habilitacion de intentos.'],
            ['icon' => 'fas fa-chart-line text-orange-500', 'titulo' => 'Seguimiento', 'texto' => 'Monitorea rankings y calificaciones para evaluar avance del grupo.'],
        ];
    }
@endphp

<div
    x-data="{
        open: {{ $abrirAlIniciar }},
        speaking: false,
        supported: 'speechSynthesis' in window,
        utterance: null,
        speechText: @js($manualTexto),
        openHelp() { this.open = true; },
        closeHelp() { this.stopSpeech(); this.open = false; },
        playSpeech() {
            if (!this.supported) return;
            this.stopSpeech();
            this.utterance = new SpeechSynthesisUtterance(this.speechText);
            this.utterance.lang = 'es-ES';
            this.utterance.rate = 1;
            this.utterance.pitch = 1;
            this.utterance.onend = () => { this.speaking = false; };
            this.utterance.onerror = () => { this.speaking = false; };
            window.speechSynthesis.speak(this.utterance);
            this.speaking = true;
        },
        stopSpeech() {
            if (!this.supported) return;
            window.speechSynthesis.cancel();
            this.speaking = false;
        }
    }"
    @open-help.window="openHelp()"
    x-show="open"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div x-show="open" x-transition.opacity @click="closeHelp()" class="fixed inset-0 bg-black/50"></div>

        <div x-show="open" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[88vh] overflow-hidden">
            <div class="bg-gradient-to-r from-primary-500 to-primary-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-circle-info text-white text-2xl"></i>
                    <h2 class="text-xl font-bold text-white">{{ $manualTitulo }}</h2>
                </div>
                <button @click="closeHelp()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[64vh]">
                <div class="mb-5 p-4 rounded-xl bg-blue-50 border border-blue-100">
                    <p class="text-sm text-blue-800 leading-relaxed">
                        Este manual aparece al iniciar sesion para guiarte rapidamente en el uso de la plataforma.
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($secciones as $seccion)
                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                                <i class="{{ $seccion['icon'] }}"></i>
                                {{ $seccion['titulo'] }}
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ $seccion['texto'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    @click="playSpeech()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!supported || speaking"
                >
                    <i class="fas fa-play mr-1"></i> Reproducir manual
                </button>
                <button
                    type="button"
                    @click="stopSpeech()"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!supported || !speaking"
                >
                    <i class="fas fa-stop mr-1"></i> Detener audio
                </button>
                <button @click="closeHelp()" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

