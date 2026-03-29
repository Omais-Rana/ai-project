<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentQuestion;
use App\Models\DocumentChunk;
use App\Services\DocumentProcessorService;
use App\Services\VectorSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentProcessorService $processorService,
        protected VectorSearchService $vectorSearchService
    ) {}

    public function index(Request $request)
    {
        // Show all documents (no authentication required)
        $documents = Document::latest()->get();

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Store file using Storage facade
        try {
            $path = $file->storeAs('documents', $filename, 'local');

            // Verify file was actually saved
            $fullPath = storage_path('app/' . $path);
            if (!file_exists($fullPath)) {
                throw new \Exception("File upload failed - file not found at: {$fullPath}");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload file: ' . $e->getMessage());
        }

        // Create document record (no user_id needed)
        $document = Document::create([
            'user_id' => null, // No authentication
            'filename' => $filename,
            'original_name' => $originalName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'status' => 'uploading',
        ]);

        // Process document
        try {
            $this->processorService->processDocument($document);

            // Only generate embeddings after processing succeeds
            if ($document->fresh()->status === 'completed') {
                $this->vectorSearchService->generateEmbeddingsForDocument($document);
            }

            return back()->with('success', 'Document uploaded and processed successfully!');
        } catch (\Exception $e) {
            // Update document with error
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to process document: ' . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }

    public function destroy(Document $document)
    {
        // Manually delete associated questions (in case cascade isn't working)
        DocumentQuestion::where('document_id', $document->id)->delete();
        
        // Delete chunks (should cascade automatically)
        DocumentChunk::where('document_id', $document->id)->delete();
        
        // Delete file from storage
        Storage::delete($document->file_path);

        // Delete document
        $document->delete();

        return back()->with('success', 'Document and all associated data deleted successfully!');
    }

    public function chat(Document $document)
    {
        return view('documents.chat', compact('document'));
    }
}
