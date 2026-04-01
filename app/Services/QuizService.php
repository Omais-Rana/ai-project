<?php

namespace App\Services;

use App\Agents\QuizAgent;
use App\Models\Document;
use App\Models\Quiz;

class QuizService
{
    protected string $model;

    public function __construct(string $model = null)
    {
        $this->model = $model ?? config('ai.default_model', 'mistral-large-latest');
    }

    /**
     * Generate a quiz for a document
     */
    public function generateQuiz(Document $document, string $difficulty = 'mixed', int $questionCount = 10): Quiz
    {
        // Check if quiz already exists for this difficulty
        $existing = $document->quizzes()->where('difficulty', $difficulty)->first();
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
        $prompt = $this->buildPrompt($document, $fullContent, $difficulty, $questionCount);

        // Generate quiz using AI agent
        $agent = new QuizAgent($this->model);
        $response = $agent->prompt($prompt);

        $responseText = (string) $response;

        // Parse quiz response
        $questions = $this->parseQuizResponse($responseText);

        // Save to database
        $quiz = Quiz::create([
            'document_id' => $document->id,
            'questions' => $questions,
            'difficulty' => $difficulty,
            'total_questions' => count($questions),
        ]);

        return $quiz;
    }

    /**
     * Regenerate quiz
     */
    public function regenerateQuiz(Document $document, string $difficulty = 'mixed', int $questionCount = 10): Quiz
    {
        // Delete existing quiz for this difficulty
        $document->quizzes()->where('difficulty', $difficulty)->delete();

        // Generate new one
        return $this->generateQuiz($document, $difficulty, $questionCount);
    }

    /**
     * Check answer for a quiz question
     */
    public function checkAnswer(array $question, $userAnswer): array
    {
        $type = $question['type'];
        $correctAnswer = $question['correct_answer'];
        $explanation = $question['explanation'] ?? '';

        $isCorrect = false;

        switch ($type) {
            case 'multiple_choice':
                $isCorrect = (int)$userAnswer === (int)$correctAnswer;
                break;

            case 'true_false':
                $isCorrect = (bool)$userAnswer === (bool)$correctAnswer;
                break;

            case 'short_answer':
                // Flexible matching for short answers
                $userAnswerNorm = strtolower(trim($userAnswer));
                $correctAnswerNorm = strtolower(trim($correctAnswer));

                // Exact match or contains correct answer
                $isCorrect = $userAnswerNorm === $correctAnswerNorm ||
                    str_contains($userAnswerNorm, $correctAnswerNorm) ||
                    str_contains($correctAnswerNorm, $userAnswerNorm);
                break;
        }

        return [
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'explanation' => $explanation,
        ];
    }

    /**
     * Build prompt for quiz generation
     */
    protected function buildPrompt(Document $document, string $content, string $difficulty, int $count): string
    {
        $difficultyNote = $difficulty === 'mixed'
            ? "Mix of easy, medium, and hard questions (30% easy, 50% medium, 20% hard)"
            : "All questions should be {$difficulty} difficulty";

        return <<<PROMPT
Generate a {$count}-question quiz for the following academic document.

Document: {$document->original_name}
Difficulty: {$difficultyNote}

CONTENT:
{$content}

Create a quiz in the JSON array format specified in your instructions. Include multiple choice, true/false, and short answer questions.
PROMPT;
    }

    /**
     * Parse quiz response from AI
     */
    protected function parseQuizResponse(string $response): array
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
                throw new \Exception("Failed to parse quiz response. No valid JSON array found.");
            }
        }

        $questions = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse quiz JSON: " . json_last_error_msg());
        }

        return $questions;
    }
}
