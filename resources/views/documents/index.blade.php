@extends('layouts.app')

@section('title', 'My Documents')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-3xl font-semibold text-gray-900">My Documents</h1>
                <p class="mt-2 text-sm text-gray-700">Upload your homework documents and ask questions about them.</p>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Upload New Document</h2>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose File</label>
                    <input type="file" name="file" accept=".pdf,.docx,.doc,.txt" required
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p class="mt-1 text-sm text-gray-500">PDF, DOCX, or TXT (Max 10MB)</p>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Document
                </button>
            </form>
        </div>

        <!-- Documents List -->
        <div class="mt-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Your Documents</h2>

            @if ($documents->isEmpty())
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by uploading a document above.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($documents as $document)
                        <div
                            class="relative bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div class="absolute top-4 right-4">
                                @if ($document->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Ready
                                    </span>
                                @elseif ($document->status === 'processing')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Processing
                                    </span>
                                @elseif ($document->status === 'failed')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ✗ Failed
                                    </span>
                                @endif
                            </div>

                            <div class="pr-16">
                                <div class="flex items-center">
                                    <svg class="h-10 w-10 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-sm font-medium text-gray-900 truncate"
                                    title="{{ $document->original_name }}">
                                    {{ $document->original_name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $document->getFileSizeFormatted() }} • {{ $document->total_chunks }} chunks
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $document->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="mt-4 flex space-x-2">
                                @if ($document->isProcessed())
                                    <a href="{{ route('documents.chat', $document) }}"
                                        class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Ask Questions
                                    </a>
                                @endif
                                <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline"
                                    onsubmit="localStorage.removeItem('chat_{{ $document->id }}'); return confirm('Delete this document?');")>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
