@extends('layouts.app')

@section('title', 'Flashcards - ' . $document->original_name)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-orange-50" x-data="flashcardsViewer()">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
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
                            <h1 class="text-2xl font-bold text-gray-900">🃏 Flashcards</h1>
                            <p class="text-sm text-gray-500">{{ $document->original_name }}</p>
                        </div>
                    </div>

                    @if ($flashcards->isEmpty())
                        <button @click="generateFlashcards()" :disabled="loading"
                            class="px-6 py-2.5 bg-gradient-to-r from-pink-600 to-orange-600 text-white rounded-lg font-medium hover:from-pink-700 hover:to-orange-700 transition disabled:opacity-50 shadow-lg transform hover:scale-105">
                            <span x-show="!loading">✨ Generate Flashcards</span>
                            <span x-show="loading" class="flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Generating...</span>
                            </span>
                        </button>
                    @else
                        <div class="flex items-center space-x-3 flex-wrap">
                            <!-- Difficulty Filter -->
                            <select x-model="difficultyFilter" @change="filterCards()"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium">
                                <option value="all">All Difficulties</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>

                            <!-- Export Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open"
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium flex items-center space-x-2">
                                    <span>📥 Export</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1">
                                    <a href="{{ route('study-materials.flashcards.export.anki', $document) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Anki CSV
                                    </a>
                                    <a href="{{ route('study-materials.flashcards.export.json', $document) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        JSON
                                    </a>
                                </div>
                            </div>

                            <button @click="regenerateFlashcards()" :disabled="loading"
                                class="px-4 py-2 bg-gradient-to-r from-pink-600 to-orange-600 text-white rounded-lg hover:from-pink-700 hover:to-orange-700 transition font-medium shadow-lg transform hover:scale-105">
                                🔄 Regenerate
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" x-cloak class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-pink-100 to-orange-100 rounded-full mb-6 animate-pulse">
                    <svg class="animate-spin h-10 w-10 text-pink-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Creating Flashcards...</h3>
                <p class="text-gray-600">Extracting key concepts and definitions. This may take 20-40 seconds.</p>
            </div>
        </div>

        <!-- Flashcards Display -->
        @if ($flashcards->isNotEmpty())
            <div x-show="!loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Progress Bar -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-pink-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">
                            Card <span x-text="currentIndex + 1"></span> of <span x-text="filteredCards.length"></span>
                        </span>
                        <span class="text-sm text-gray-500" x-text="currentCard.difficulty"
                            :class="{
                                'text-green-600 font-semibold': currentCard.difficulty === 'easy',
                                'text-yellow-600 font-semibold': currentCard.difficulty === 'medium',
                                'text-red-600 font-semibold': currentCard.difficulty === 'hard'
                            }"></span>
                    </div>
                    <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-pink-500 to-orange-500 h-full transition-all duration-300"
                            :style="`width: ${((currentIndex + 1) / filteredCards.length) * 100}%`"></div>
                    </div>
                </div>

                <!-- Flashcard -->
                <div class="perspective-1000 mb-8">
                    <div @click="flipCard()" class="flashcard-container cursor-pointer" :class="{ 'flipped': isFlipped }">
                        <!-- Front -->
                        <div
                            class="flashcard flashcard-front bg-gradient-to-br from-white to-pink-50 border-2 border-pink-200">
                            <div class="flex flex-col items-center justify-center h-full p-12 text-center">
                                <div class="text-pink-600 mb-4 text-4xl">❓</div>
                                <p class="text-2xl font-bold text-gray-900 leading-relaxed" x-text="currentCard.front"></p>
                                <p class="text-sm text-gray-500 mt-8 animate-pulse">Click to reveal answer</p>
                            </div>
                        </div>

                        <!-- Back -->
                        <div
                            class="flashcard flashcard-back bg-gradient-to-br from-white to-orange-50 border-2 border-orange-200">
                            <div class="flex flex-col items-center justify-center h-full p-12 text-center">
                                <div class="text-orange-600 mb-4 text-4xl">💡</div>
                                <p class="text-xl text-gray-800 leading-relaxed" x-text="currentCard.back"></p>
                                <p class="text-sm text-gray-500 mt-8 animate-pulse">Click to see question</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <button @click="previousCard()" :disabled="currentIndex === 0"
                        class="p-4 bg-white rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200 hover:border-pink-300 transform hover:scale-110">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button @click="shuffleCards()"
                        class="px-6 py-3 bg-gradient-to-r from-pink-600 to-orange-600 text-white rounded-full shadow-lg hover:shadow-xl transition font-medium transform hover:scale-105">
                        🔀 Shuffle
                    </button>

                    <button @click="nextCard()" :disabled="currentIndex === filteredCards.length - 1"
                        class="p-4 bg-white rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200 hover:border-pink-300 transform hover:scale-110">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Keyboard Shortcuts -->
                <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Keyboard shortcuts:</span>
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">←</kbd> Previous •
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">→</kbd> Next •
                        <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Space</kbd> Flip
                    </p>
                </div>
            </div>
        @endif

        <!-- Error State -->
        <div x-show="error" x-cloak class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-8 text-center shadow-xl">
                <div class="text-red-600 text-5xl mb-4 animate-bounce">⚠️</div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Generation Failed</h3>
                <p class="text-red-700 mb-4" x-text="errorMessage"></p>
                <button @click="location.reload()"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .perspective-1000 {
            perspective: 1000px;
        }

        .flashcard-container {
            position: relative;
            width: 100%;
            height: 400px;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .flashcard-container.flipped {
            transform: rotateY(180deg);
        }

        .flashcard {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .flashcard:hover {
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15);
        }

        .flashcard-back {
            transform: rotateY(180deg);
        }

        kbd {
            font-family: monospace;
            border: 1px solid #ccc;
        }
    </style>

    <script>
        function flashcardsViewer() {
            return {
                loading: false,
                error: false,
                errorMessage: '',
                allCards: @json($flashcards->toArray()),
                filteredCards: [],
                currentIndex: 0,
                currentCard: {},
                isFlipped: false,
                difficultyFilter: 'all',

                init() {
                    this.filteredCards = this.allCards;
                    this.currentCard = this.filteredCards[0] || {};

                    // Keyboard navigation
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowLeft') this.previousCard();
                        if (e.key === 'ArrowRight') this.nextCard();
                        if (e.key === ' ') {
                            e.preventDefault();
                            this.flipCard();
                        }
                    });
                },

                flipCard() {
                    this.isFlipped = !this.isFlipped;
                },

                nextCard() {
                    if (this.currentIndex < this.filteredCards.length - 1) {
                        this.currentIndex++;
                        this.currentCard = this.filteredCards[this.currentIndex];
                        this.isFlipped = false;
                    }
                },

                previousCard() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.currentCard = this.filteredCards[this.currentIndex];
                        this.isFlipped = false;
                    }
                },

                filterCards() {
                    if (this.difficultyFilter === 'all') {
                        this.filteredCards = this.allCards;
                    } else {
                        this.filteredCards = this.allCards.filter(card => card.difficulty === this.difficultyFilter);
                    }
                    this.currentIndex = 0;
                    this.currentCard = this.filteredCards[0] || {};
                    this.isFlipped = false;
                },

                shuffleCards() {
                    this.filteredCards = [...this.filteredCards].sort(() => Math.random() - 0.5);
                    this.currentIndex = 0;
                    this.currentCard = this.filteredCards[0];
                    this.isFlipped = false;
                },

                async generateFlashcards() {
                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch('{{ route('study-materials.flashcards.generate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                count: 20
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            this.error = true;
                            this.errorMessage = data.error || 'Failed to generate flashcards';
                            this.loading = false;
                        }
                    } catch (err) {
                        this.error = true;
                        this.errorMessage = 'Network error. Please try again.';
                        this.loading = false;
                    }
                },

                async regenerateFlashcards() {
                    if (!confirm('Regenerate flashcards? This will replace the existing ones.')) return;

                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch(
                        '{{ route('study-materials.flashcards.regenerate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                count: 20
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            this.error = true;
                            this.errorMessage = data.error || 'Failed to regenerate';
                            this.loading = false;
                        }
                    } catch (err) {
                        this.error = true;
                        this.errorMessage = 'Network error. Please try again.';
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
