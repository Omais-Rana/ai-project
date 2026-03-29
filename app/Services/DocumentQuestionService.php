<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentQuestion;
use Illuminate\Support\Str;

class DocumentQuestionService
{
    protected VectorSearchService $vectorSearchService;
    protected string $model;

    public function __construct(VectorSearchService $vectorSearchService)
    {
        $this->vectorSearchService = $vectorSearchService;
        $this->model = config('ai.default_model', 'mistral-large-latest');
    }

    /**
     * Ask a question about a specific document
     */
    public function askQuestion(
        Document $document,
        string $question,
        ?int $userId = null,
        ?string $conversationId = null
    ): DocumentQuestion {
        // Search for relevant chunks
        $searchResults = $this->vectorSearchService->searchDocument($document, $question);

        if (empty($searchResults)) {
            return $this->createQuestionRecord(
                $document,
                $question,
                "I cannot find relevant information in this document to answer your question.",
                [],
                [],
                0.0,
                0,
                $userId,
                $conversationId
            );
        }

        // Build context from retrieved chunks
        $context = $this->buildContext($searchResults);
        $citations = $this->extractCitations($searchResults);

        // Generate answer using Mistral via agent
        $prompt = $this->buildPrompt($question, $context);
        
        try {
            // Use Laravel AI Agent
            $agent = new \App\Agents\DocumentQuestionAgent($this->model);
            $response = $agent->prompt($prompt);
            
            $answer = (string) $response;
            
            // Skip token tracking - Laravel AI SDK response structure unknown
            $tokensUsed = 0;

            // Calculate confidence based on similarity scores
            $confidence = $this->calculateConfidence($searchResults);

            return $this->createQuestionRecord(
                $document,
                $question,
                $answer,
                $citations,
                $this->serializeChunks($searchResults),
                $confidence,
                $tokensUsed,
                $userId,
                $conversationId
            );

        } catch (\Exception $e) {
            throw new \Exception("Failed to generate answer: " . $e->getMessage());
        }
    }

    /**
     * Ask a question across all documents (no user filtering)
     */
    public function askQuestionAcrossDocuments(
        ?int $userId, // Keep parameter but ignore it
        string $question,
        ?string $conversationId = null
    ): DocumentQuestion {
        // Search across all documents (no user filtering)
        $searchResults = $this->vectorSearchService->searchAllDocuments(null, $question);

        if (empty($searchResults)) {
            return $this->createQuestionRecord(
                null,
                $question,
                "I cannot find relevant information in the documents to answer your question.",
                [],
                [],
                0.0,
                0,
                null, // No user_id
                $conversationId
            );
        }

        // Build context and generate answer
        $context = $this->buildContext($searchResults);
        $citations = $this->extractCitations($searchResults);
        $prompt = $this->buildPrompt($question, $context);

        try {
            // Use Laravel AI Agent
            $agent = new \App\Agents\DocumentQuestionAgent($this->model);
            $response = $agent->prompt($prompt);
            
            $answer = (string) $response;
            
            // Try to get tokens used - handle different response structures
            $tokensUsed = 0;
            try {
                // Try different possible properties for token usage
                if (property_exists($response, 'usage')) {
                    $usage = $response->usage;
                    if (is_array($usage)) {
                        $tokensUsed = ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0);
                    } elseif (is_object($usage)) {
                        $tokensUsed = ($usage->input_tokens ?? 0) + ($usage->output_tokens ?? 0);
                    }
                }
                // Skip method calls since they don't exist in Laravel AI SDK
            } catch (\Exception $e) {
                // Fallback: couldn't get token count, that's okay
                $tokensUsed = 0;
            }
            
            $confidence = $this->calculateConfidence($searchResults);

            // Use the document from the highest-ranked chunk
            $primaryDocument = $searchResults[0]['chunk']->document ?? null;

            return $this->createQuestionRecord(
                $primaryDocument,
                $question,
                $answer,
                $citations,
                $this->serializeChunks($searchResults),
                $confidence,
                $tokensUsed,
                null, // No user_id
                $conversationId
            );

        } catch (\Exception $e) {
            throw new \Exception("Failed to generate answer: " . $e->getMessage());
        }
    }

    protected function buildContext(array $searchResults): string
    {
        $context = '';
        $chunkNumber = 1;

        foreach ($searchResults as $result) {
            $chunk = $result['chunk'];
            $metadata = $chunk->metadata ?? [];
            $page = $metadata['page'] ?? 'unknown';

            $context .= "[Chunk {$chunkNumber} - Page {$page}]\n";
            $context .= $chunk->chunk_text . "\n\n";
            $chunkNumber++;
        }

        return trim($context);
    }

    protected function extractCitations(array $searchResults): array
    {
        $citations = [];

        foreach ($searchResults as $result) {
            $chunk = $result['chunk'];
            $metadata = $chunk->metadata ?? [];

            $citations[] = [
                'chunk_id' => $chunk->id,
                'document_id' => $chunk->document_id,
                'document_name' => $chunk->document->original_name ?? 'Unknown',
                'page' => $metadata['page'] ?? null,
                'similarity' => round($result['similarity'], 3),
            ];
        }

        return $citations;
    }

    protected function buildPrompt(string $question, string $context): string
    {
        return <<<PROMPT
Context from documents:
{$context}

Question: {$question}

Answer the question above using ONLY the context provided. Be concise. If the answer is not in the context, state that clearly.
PROMPT;
    }

    protected function calculateConfidence(array $searchResults): float
    {
        if (empty($searchResults)) {
            return 0.0;
        }

        // Average of top similarities, weighted toward top result
        $weights = [0.4, 0.3, 0.2, 0.1];
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($searchResults as $index => $result) {
            $weight = $weights[$index] ?? 0.05;
            $weightedSum += $result['similarity'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0.0;
    }

    protected function serializeChunks(array $searchResults): array
    {
        return array_map(function ($result) {
            return [
                'chunk_id' => $result['chunk']->id,
                'text_preview' => Str::limit($result['chunk']->chunk_text, 150),
                'similarity' => round($result['similarity'], 3),
            ];
        }, $searchResults);
    }

    protected function createQuestionRecord(
        ?Document $document,
        string $question,
        string $answer,
        array $citations,
        array $retrievedChunks,
        float $confidence,
        int $tokensUsed,
        ?int $userId,
        ?string $conversationId
    ): DocumentQuestion {
        return DocumentQuestion::create([
            'document_id' => $document?->id,
            'user_id' => $userId,
            'conversation_id' => $conversationId ?? Str::uuid(),
            'question' => $question,
            'answer' => $answer,
            'citations' => $citations,
            'retrieved_chunks' => $retrievedChunks,
            'confidence' => $confidence,
            'tokens_used' => $tokensUsed,
        ]);
    }
}
