<?php

namespace App\Services;

use App\Agents\FlashcardAgent;
use App\Models\Document;
use App\Models\Flashcard;

class FlashcardService
{
    protected string $model;

    public function __construct(string $model = null)
    {
        $this->model = $model ?? config('ai.default_model', 'mistral-large-latest');
    }

    /**
     * Generate flashcards for a document
     */
    public function generateFlashcards(Document $document, int $count = 20): array
    {
        // Check if flashcards already exist
        $existing = $document->flashcards;
        if ($existing->isNotEmpty()) {
            return $existing->toArray();
        }

        // Get all document chunks
        $chunks = $document->chunks()->orderBy('chunk_index')->get();

        if ($chunks->isEmpty()) {
            throw new \Exception("Document has no processed chunks.");
        }

        $fullContent = $chunks->pluck('chunk_text')->implode("\n\n");

        // Build prompt
        $prompt = $this->buildPrompt($document, $fullContent, $count);

        // Generate flashcards using AI agent
        $agent = new FlashcardAgent($this->model);
        $response = $agent->prompt($prompt);

        $responseText = (string) $response;

        // Parse flashcards response
        $flashcardsData = $this->parseFlashcardsResponse($responseText);

        // Save to database
        $flashcards = [];
        foreach ($flashcardsData as $index => $cardData) {
            $flashcard = Flashcard::create([
                'document_id' => $document->id,
                'front' => $cardData['front'],
                'back' => $cardData['back'],
                'difficulty' => $cardData['difficulty'] ?? 'medium',
                'order' => $index + 1,
            ]);
            $flashcards[] = $flashcard;
        }

        return $flashcards;
    }

    /**
     * Regenerate flashcards
     */
    public function regenerateFlashcards(Document $document, int $count = 20): array
    {
        // Delete existing flashcards
        $document->flashcards()->delete();

        // Generate new ones
        return $this->generateFlashcards($document, $count);
    }

    /**
     * Build prompt for flashcard generation
     */
    protected function buildPrompt(Document $document, string $content, int $count): string
    {
        return <<<PROMPT
Generate {$count} flashcards for the following academic document.

Document: {$document->original_name}

CONTENT:
{$content}

Create flashcards in the JSON array format specified in your instructions. Focus on key concepts, definitions, and testable knowledge.
PROMPT;
    }

    /**
     * Parse flashcards response from AI
     */
    protected function parseFlashcardsResponse(string $response): array
    {
        // Try to extract JSON array from markdown code blocks
        if (preg_match('/```json\s*(\[.*?\])\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/```\s*(\[.*?\])\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } else {
            // Try to find JSON array directly
            if (preg_match('/\[.*\]/s', $response, $matches)) {
                $jsonStr = $matches[0];
            } else {
                throw new \Exception("Failed to parse flashcards response. No valid JSON array found.");
            }
        }

        $flashcards = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse flashcards JSON: " . json_last_error_msg());
        }

        return $flashcards;
    }

    /**
     * Export flashcards to Anki format (CSV)
     */
    public function exportToAnki(Document $document): string
    {
        $flashcards = $document->flashcards()->ordered()->get();

        $csv = "Front,Back,Difficulty\n";
        foreach ($flashcards as $card) {
            $front = str_replace('"', '""', $card->front);
            $back = str_replace('"', '""', $card->back);
            $csv .= "\"{$front}\",\"{$back}\",\"{$card->difficulty}\"\n";
        }

        return $csv;
    }

    /**
     * Export flashcards to JSON
     */
    public function exportToJson(Document $document): string
    {
        $flashcards = $document->flashcards()->ordered()->get();

        return json_encode($flashcards->map(function ($card) {
            return [
                'front' => $card->front,
                'back' => $card->back,
                'difficulty' => $card->difficulty,
            ];
        })->toArray(), JSON_PRETTY_PRINT);
    }
}
