<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'questions',
        'difficulty',
        'total_questions',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    /**
     * Get the document that owns the quiz
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get questions from the quiz
     */
    public function getQuestions(): array
    {
        return $this->questions ?? [];
    }

    /**
     * Get questions by type
     */
    public function getQuestionsByType(string $type): array
    {
        return array_filter($this->getQuestions(), function ($q) use ($type) {
            return ($q['type'] ?? '') === $type;
        });
    }

    /**
     * Get questions by difficulty
     */
    public function getQuestionsByDifficulty(string $difficulty): array
    {
        return array_filter($this->getQuestions(), function ($q) use ($difficulty) {
            return ($q['difficulty'] ?? '') === $difficulty;
        });
    }
}
