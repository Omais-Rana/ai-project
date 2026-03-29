<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'conversation_id',
        'question',
        'answer',
        'citations',
        'retrieved_chunks',
        'confidence',
        'tokens_used',
    ];

    protected $casts = [
        'citations' => 'array',
        'retrieved_chunks' => 'array',
        'confidence' => 'decimal:2',
        'tokens_used' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCitations(): bool
    {
        return !empty($this->citations);
    }

    public function getConfidencePercentage(): ?int
    {
        return $this->confidence ? (int)($this->confidence * 100) : null;
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence && $this->confidence >= 0.75;
    }
}
