@extends('layouts.app')

@section('title', $document->original_name)

@section('content')
    <div x-data="{
        loading: false,
        startGenerating(url) {
            this.loading = true;
            // Let the browser navigate after setting loading state
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        }
    }">
        <!-- Back Button with Gradient -->
        <div class="px-4 sm:px-6 lg:px-8 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
            <a href="{{ route('documents.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Documents
            </a>
        </div>

        <div class="px-4 sm:px-6 lg:px-8 py-8 overflow-visible">
            <!-- Document Header Card -->
            <div class="max-w-5xl mx-auto mb-8 overflow-visible">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-visible">
                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-6">
                        <div class="flex items-start justify-between flex-wrap gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h1 class="text-2xl font-bold text-white break-words">{{ $document->original_name }}
                                        </h1>
                                        <p class="text-white/80 text-sm mt-1">
                                            {{ $document->getFileSizeFormatted() }} • {{ $document->total_chunks }} chunks •
                                            {{ $document->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if ($document->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white text-green-600 shadow-lg">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Ready
                                    </span>
                                @elseif ($document->status === 'processing')
                                    <span
                                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white text-yellow-600 shadow-lg">
                                        <svg class="animate-spin w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Processing
                                    </span>
                                @elseif ($document->status === 'failed')
                                    <span
                                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white text-red-600 shadow-lg">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Failed
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    @if ($document->status === 'failed' && $document->error_message)
                        <div class="p-6 bg-red-50 border-t border-red-100">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800">Error Processing Document</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $document->error_message }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="p-6 bg-gray-50 border-t overflow-visible">
                        <div class="flex flex-wrap gap-3 overflow-visible">
                            @if ($document->isProcessed())
                                <!-- Ask Questions Button -->
                                <a href="{{ route('documents.chat', $document) }}"
                                    class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Ask Questions
                                </a>

                                <!-- Study Materials Dropdown -->
                                <div x-data="{
                                    open: false,
                                    toggleDropdown() {
                                        this.open = !this.open;
                                    }
                                }" class="relative" @click.away="open = false">
                                    <button @click="toggleDropdown()"
                                        class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-xl text-indigo-600 bg-white border-2 border-indigo-600 hover:bg-indigo-50 shadow-md hover:shadow-lg transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Study Materials
                                        <svg class="ml-2 h-4 w-4 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu with better positioning -->
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95"
                                        class="absolute top-full left-0 mt-2 w-80 rounded-2xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 border border-gray-100 overflow-hidden"
                                        style="z-index: 999999;">

                                        <!-- Dropdown Content -->
                                        <div class="py-3">
                                            <div
                                                class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                                Choose Study Material
                                            </div>

                                            <a href="#"
                                                class="flex items-center px-4 py-4 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-pink-50 hover:to-orange-50 hover:text-pink-700 transition-all duration-200 group"
                                                @click.prevent="startGenerating('{{ route('study-materials.flashcards.view', $document) }}')">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-pink-100 to-orange-100 mr-4 group-hover:from-pink-200 group-hover:to-orange-200 transition-all duration-200">
                                                    <span class="text-xl">🃏</span>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 group-hover:text-pink-900">
                                                        Flashcards</div>
                                                    <div class="text-xs text-gray-500 mt-1">Interactive learning cards with
                                                        spaced repetition</div>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 group-hover:text-pink-500 transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>

                                            <a href="#"
                                                class="flex items-center px-4 py-4 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-teal-50 hover:text-green-700 transition-all duration-200 group"
                                                @click.prevent="startGenerating('{{ route('study-materials.quiz.view', $document) }}')">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-green-100 to-teal-100 mr-4 group-hover:from-green-200 group-hover:to-teal-200 transition-all duration-200">
                                                    <span class="text-xl">✅</span>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 group-hover:text-green-900">
                                                        Quiz
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">Test your knowledge with
                                                        interactive questions</div>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-500 transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>

                                            <a href="#"
                                                class="flex items-center px-4 py-4 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-700 transition-all duration-200 group"
                                                @click.prevent="startGenerating('{{ route('study-materials.summary.view', $document) }}')">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-blue-100 to-indigo-100 mr-4 group-hover:from-blue-200 group-hover:to-indigo-200 transition-all duration-200">
                                                    <span class="text-xl">📝</span>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 group-hover:text-blue-900">
                                                        Summary
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">Quick overview of key points
                                                        and
                                                        takeaways</div>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Loading overlay -->
                                    <!-- Removed intrusive full-screen loader -->
                                </div>
                            @endif

                            <!-- Delete Button -->
                            <form action="{{ route('documents.destroy', $document) }}" method="POST" class="ml-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this document?');"
                                    class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-xl text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 hover:border-red-300 transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div x-show="loading" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-sm mx-4 text-center">
                    <div class="mb-4">
                        <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Generating Study Materials</h3>
                    <p class="text-sm text-gray-600">Please wait while we analyze your document and create the study
                        materials...</p>
                </div>
            </div>
        </div>
    </div>
@endsection
