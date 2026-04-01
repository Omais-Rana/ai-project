<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'content',
        'raw_markdown',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Get the document that owns the study guide
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get sections from the structured content
     */
    public function getSections(): array
    {
        return $this->content['sections'] ?? [];
    }

    /**
     * Get key concepts from the structured content
     */
    public function getKeyConcepts(): array
    {
        return $this->content['key_concepts'] ?? [];
    }

    /**
     * Get learning objectives from the structured content
     */
    public function getLearningObjectives(): array
    {
        return $this->content['learning_objectives'] ?? [];
    }

    /**
     * Get important terms from the structured content
     */
    public function getImportantTerms(): array
    {
        return $this->content['important_terms'] ?? [];
    }
}
