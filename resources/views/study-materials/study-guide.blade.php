@extends('layouts.app')

@section('title', 'Study Guide - ' . $document->original_name)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50" x-data="studyGuideViewer()">
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
                            <h1 class="text-2xl font-bold text-gray-900">📖 Study Guide</h1>
                            <p class="text-sm text-gray-500">{{ $document->original_name }}</p>
                        </div>
                    </div>

                    @if (!$studyGuide)
                        <button @click="generateStudyGuide()" :disabled="loading"
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:scale-105">
                            <span x-show="!loading">✨ Generate Study Guide</span>
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
                        <div class="flex items-center space-x-3">
                            <button @click="exportMarkdown()"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium shadow-sm hover:shadow">
                                📥 Export MD
                            </button>
                            <button @click="regenerateStudyGuide()" :disabled="loading"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 font-medium shadow-lg transform hover:scale-105">
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
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full mb-6 animate-pulse">
                    <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Analyzing Document...</h3>
                <p class="text-gray-600">AI is reading and structuring the content. This may take 30-60 seconds.</p>
                <div
                    class="mt-6 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-full h-2 w-64 mx-auto overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-full animate-pulse"></div>
                </div>
            </div>
        </div>

        <!-- Study Guide Content -->
        @if ($studyGuide)
            <div x-show="!loading" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

                <!-- Learning Objectives -->
                @if (count($studyGuide->getLearningObjectives()) > 0)
                    <div
                        class="bg-white rounded-2xl shadow-lg p-8 border border-indigo-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center space-x-3 mb-6">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                🎯
                            </div>
                            <h2
                                class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Learning Objectives</h2>
                        </div>
                        <ul class="space-y-3">
                            @foreach ($studyGuide->getLearningObjectives() as $objective)
                                <li
                                    class="flex items-start space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 transition">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-semibold mt-0.5">✓</span>
                                    <span class="text-lg">{{ $objective }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Main Sections -->
                @if (count($studyGuide->getSections()) > 0)
                    <div
                        class="bg-white rounded-2xl shadow-lg p-8 border border-purple-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center space-x-3 mb-6">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                📚
                            </div>
                            <h2
                                class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                                Main Content</h2>
                        </div>

                        <div class="space-y-8">
                            @foreach ($studyGuide->getSections() as $index => $section)
                                <div class="border-l-4 border-indigo-500 pl-6 py-2 hover:border-purple-600 transition">
                                    <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center space-x-2">
                                        <span class="text-indigo-600">{{ $index + 1 }}.</span>
                                        <span>{{ $section['title'] }}</span>
                                    </h3>
                                    <p class="text-gray-700 leading-relaxed mb-4 text-lg">{{ $section['content'] }}</p>

                                    @if (isset($section['key_points']) && count($section['key_points']) > 0)
                                        <div
                                            class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-5 mt-4 border border-indigo-200">
                                            <p class="font-semibold text-indigo-900 mb-3 flex items-center space-x-2">
                                                <span>🔑</span>
                                                <span>Key Points:</span>
                                            </p>
                                            <ul class="space-y-2">
                                                @foreach ($section['key_points'] as $point)
                                                    <li class="flex items-start space-x-3 text-gray-700">
                                                        <span class="text-indigo-600 font-bold text-lg">•</span>
                                                        <span>{{ $point }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Key Concepts -->
                @if (count($studyGuide->getKeyConcepts()) > 0)
                    <div
                        class="bg-white rounded-2xl shadow-lg p-8 border border-blue-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center space-x-3 mb-6">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                💡
                            </div>
                            <h2
                                class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                                Key Concepts</h2>
                        </div>

                        <div class="grid gap-6">
                            @foreach ($studyGuide->getKeyConcepts() as $concept)
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border border-blue-200 hover:shadow-lg transition-all transform hover:scale-[1.02]">
                                    <h4 class="text-lg font-bold text-blue-900 mb-2 flex items-center space-x-2">
                                        <span class="text-blue-600">●</span>
                                        <span>{{ $concept['term'] }}</span>
                                    </h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">{{ $concept['definition'] }}</p>
                                    @if (isset($concept['importance']))
                                        <p class="text-sm text-blue-700 italic bg-white/50 rounded-lg p-3">
                                            <span class="font-semibold">💭 Why it matters:</span>
                                            {{ $concept['importance'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Important Terms -->
                @if (count($studyGuide->getImportantTerms()) > 0)
                    <div
                        class="bg-white rounded-2xl shadow-lg p-8 border border-green-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center space-x-3 mb-6">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                📝
                            </div>
                            <h2
                                class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                                Important Terms</h2>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach ($studyGuide->getImportantTerms() as $term)
                                <div
                                    class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition transform hover:scale-[1.02]">
                                    <dt class="font-bold text-green-900 mb-1">{{ $term['term'] }}</dt>
                                    <dd class="text-gray-700">{{ $term['definition'] }}</dd>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        @endif

        <!-- Error State -->
        <div x-show="error" x-cloak class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-8 text-center shadow-xl">
                <div class="text-red-600 text-5xl mb-4 animate-bounce">⚠️</div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Generation Failed</h3>
                <p class="text-red-700 mb-4" x-text="errorMessage"></p>
                <button @click="location.reload()"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium shadow-lg">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function studyGuideViewer() {
            return {
                loading: false,
                error: false,
                errorMessage: '',

                async generateStudyGuide() {
                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch(
                        '{{ route('study-materials.study-guide.generate', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            this.error = true;
                            this.errorMessage = data.error || 'Failed to generate study guide';
                            this.loading = false;
                        }
                    } catch (err) {
                        this.error = true;
                        this.errorMessage = 'Network error. Please try again.';
                        this.loading = false;
                    }
                },

                async regenerateStudyGuide() {
                    if (!confirm('Regenerate study guide? This will replace the existing one.')) return;

                    this.loading = true;
                    this.error = false;

                    try {
                        const response = await fetch(
                            '{{ route('study-materials.study-guide.regenerate', $document) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
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
                },

                exportMarkdown() {
                    const markdown = @json($studyGuide->raw_markdown ?? '');
                    const blob = new Blob([markdown], {
                        type: 'text/markdown'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = '{{ str_replace(' ', '_', $document->original_name) }}_study_guide.md';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            }
        }
    </script>
@endsection
