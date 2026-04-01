<?php

namespace App\Services;

use App\Agents\StudyGuideAgent;
use App\Models\Document;
use App\Models\StudyGuide;

class StudyGuideService
{
    protected string $model;

    public function __construct(string $model = null)
    {
        $this->model = $model ?? config('ai.default_model', 'mistral-large-latest');
    }

    /**
     * Generate a comprehensive study guide for a document
     */
    public function generateStudyGuide(Document $document): StudyGuide
    {
        // Check if study guide already exists
        $existing = $document->studyGuide;
        if ($existing) {
            return $existing;
        }

        // Get all document chunks to build full context
        $chunks = $document->chunks()->orderBy('chunk_index')->get();

        if ($chunks->isEmpty()) {
            throw new \Exception("Document has no processed chunks. Please process the document first.");
        }

        // Build full document content
        $fullContent = $chunks->pluck('chunk_text')->implode("\n\n");

        // Prepare prompt for the agent
        $prompt = $this->buildPrompt($document, $fullContent);

        // Generate study guide using AI agent
        $agent = new StudyGuideAgent($this->model);
        $response = $agent->prompt($prompt);

        $responseText = (string) $response;

        // Parse JSON response
        $content = $this->parseStudyGuideResponse($responseText);

        // Generate markdown version for export
        $markdown = $this->convertToMarkdown($content, $document);

        // Save to database
        $studyGuide = StudyGuide::create([
            'document_id' => $document->id,
            'content' => $content,
            'raw_markdown' => $markdown,
        ]);

        return $studyGuide;
    }

    /**
     * Regenerate study guide (force refresh)
     */
    public function regenerateStudyGuide(Document $document): StudyGuide
    {
        // Delete existing study guide
        $document->studyGuide?->delete();

        // Generate new one
        return $this->generateStudyGuide($document);
    }

    /**
     * Build prompt for study guide generation
     */
    protected function buildPrompt(Document $document, string $content): string
    {
        return <<<PROMPT
Generate a comprehensive study guide for the following academic document.

Document: {$document->original_name}
Document Type: {$document->file_type}

CONTENT:
{$content}

Create a structured study guide in the JSON format specified in your instructions. Focus on helping students understand and master this material.
PROMPT;
    }

    /**
     * Parse study guide response from AI
     */
    protected function parseStudyGuideResponse(string $response): array
    {
        // Try to extract JSON from markdown code blocks
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/```\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } else {
            // Try to find JSON directly
            if (preg_match('/\{.*\}/s', $response, $matches)) {
                $jsonStr = $matches[0];
            } else {
                throw new \Exception("Failed to parse study guide response. No valid JSON found.");
            }
        }

        $content = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse study guide JSON: " . json_last_error_msg());
        }

        return $content;
    }

    /**
     * Convert structured content to markdown for export
     */
    protected function convertToMarkdown(array $content, Document $document): string
    {
        $markdown = "# Study Guide: {$document->original_name}\n\n";

        // Learning Objectives
        if (!empty($content['learning_objectives'])) {
            $markdown .= "## Learning Objectives\n\n";
            foreach ($content['learning_objectives'] as $objective) {
                $markdown .= "- {$objective}\n";
            }
            $markdown .= "\n";
        }

        // Sections
        if (!empty($content['sections'])) {
            $markdown .= "## Main Content\n\n";
            foreach ($content['sections'] as $section) {
                $markdown .= "### {$section['title']}\n\n";
                $markdown .= "{$section['content']}\n\n";

                if (!empty($section['key_points'])) {
                    $markdown .= "**Key Points:**\n";
                    foreach ($section['key_points'] as $point) {
                        $markdown .= "- {$point}\n";
                    }
                    $markdown .= "\n";
                }
            }
        }

        // Key Concepts
        if (!empty($content['key_concepts'])) {
            $markdown .= "## Key Concepts\n\n";
            foreach ($content['key_concepts'] as $concept) {
                $markdown .= "**{$concept['term']}**\n\n";
                $markdown .= "{$concept['definition']}\n\n";
                if (!empty($concept['importance'])) {
                    $markdown .= "*Why it matters:* {$concept['importance']}\n\n";
                }
            }
        }

        // Important Terms
        if (!empty($content['important_terms'])) {
            $markdown .= "## Important Terms\n\n";
            foreach ($content['important_terms'] as $term) {
                $markdown .= "- **{$term['term']}**: {$term['definition']}\n";
            }
            $markdown .= "\n";
        }

        return $markdown;
    }
}
