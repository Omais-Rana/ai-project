@extends('layouts.app')

@section('title', 'Quiz - ' . $document->original_name)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-teal-50" x-data="quizViewer()">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('documents.show', $document) }}"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">✅ Quiz</h1>
                            <p class="text-sm text-gray-500">{{ $document->original_name }}</p>
                        </div>
                    </div>

                    @if (!$quiz)
                        <div class="flex items-center space-x-3">
                            <select x-model="difficulty" class="px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="mixed">Mixed</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                            <button @click="generateQuiz()" :disabled="loading"
                                class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg font-medium hover:from-green-700 hover:to-teal-700 transition disabled:opacity-50 shadow-lg transform hover:scale-105">
                                <span x-show="!loading">✨ Generate Quiz</span>
                                <span x-show="loading">Generating...</span>
                            </button>
                        </div>
                    @else
                        <button @click="regenerateQuiz()" :disabled="loading"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition font-medium shadow-lg transform hover:scale-105">
                            🔄 Regenerate
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" x-cloak class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-100 to-teal-100 rounded-full mb-6 animate-pulse">
                    <svg class="animate-spin h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Generating Quiz...</h3>
                <p class="text-gray-600">Creating questions from document. This may take 30-50 seconds.</p>
            </div>
        </div>

        <!-- Quiz Content -->
        @if ($quiz)
            <div x-show="!loading && !quizComplete" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Progress -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-green-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">
                            Question <span x-text="currentQuestionIndex + 1"></span> of <span
                                x-text="questions.length"></span>
                        </span>
                        <span class="text-sm text-gray-600">
                            Score: <span class="font-bold text-green-600" x-text="score"></span>/<span
                                x-text="answered"></span>
                        </span>
                    </div>
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 h-full rounded-full transition-all"
                            :style="`width: ${((currentQuestionIndex + 1) / questions.length) * 100}%`"></div>
                    </div>
                </div>

                <!-- Question Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border-2 border-green-200">
                    <div class="mb-6">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold mb-4"
                            :class="{
                                'bg-green-100 text-green-700': currentQuestion.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-700': currentQuestion.difficulty === 'medium',
                                'bg-red-100 text-red-700': currentQuestion.difficulty === 'hard'
                            }"
                            x-text="currentQuestion.difficulty?.toUpperCase()"></span>
                        <h3 class="text-2xl font-bold text-gray-900" x-text="currentQuestion.question"></h3>
                    </div>

                    <!-- Multiple Choice -->
                    <template x-if="currentQuestion.type === 'multiple_choice'">
                        <div class="space-y-3">
                            <template x-for="(option, index) in currentQuestion.options" :key="index">
                                <button @click="selectAnswer(index)" :disabled="answerSubmitted"
                                    class="w-full text-left p-4 rounded-xl border-2 transition"
                                    :class="{
                                        'border-gray-300 hover:border-green-500 hover:bg-green-50': userAnswer !==
                                            index && !answerSubmitted,
                                        'border-green-500 bg-green-50': userAnswer === index && !answerSubmitted,
                                        'border-green-500 bg-green-100': answerSubmitted && index === currentQuestion
                                            .correct_answer,
                                        'border-red-500 bg-red-100': answerSubmitted && userAnswer === index &&
                                            index !== currentQuestion.correct_answer,
                                        'border-gray-200': answerSubmitted && userAnswer !== index && index !==
                                            currentQuestion.correct_answer
                                    }">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-semibold"
                                            :class="{
                                                'bg-gray-200 text-gray-700': userAnswer !== index && !answerSubmitted,
                                                'bg-green-500 text-white': userAnswer === index && !answerSubmitted,
                                                'bg-green-600 text-white': answerSubmitted && index === currentQuestion
                                                    .correct_answer,
                                                'bg-red-600 text-white': answerSubmitted && userAnswer === index &&
                                                    index !== currentQuestion.correct_answer
                                            }"
                                            x-text="String.fromCharCode(65 + index)"></span>
                                        <span class="text-gray-900" x-text="option"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </template>

                    <!-- True/False -->
                    <template x-if="currentQuestion.type === 'true_false'">
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="selectAnswer(true)" :disabled="answerSubmitted"
                                class="p-6 rounded-xl border-2 transition font-semibold text-lg"
                                :class="{
                                    'border-gray-300 hover:border-green-500 hover:bg-green-50': userAnswer !== true && !
                                        answerSubmitted,
                                    'border-green-500 bg-green-50': userAnswer === true && !answerSubmitted,
                                    'border-green-500 bg-green-100': answerSubmitted && currentQuestion
                                        .correct_answer === true,
                                    'border-red-500 bg-red-100': answerSubmitted && userAnswer === true &&
                                        currentQuestion.correct_answer !== true
                                }">
                                ✓ True
                            </button>
                            <button @click="selectAnswer(false)" :disabled="answerSubmitted"
                                class="p-6 rounded-xl border-2 transition font-semibold text-lg"
                                :class="{
                                    'border-gray-300 hover:border-red-500 hover:bg-red-50': userAnswer !== false && !
                                        answerSubmitted,
                                    'border-red-500 bg-red-50': userAnswer === false && !answerSubmitted,
                                    'border-green-500 bg-green-100': answerSubmitted && currentQuestion
                                        .correct_answer === false,
                                    'border-red-500 bg-red-100': answerSubmitted && userAnswer === false &&
                                        currentQuestion.correct_answer !== false
                                }">
                                ✗ False
                            </button>
                        </div>
                    </template>

                    <!-- Short Answer -->
                    <template x-if="currentQuestion.type === 'short_answer'">
                        <div>
                            <textarea x-model="userAnswer" :disabled="answerSubmitted"
                                class="w-full p-4 border-2 border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200 transition"
                                rows="3" placeholder="Type your answer here..."></textarea>
                        </div>
                    </template>

                    <!-- Explanation (after submit) -->
                    <div x-show="answerSubmitted" x-cloak class="mt-6 p-4 rounded-xl"
                        :class="isCorrect ? 'bg-green-50 border-2 border-green-200' : 'bg-red-50 border-2 border-red-200'">
                        <div class="flex items-start space-x-3">
                            <div class="text-2xl" x-text="isCorrect ? '✅' : '❌'"></div>
                            <div>
                                <p class="font-semibold mb-2" :class="isCorrect ? 'text-green-900' : 'text-red-900'"
                                    x-text="isCorrect ? 'Correct!' : 'Incorrect'"></p>
                                <p class="text-gray-700" x-text="currentQuestion.explanation"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <button @click="previousQuestion()" :disabled="currentQuestionIndex === 0"
                        class="px-6 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition disabled:opacity-50 font-medium">
                        ← Previous
                    </button>

                    <button @click="submitAnswer()" x-show="!answerSubmitted" :disabled="userAnswer === null"
                        class="px-8 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition disabled:opacity-50 font-medium shadow-lg">
                        Submit Answer
                    </button>

                    <button @click="nextQuestion()" x-show="answerSubmitted"
                        class="px-8 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition font-medium shadow-lg">
                        <span x-show="currentQuestionIndex < questions.length - 1">Next Question →</span>
                        <span x-show="currentQuestionIndex === questions.length - 1">Finish Quiz</span>
                    </button>
                </div>
            </div>

            <!-- Quiz Complete -->
            <div x-show="quizComplete" x-cloak class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center border-2"
                    :class="score / questions.length >= 0.7 ? 'border-green-300' : 'border-yellow-300'">
                    <div class="text-6xl mb-6" x-text="score / questions.length >= 0.7 ? '🎉' : '📚'"></div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Quiz Complete!</h2>
                    <div class="text-5xl font-bold mb-6"
                        :class="score / questions.length >= 0.7 ? 'text-green-600' : 'text-yellow-600'">
                        <span x-text="score"></span>/<span x-text="questions.length"></span>
                    </div>
                    <p class="text-xl text-gray-600 mb-8">
                        <span x-text="Math.round((score / questions.length) * 100)"></span>% Correct
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button @click="resetQuiz()"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition font-medium">
                            Retake Quiz
                        </button>
                        <a href="{{ route('documents.show', $document) }}"
                            class="px-6 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                            Back to Document
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function quizViewer() {
            return {
                loading: false,
                questions: @json($quiz->questions ?? []),
                currentQuestionIndex: 0,
                currentQuestion: {},
                userAnswer: null,
                answerSubmitted: false,
                isCorrect: false,
                score: 0,
                answered: 0,
                quizComplete: false,
                difficulty: 'mixed',

                init() {
                    if (this.questions.length > 0) {
                        this.currentQuestion = this.questions[0];
                    }
                },

                selectAnswer(answer) {
                    if (!this.answerSubmitted) {
                        this.userAnswer = answer;
                    }
                },

                async submitAnswer() {
                    if (this.userAnswer === null) return;

                    this.answerSubmitted = true;
                    this.answered++;

                    const question = this.currentQuestion;
                    let correct = false;

                    if (question.type === 'multiple_choice') {
                        correct = this.userAnswer === question.correct_answer;
                    } else if (question.type === 'true_false') {
                        correct = this.userAnswer === question.correct_answer;
                    } else if (question.type === 'short_answer') {
                        const userAnswerLower = String(this.userAnswer).toLowerCase().trim();
                        const correctAnswerLower = String(question.correct_answer).toLowerCase().trim();
                        correct = userAnswerLower === correctAnswerLower ||
                            userAnswerLower.includes(correctAnswerLower) ||
                            correctAnswerLower.includes(userAnswerLower);
                    }

                    this.isCorrect = correct;
                    if (correct) this.score++;
                },

                nextQuestion() {
                    if (this.currentQuestionIndex < this.questions.length - 1) {
                        this.currentQuestionIndex++;
                        this.currentQuestion = this.questions[this.currentQuestionIndex];
                        this.userAnswer = null;
                        this.answerSubmitted = false;
                        this.isCorrect = false;
                    } else {
                        this.quizComplete = true;
                    }
                },

                previousQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex--;
                        this.currentQuestion = this.questions[this.currentQuestionIndex];
                        this.userAnswer = null;
                        this.answerSubmitted = false;
                        this.isCorrect = false;
                    }
                },

                resetQuiz() {
                    this.currentQuestionIndex = 0;
                    this.currentQuestion = this.questions[0];
                    this.userAnswer = null;
                    this.answerSubmitted = false;
                    this.isCorrect = false;
                    this.score = 0;
                    this.answered = 0;
                    this.quizComplete = false;
                },

                async generateQuiz() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('study-materials.quiz.generate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                difficulty: this.difficulty,
                                count: 10
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.error || 'Failed to generate quiz');
                            this.loading = false;
                        }
                    } catch (err) {
                        alert('Network error. Please try again.');
                        this.loading = false;
                    }
                },

                async regenerateQuiz() {
                    if (!confirm('Regenerate quiz? This will create new questions.')) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('study-materials.quiz.regenerate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                difficulty: this.difficulty,
                                count: 10
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.error || 'Failed to regenerate');
                            this.loading = false;
                        }
                    } catch (err) {
                        alert('Network error');
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
