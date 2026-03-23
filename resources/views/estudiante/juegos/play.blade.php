@extends('layouts.app')

@section('title', $juego->titulo)

@section('content')
<div class="container mx-auto px-4 py-8" x-data="gameEngine()" x-init="initGame()">
    <div class="mb-6">
        <div class="flex items-center text-gray-600">
            <a href="{{ route('estudiante.asignaturas.index') }}" class="hover:text-blue-600">Asignaturas</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('estudiante.asignaturas.show', $juego->tema->asignatura) }}" class="hover:text-blue-600">{{ $juego->tema->asignatura->nombre }}</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-800 font-medium">{{ $juego->titulo }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center text-2xl">{{ $juego->tipo_icono }}</div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $juego->titulo }}</h1>
                    <p class="text-gray-600">{{ $juego->tema->titulo }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Puntaje base</div>
                <div class="text-2xl font-bold text-purple-600">{{ $juego->puntaje_base }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-3 flex justify-between items-center">
            <div class="font-medium">Pregunta <span x-text="currentQuestion + 1"></span> de <span x-text="totalQuestions"></span></div>
            <div class="flex items-center gap-4">
                <div x-show="timeLeft > 0" class="font-medium text-orange-600" x-text="formatTime(timeLeft)"></div>
                <div class="font-bold text-purple-600" x-text="score"></div>
            </div>
        </div>

        <div class="p-8">
            <div x-show="gameState === 'start'" class="text-center py-10">
                <div class="text-5xl mb-4">{{ $juego->tipo_icono }}</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Listo para comenzar</h2>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">{{ $juego->descripcion }}</p>
                <div class="text-sm text-gray-500 mb-6">
                    <p>{{ $preguntas->count() }} preguntas</p>
                    <p>Dificultad {{ $juego->dificultad }}</p>
                    <p>Tiempo: {{ $juego->tiempo_limite_formateado }}</p>
                </div>
                <button @click="startGame()" class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-medium">Comenzar Juego</button>
            </div>

            <div x-show="gameState === 'playing'" x-cloak>
                <template x-if="currentQuestionData">
                    <div>
                        <div class="mb-6">
                            <h3 class="text-xl font-medium text-gray-800 mb-3" x-text="currentQuestionData.enunciado || currentQuestionData.pregunta || 'Pregunta'"></h3>
                            <template x-if="currentQuestionData.imagen_apoyo">
                                <img :src="'/storage/preguntas/' + currentQuestionData.imagen_apoyo" class="max-w-md rounded-lg mb-3" alt="Imagen de apoyo">
                            </template>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="currentQuestionData.opciones_normalizadas.length > 0">
                            <template x-for="(opcion, index) in currentQuestionData.opciones_normalizadas" :key="index">
                                <button @click="selectAnswer(index)"
                                    :disabled="answerSubmitted"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600': selectedAnswer === index && !answerSubmitted,
                                        'bg-green-500 text-white border-green-500': answerSubmitted && isCorrect && selectedAnswer === index,
                                        'bg-red-500 text-white border-red-500': answerSubmitted && !isCorrect && selectedAnswer === index,
                                        'bg-white text-gray-700 border-gray-200 hover:border-blue-400': selectedAnswer !== index && !answerSubmitted,
                                        'opacity-50': answerSubmitted && selectedAnswer !== index
                                    }"
                                    class="p-4 border-2 rounded-lg text-left transition font-medium">
                                    <span x-text="opcion.texto"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="currentQuestionData.opciones_normalizadas.length === 0" class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Tu respuesta</label>
                            <input type="text"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :value="textAnswer"
                                @input="textAnswer = $event.target.value"
                                :disabled="answerSubmitted"
                                placeholder="Escribe tu respuesta">
                        </div>

                        <div class="mt-8 text-center">
                            <button x-show="!answerSubmitted && canSubmit()" @click="submitAnswer()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-medium">Confirmar Respuesta</button>
                            <button x-show="answerSubmitted" @click="nextQuestion()" class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-medium">Siguiente Pregunta ?</button>
                        </div>

                        <div x-show="answerSubmitted" class="mt-6 text-center">
                            <p x-show="isCorrect" class="text-green-600 font-medium text-lg">Correcto</p>
                            <p x-show="!isCorrect" class="text-red-600 font-medium text-lg">Incorrecto</p>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="gameState === 'finished'" x-cloak class="text-center py-10">
                <div class="text-5xl mb-4">??</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Juego completado</h2>

                <div class="bg-gray-50 rounded-xl p-6 max-w-md mx-auto mb-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-2xl font-bold text-purple-600" x-text="score"></p>
                            <p class="text-sm text-gray-500">Puntos</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600" x-text="correctAnswers"></p>
                            <p class="text-sm text-gray-500">Correctas</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600" x-text="totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) + '%' : '0%'"></p>
                            <p class="text-sm text-gray-500">Precisión</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center space-x-4">
                    <a href="{{ route('estudiante.temas.show', $juego->tema) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-medium">Volver al Tema</a>
                    <button @click="restartGame()" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-medium">Jugar de Nuevo</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function gameEngine() {
    return {
        gameState: 'start',
        currentQuestion: 0,
        totalQuestions: {{ $preguntas->count() }},
        questions: @json($preguntas->values()),
        currentQuestionData: null,
        selectedAnswer: null,
        textAnswer: '',
        answerSubmitted: false,
        isCorrect: false,
        score: 0,
        correctAnswers: 0,
        timeLeft: {{ $juego->tiempo_limite_segundos ?? 0 }},
        timer: null,
        responses: {},
        startedAt: null,

        initGame() {
            this.questions = this.questions.map((p) => {
                let opciones = p.opciones ?? [];
                if (typeof opciones === 'string') {
                    try { opciones = JSON.parse(opciones); } catch (_) { opciones = []; }
                }
                if (!Array.isArray(opciones)) opciones = [];

                if ((p.tipo === 'verdadero_falso' || p.tipo === 'vf') && opciones.length === 0) {
                    opciones = ['Verdadero', 'Falso'];
                }

                const opciones_normalizadas = opciones
                    .filter(o => o !== null && o !== undefined && String(o).trim() !== '')
                    .map((o, i) => {
                        if (typeof o === 'object') {
                            return {
                                texto: o.texto ?? o.label ?? o.value ?? o.valor ?? `Opcion ${i + 1}`,
                                valor: String(o.value ?? o.valor ?? o.texto ?? o.label ?? i),
                            };
                        }
                        return { texto: String(o), valor: String(o) };
                    });

                return { ...p, opciones_normalizadas };
            });
        },

        startGame() {
            this.gameState = 'playing';
            this.currentQuestion = 0;
            this.score = 0;
            this.correctAnswers = 0;
            this.responses = {};
            this.startedAt = Date.now();
            this.loadQuestion();
            this.startTimer();
        },

        loadQuestion() {
            this.currentQuestionData = this.questions[this.currentQuestion] || null;
            this.selectedAnswer = null;
            this.textAnswer = '';
            this.answerSubmitted = false;
            this.isCorrect = false;
        },

        startTimer() {
            if (this.timeLeft <= 0) return;
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    this.finishGame();
                }
            }, 1000);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        selectAnswer(index) {
            if (!this.answerSubmitted) this.selectedAnswer = index;
        },

        canSubmit() {
            if (!this.currentQuestionData) return false;
            if (this.currentQuestionData.opciones_normalizadas.length > 0) {
                return this.selectedAnswer !== null;
            }
            return this.textAnswer.trim() !== '';
        },

        normalize(v) {
            return String(v ?? '').trim().toLowerCase();
        },

        isCorrectAnswer(question, responseValue) {
            let correcta = question.respuesta_correcta;
            if (typeof correcta === 'string') {
                const raw = correcta.trim();
                if ((raw.startsWith('[') && raw.endsWith(']')) || (raw.startsWith('{') && raw.endsWith('}'))) {
                    try { correcta = JSON.parse(raw); } catch (_) {}
                }
            }

            if (Array.isArray(correcta)) {
                return correcta.map(v => this.normalize(v)).includes(this.normalize(responseValue));
            }
            return this.normalize(correcta) === this.normalize(responseValue);
        },

        submitAnswer() {
            if (!this.canSubmit()) return;

            this.answerSubmitted = true;

            let responseValue = this.textAnswer;
            if (this.currentQuestionData.opciones_normalizadas.length > 0) {
                const selected = this.currentQuestionData.opciones_normalizadas[this.selectedAnswer];
                responseValue = selected ? selected.valor : '';
            }

            const correct = this.isCorrectAnswer(this.currentQuestionData, responseValue);
            this.isCorrect = correct;
            if (correct) {
                this.correctAnswers++;
                this.score += this.currentQuestionData.puntaje || 10;
            }

            this.responses[this.currentQuestionData.id] = responseValue;
        },

        nextQuestion() {
            this.currentQuestion++;
            if (this.currentQuestion >= this.totalQuestions) {
                this.finishGame();
                return;
            }
            this.loadQuestion();
        },

        finishGame() {
            if (this.timer) clearInterval(this.timer);
            this.gameState = 'finished';

            const elapsedSeconds = Math.max(0, Math.round((Date.now() - (this.startedAt || Date.now())) / 1000));

            fetch('{{ route('estudiante.juegos.guardar', $juego->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    respuestas: this.responses,
                    duracion_segundos: elapsedSeconds
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(() => {});
        },

        restartGame() {
            this.timeLeft = {{ $juego->tiempo_limite_segundos ?? 0 }};
            this.gameState = 'start';
            this.currentQuestion = 0;
            this.score = 0;
            this.correctAnswers = 0;
            this.responses = {};
        }
    }
}
</script>
@endsection
