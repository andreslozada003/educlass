@extends('layouts.app')

@section('title', $evaluacion->titulo)

@section('content')
<div class="container mx-auto px-4 py-8" x-data="evaluationEngine()" x-init="initEval()">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $evaluacion->titulo }}</h1>
                <p class="text-gray-600">{{ $evaluacion->tema->asignatura->nombre }} - {{ $evaluacion->tema->titulo }}</p>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Pregunta</p>
                    <p class="text-xl font-bold text-blue-600"><span x-text="currentQuestion + 1"></span>/{{ $preguntas->count() }}</p>
                </div>
                <div class="text-center" x-show="timeLeft > 0">
                    <p class="text-sm text-gray-500">Tiempo restante</p>
                    <p class="text-xl font-bold" :class="timeLeft < 60 ? 'text-red-600' : 'text-orange-600'" x-text="formatTime(timeLeft)"></p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${((currentQuestion + 1) / {{ $preguntas->count() }}) * 100}%`"></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <div x-show="evalState === 'instructions'" class="text-center py-12">
            <div class="text-6xl mb-6">??</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Instrucciones</h2>

            @if($evaluacion->descripcion)
                <p class="text-gray-600 mb-6 max-w-lg mx-auto">{{ $evaluacion->descripcion }}</p>
            @endif

            <div class="bg-gray-50 rounded-lg p-6 max-w-md mx-auto mb-8 text-left">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center">
                        <span>{{ $preguntas->count() }} preguntas en total</span>
                    </div>
                    <div class="flex items-center">
                        <span>Tiempo límite: {{ $evaluacion->tiempo_limite_minutos }} minutos</span>
                    </div>
                    <div class="flex items-center">
                        <span>Calificación mínima para aprobar: {{ $evaluacion->umbral_aprobacion }}%</span>
                    </div>
                    <div class="flex items-center">
                        <span>Intentos permitidos: {{ $evaluacion->intentos_permitidos }}</span>
                    </div>
                </div>
            </div>

            <button @click="startEval()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium text-lg">
                Comenzar Evaluación
            </button>
        </div>

        <div x-show="evalState === 'taking'" x-cloak>
            <template x-if="currentQuestionData">
                <div>
                    <div class="mb-8">
                        <h3 class="text-xl font-medium text-gray-800 mb-4" x-text="currentQuestionData.enunciado || currentQuestionData.pregunta || 'Pregunta'"></h3>
                        <div x-show="currentQuestionData.imagen_apoyo" class="mb-4">
                            <img :src="currentQuestionData.imagen_url || '/storage/preguntas/' + currentQuestionData.imagen_apoyo"
                                 class="max-w-md rounded-lg"
                                 alt="Imagen de la pregunta">
                        </div>
                    </div>

                    <div class="space-y-3" x-show="currentQuestionData.opciones_normalizadas.length > 0">
                        <template x-for="(opcion, index) in currentQuestionData.opciones_normalizadas" :key="index">
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
                                :class="{ 'border-blue-500 bg-blue-50': answers[currentQuestionData.id] === opcion.value }">
                                <input type="radio"
                                    :name="'question_' + currentQuestionData.id"
                                    :value="opcion.value"
                                    :checked="answers[currentQuestionData.id] === opcion.value"
                                    @change="answers[currentQuestionData.id] = opcion.value"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700" x-text="opcion.texto"></span>
                            </label>
                        </template>
                    </div>

                    <div x-show="currentQuestionData.opciones_normalizadas.length === 0" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Tu respuesta</label>
                        <input type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :value="answers[currentQuestionData.id] || ''"
                            @input="answers[currentQuestionData.id] = $event.target.value"
                            placeholder="Escribe tu respuesta">
                    </div>

                    <div class="flex justify-between mt-8">
                        <button @click="prevQuestion()" x-show="currentQuestion > 0"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            ? Anterior
                        </button>
                        <button @click="nextQuestion()"
                            x-show="currentQuestion < {{ $preguntas->count() - 1 }}"
                            :disabled="!hasCurrentAnswer()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed ml-auto">
                            Siguiente ?
                        </button>
                        <button @click="finishEval()"
                            x-show="currentQuestion === {{ $preguntas->count() - 1 }}"
                            :disabled="!hasCurrentAnswer()"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed ml-auto">
                            Finalizar
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="evalState === 'results'" x-cloak class="text-center py-8">
            <div class="mb-6">
                <template x-if="passed"><div class="text-6xl">??</div></template>
                <template x-if="!passed"><div class="text-6xl">??</div></template>
            </div>

            <h2 class="text-3xl font-bold mb-2" :class="passed ? 'text-green-600' : 'text-red-600'"
                x-text="passed ? 'Has aprobado' : 'No has alcanzado la calificación mínima'"></h2>

            <div class="bg-gray-50 rounded-xl p-6 max-w-md mx-auto my-8">
                <div class="text-5xl font-bold mb-2" :class="passed ? 'text-green-600' : 'text-red-600'">
                    <span x-text="score"></span>%
                </div>
                <p class="text-gray-500">Calificación obtenida</p>

                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-200">
                    <div>
                        <p class="text-2xl font-bold text-blue-600" x-text="correctAnswers"></p>
                        <p class="text-sm text-gray-500">Correctas</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-600" x-text="{{ $preguntas->count() }} - correctAnswers"></p>
                        <p class="text-sm text-gray-500">Incorrectas</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-center space-x-4">
                <a href="{{ route('estudiante.temas.show', $evaluacion->tema) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Volver al Tema
                </a>
                <template x-if="!passed && intentosRestantes > 0">
                    <a href="{{ route('estudiante.evaluaciones.take', $evaluacion) }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Reintentar (<span x-text="intentosRestantes"></span> restantes)
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function evaluationEngine() {
    return {
        evalState: 'instructions',
        currentQuestion: 0,
        questions: @json($preguntas),
        currentQuestionData: null,
        answers: {},
        timeLeft: 0,
        timer: null,
        score: 0,
        correctAnswers: 0,
        passed: false,
        intentosRestantes: {{ $intentosRestantes }},

        initEval() {
            this.questions = this.questions.map(p => {
                let opciones = p.opciones || [];
                if (typeof opciones === 'string') {
                    try { opciones = JSON.parse(opciones); } catch (_) { opciones = []; }
                }
                if (!Array.isArray(opciones)) opciones = [];

                if (p.tipo === 'vf' && opciones.length === 0) {
                    opciones = ['Verdadero', 'Falso'];
                }

                const normalizadas = opciones
                    .filter(o => o !== null && o !== undefined && String(o).trim() !== '')
                    .map((o, i) => {
                        if (typeof o === 'object') {
                            const texto = o.texto ?? o.label ?? o.valor ?? `Opcion ${i + 1}`;
                            const value = o.value ?? o.valor ?? texto;
                            return { texto, value: String(value) };
                        }
                        return { texto: String(o), value: String(o) };
                    });

                return { ...p, opciones, opciones_normalizadas: normalizadas };
            });
        },

        startEval() {
            this.evalState = 'taking';
            this.currentQuestion = 0;
            this.answers = {};
            this.loadQuestion();
            this.startTimer();
        },

        loadQuestion() {
            this.currentQuestionData = this.questions[this.currentQuestion];
        },

        hasCurrentAnswer() {
            if (!this.currentQuestionData) return false;
            const r = this.answers[this.currentQuestionData.id];
            return r !== undefined && String(r).trim() !== '';
        },

        startTimer() {
            this.timeLeft = {{ $evaluacion->tiempo_limite_minutos }} * 60;
            this.timer = setInterval(() => {
                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    this.finishEval();
                }
            }, 1000);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        prevQuestion() {
            if (this.currentQuestion > 0) {
                this.currentQuestion--;
                this.loadQuestion();
            }
        },

        nextQuestion() {
            if (this.currentQuestion < this.questions.length - 1) {
                this.currentQuestion++;
                this.loadQuestion();
            }
        },

        isCorrectAnswer(question, respuesta) {
            const correcta = question.respuesta_correcta;
            if (correcta === null || correcta === undefined) return false;

            if (typeof correcta === 'string') {
                const c = correcta.trim();
                if ((c.startsWith('[') && c.endsWith(']')) || (c.startsWith('{') && c.endsWith('}'))) {
                    try {
                        const parsed = JSON.parse(c);
                        if (Array.isArray(parsed)) {
                            return parsed.map(v => String(v).toLowerCase()).includes(String(respuesta).toLowerCase());
                        }
                    } catch (_) {}
                }
                return c.toLowerCase() === String(respuesta).toLowerCase();
            }

            if (Array.isArray(correcta)) {
                return correcta.map(v => String(v).toLowerCase()).includes(String(respuesta).toLowerCase());
            }

            return String(correcta).toLowerCase() === String(respuesta).toLowerCase();
        },

        finishEval() {
            clearInterval(this.timer);

            let correct = 0;
            this.questions.forEach((q) => {
                const respuesta = this.answers[q.id];
                if (respuesta !== undefined && this.isCorrectAnswer(q, respuesta)) {
                    correct++;
                }
            });

            this.correctAnswers = correct;
            this.score = this.questions.length > 0 ? Math.round((correct / this.questions.length) * 100) : 0;
            this.passed = this.score >= {{ $evaluacion->umbral_aprobacion }};

            fetch('{{ route('estudiante.evaluaciones.submit', $evaluacion) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    respuestas: this.answers,
                    puntuacion: this.score,
                    tiempo_empleado: ({{ $evaluacion->tiempo_limite_minutos }} * 60) - this.timeLeft
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.intentos_restantes !== undefined) {
                    this.intentosRestantes = data.intentos_restantes;
                }
            })
            .catch(error => {
                console.error('Error al guardar resultados:', error);
            });

            this.evalState = 'results';
        }
    }
}
</script>
@endsection
