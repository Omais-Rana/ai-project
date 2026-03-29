@extends('layouts.app')

@section('title', $document->original_name)

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('documents.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Documents
            </a>
        </div>

        <!-- Document Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $document->original_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $document->getFileSizeFormatted() }} • {{ $document->total_chunks }} chunks
                    </p>
                </div>
                <div class="text-right">
                    @if ($document->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            ✓ Ready
                        </span>
                    @elseif ($document->status === 'processing')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            ⏳ Processing
                        </span>
                    @elseif ($document->status === 'failed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            ✗ Failed
                        </span>
                    @endif
                </div>
            </div>

            @if ($document->status === 'failed' && $document->error_message)
                <div class="mt-4 bg-red-50 border border-red-200 rounded p-4">
                    <p class="text-sm text-red-800">
                        <strong>Error:</strong> {{ $document->error_message }}
                    </p>
                </div>
            @endif

            <div class="mt-6 flex space-x-2">
                @if ($document->isProcessed())
                    <a href="{{ route('documents.chat', $document) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Ask Questions
                    </a>
                @endif
                <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
