<?php

namespace App\Services;

use App\Agents\SummaryAgent;
use App\Models\Document;
use App\Models\Summary;

class SummaryService
{
    protected string $model;

    public function __construct(string $model = null)
    {
        $this->model = $model ?? config('ai.default_model', 'mistral-large-latest');
    }

    /**
     * Generate a summary for a document
     */
    public function generateSummary(Document $document, string $lengthMode = 'brief'): Summary
    {
        // Validate length mode
        if (!in_array($lengthMode, ['tldr', 'brief', 'detailed'])) {
            throw new \InvalidArgumentException("Invalid length mode. Must be: tldr, brief, or detailed");
        }

        // Check if summary already exists for this mode
        $existing = $document->summaries()->where('length_mode', $lengthMode)->first();
        if ($existing) {
            return $existing;
        }

        // Get all document chunks
        $chunks = $document->chunks()->orderBy('chunk_index')->get();

        if ($chunks->isEmpty()) {
            throw new \Exception("Document has no processed chunks.");
        }

        $fullContent = $chunks->pluck('chunk_text')->implode("\n\n");

        // Build prompt
        $prompt = $this->buildPrompt($document, $fullContent);

        // Generate summary using AI agent
        $agent = new SummaryAgent($this->model, $lengthMode);
        $response = $agent->prompt($prompt);

        $summaryContent = trim((string) $response);

        // Calculate word count
        $wordCount = str_word_count($summaryContent);

        // Save to database
        $summary = Summary::create([
            'document_id' => $document->id,
            'length_mode' => $lengthMode,
            'content' => $summaryContent,
            'word_count' => $wordCount,
        ]);

        return $summary;
    }

    /**
     * Generate all summary modes at once
     */
    public function generateAllSummaries(Document $document): array
    {
        return [
            'tldr' => $this->generateSummary($document, 'tldr'),
            'brief' => $this->generateSummary($document, 'brief'),
            'detailed' => $this->generateSummary($document, 'detailed'),
        ];
    }

    /**
     * Regenerate summary for a specific mode
     */
    public function regenerateSummary(Document $document, string $lengthMode = 'brief'): Summary
    {
        // Delete existing summary for this mode
        $document->summaries()->where('length_mode', $lengthMode)->delete();

        // Generate new one
        return $this->generateSummary($document, $lengthMode);
    }

    /**
     * Build prompt for summary generation
     */
    protected function buildPrompt(Document $document, string $content): string
    {
        return <<<PROMPT
Summarize the following academic document.

Document: {$document->original_name}
Document Type: {$document->file_type}

CONTENT:
{$content}

Provide a summary following the length guidelines in your instructions. Focus on main topics, key findings, and important takeaways.
PROMPT;
    }
}
