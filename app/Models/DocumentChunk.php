<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'chunk_text',
        'chunk_index',
        'start_char',
        'end_char',
        'embedding',
        'metadata',
    ];

    protected $casts = [
        'chunk_index' => 'integer',
        'start_char' => 'integer',
        'end_char' => 'integer',
        'embedding' => 'array',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function hasEmbedding(): bool
    {
        return !empty($this->embedding);
    }

    public function getPageNumber(): ?int
    {
        return $this->metadata['page'] ?? null;
    }

    public function getSectionTitle(): ?string
    {
        return $this->metadata['section'] ?? null;
    }
}
