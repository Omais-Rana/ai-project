<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'length_mode',
        'content',
        'word_count',
    ];

    /**
     * Get the document that owns the summary
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Scope to get summary by length mode
     */
    public function scopeByLengthMode($query, string $mode)
    {
        return $query->where('length_mode', $mode);
    }

    /**
     * Check if this is a TL;DR summary
     */
    public function isTldr(): bool
    {
        return $this->length_mode === 'tldr';
    }

    /**
     * Check if this is a brief summary
     */
    public function isBrief(): bool
    {
        return $this->length_mode === 'brief';
    }

    /**
     * Check if this is a detailed summary
     */
    public function isDetailed(): bool
    {
        return $this->length_mode === 'detailed';
    }
}
