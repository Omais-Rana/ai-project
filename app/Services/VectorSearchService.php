<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;

class VectorSearchService
{
    protected EmbeddingService $embeddingService;
    protected int $topK;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->topK = (int) config('ai.top_k_chunks', 5);
    }

    /**
     * Search for most relevant chunks in a document
     */
    public function searchDocument(Document $document, string $query, ?int $topK = null): array
    {
        $topK = $topK ?? $this->topK;

        // Generate query embedding
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Get all chunks with embeddings for this document
        $chunks = DocumentChunk::where('document_id', $document->id)
            ->whereNotNull('embedding')
            ->get();

        if ($chunks->isEmpty()) {
            return [];
        }

        // Calculate similarities
        $similarities = [];
        foreach ($chunks as $chunk) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $chunk->embedding
            );

            $similarities[] = [
                'chunk' => $chunk,
                'similarity' => $similarity,
            ];
        }

        // Sort by similarity (descending) and take top-K
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $topResults = array_slice($similarities, 0, $topK);

        return $topResults;
    }

    /**
     * Search across all documents (no user filtering)
     */
    public function searchAllDocuments(?int $userId, string $query, ?int $topK = null): array
    {
        $topK = $topK ?? $this->topK;

        // Generate query embedding
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Get all chunks with embeddings (no user filtering)
        $chunks = DocumentChunk::whereNotNull('embedding')
            ->with('document')
            ->get();

        if ($chunks->isEmpty()) {
            return [];
        }

        // Calculate similarities
        $similarities = [];
        foreach ($chunks as $chunk) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $chunk->embedding
            );

            $similarities[] = [
                'chunk' => $chunk,
                'similarity' => $similarity,
            ];
        }

        // Sort by similarity (descending) and take top-K
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $topResults = array_slice($similarities, 0, $topK);

        return $topResults;
    }

    /**
     * Ensure all chunks in a document have embeddings
     */
    public function generateEmbeddingsForDocument(Document $document): void
    {
        $chunks = DocumentChunk::where('document_id', $document->id)
            ->whereNull('embedding')
            ->get();

        foreach ($chunks as $chunk) {
            $embedding = $this->embeddingService->generateEmbedding($chunk->chunk_text);
            $chunk->update(['embedding' => $embedding]);
        }
    }
}
