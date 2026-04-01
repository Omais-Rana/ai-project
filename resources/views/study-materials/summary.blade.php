@extends('layouts.app')

@section('title', 'Summary - ' . $document->original_name)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50" x-data="summaryViewer()">
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
                            <h1 class="text-2xl font-bold text-gray-900">📝 Summary</h1>
                            <p class="text-sm text-gray-500">{{ $document->original_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Mode Selector -->
                        <div class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1">
                            <button @click="switchMode('tldr')" class="px-4 py-2 rounded-md font-medium text-sm transition"
                                :class="currentMode === 'tldr' ? 'bg-white shadow-sm text-blue-600' :
                                    'text-gray-600 hover:text-gray-900'">
                                TL;DR
                            </button>
                            <button @click="switchMode('brief')" class="px-4 py-2 rounded-md font-medium text-sm transition"
                                :class="currentMode === 'brief' ? 'bg-white shadow-sm text-blue-600' :
                                    'text-gray-600 hover:text-gray-900'">
                                Brief
                            </button>
                            <button @click="switchMode('detailed')"
                                class="px-4 py-2 rounded-md font-medium text-sm transition"
                                :class="currentMode === 'detailed' ? 'bg-white shadow-sm text-blue-600' :
                                    'text-gray-600 hover:text-gray-900'">
                                Detailed
                            </button>
                        </div>

                        <!-- Regenerate Button -->
                        <template x-if="summaries[currentMode]">
                            <button @click="regenerateSummary()" :disabled="loading"
                                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition font-medium shadow-lg transform hover:scale-105 disabled:opacity-50">
                                🔄 Regenerate
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" x-cloak class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full mb-6 animate-pulse">
                    <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Generating Summary...</h3>
                <p class="text-gray-600" x-text="getModeDescription()"></p>
            </div>
        </div>

        <!-- Summary Content -->
        <div x-show="!loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Mode Info Banner -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 mb-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">
                            <span x-show="currentMode === 'tldr'">⚡ TL;DR Summary</span>
                            <span x-show="currentMode === 'brief'">📄 Brief Summary</span>
                            <span x-show="currentMode === 'detailed'">📚 Detailed Summary</span>
                        </h2>
                        <p class="text-blue-100" x-text="getModeDescription()"></p>
                    </div>
                    <div class="text-right">
                        <template x-if="summaries[currentMode]">
                            <div class="text-3xl font-bold" x-text="summaries[currentMode].word_count"></div>
                        </template>
                        <div class="text-sm text-blue-100">words</div>
                    </div>
                </div>
            </div>

            <!-- Summary Display -->
            <template x-if="summaries[currentMode]">
                <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-blue-100">
                    <div class="prose prose-lg max-w-none">
                        <p class="text-gray-800 leading-relaxed text-lg whitespace-pre-line"
                            x-text="summaries[currentMode].content"></p>
                    </div>

                    <!-- Meta Info -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
                        <div>
                            Generated: <span x-text="formatDate(summaries[currentMode].created_at)"></span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span x-text="summaries[currentMode].word_count + ' words'"></span>
                            <span>•</span>
                            <span x-text="Math.ceil(summaries[currentMode].word_count / 200) + ' min read'"></span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Generate Button (if summary doesn't exist) -->
            <template x-if="!summaries[currentMode] && !loading">
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center border-2 border-dashed border-gray-300">
                    <div class="text-6xl mb-6">📝</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        No <span x-text="currentMode.toUpperCase()"></span> Summary Yet
                    </h3>
                    <p class="text-gray-600 mb-6" x-text="getModeDescription()"></p>
                    <button @click="generateSummary()" :disabled="loading"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition font-medium shadow-lg transform hover:scale-105 disabled:opacity-50">
                        ✨ Generate <span x-text="currentMode.toUpperCase()"></span> Summary
                    </button>
                </div>
            </template>

            <!-- Quick Navigation (if summaries exist) -->
            <template x-if="hasSomeSummaries()">
                <div class="mt-6 bg-blue-50 rounded-xl p-6 border border-blue-200">
                    <h4 class="font-semibold text-gray-900 mb-3">💡 Quick Tip</h4>
                    <p class="text-gray-700 text-sm">
                        Switch between summary modes using the buttons above. Each mode offers a different level of detail:
                    </p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        <li class="flex items-center space-x-2">
                            <span class="text-blue-600">⚡</span>
                            <span><strong>TL;DR:</strong> 2-3 sentences for quick understanding</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-blue-600">📄</span>
                            <span><strong>Brief:</strong> 1-2 paragraphs covering main points</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-blue-600">📚</span>
                            <span><strong>Detailed:</strong> 3-5 paragraphs with comprehensive overview</span>
                        </li>
                    </ul>
                </div>
            </template>
        </div>

        <!-- Error State -->
        <div x-show="error" x-cloak class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-8 text-center shadow-xl">
                <div class="text-red-600 text-5xl mb-4 animate-bounce">⚠️</div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Generation Failed</h3>
                <p class="text-red-700 mb-4" x-text="errorMessage"></p>
                <button @click="error = false"
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

        .prose p {
            margin-bottom: 1.25em;
        }
    </style>

    <script>
        function summaryViewer() {
            return {
                loading: false,
                error: false,
                errorMessage: '',
                currentMode: '{{ $summary->length_mode ?? 'brief' }}',
                summaries: {
                    tldr: @json($document->summaries->where('length_mode', 'tldr')->first()),
                    brief: @json($document->summaries->where('length_mode', 'brief')->first()),
                    detailed: @json($document->summaries->where('length_mode', 'detailed')->first())
                },

                getModeDescription() {
                    const descriptions = {
                        tldr: 'Ultra-concise 2-3 sentence overview',
                        brief: '1-2 paragraphs covering main topics (100-200 words)',
                        detailed: 'Comprehensive 3-5 paragraph summary (300-500 words)'
                    };
                    return descriptions[this.currentMode];
                },

                switchMode(mode) {
                    this.currentMode = mode;
                    this.error = false;
                },

                hasSomeSummaries() {
                    return this.summaries.tldr || this.summaries.brief || this.summaries.detailed;
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                async generateSummary() {
                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch('{{ route('study-materials.summary.generate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                mode: this.currentMode
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Reload with the current mode to preserve the view
                            window.location.href = `{{ route('study-materials.summary.view', $document) }}?mode=${this.currentMode}`;
                        } else {
                            this.error = true;
                            this.errorMessage = data.error || 'Failed to generate summary';
                            this.loading = false;
                        }
                    } catch (err) {
                        this.error = true;
                        this.errorMessage = 'Network error. Please try again.';
                        this.loading = false;
                    }
                },

                async regenerateSummary() {
                    if (!confirm(
                            `Regenerate ${this.currentMode.toUpperCase()} summary? This will replace the existing one.`
                            )) return;

                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch('{{ route('study-materials.summary.regenerate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                mode: this.currentMode
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
