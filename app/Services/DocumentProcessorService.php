<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\DocumentParser\PdfParser;
use App\Services\DocumentParser\DocxParser;
use App\Services\DocumentParser\TxtParser;

class DocumentProcessorService
{
    protected int $chunkSize;
    protected int $chunkOverlap;
    protected array $parsers = [];

    public function __construct()
    {
        $this->chunkSize = (int) config('ai.chunk_size', 800);
        $this->chunkOverlap = (int) config('ai.chunk_overlap', 150);
        
        $this->parsers = [
            new PdfParser(),
            new DocxParser(),
            new TxtParser(),
        ];
    }

    public function processDocument(Document $document): void
    {
        try {
            $document->update(['status' => 'processing']);

            // Parse document - construct proper file path using Storage facade
            $filePath = storage_path('app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $document->file_path));
            
            if (!file_exists($filePath)) {
                throw new \Exception("File not found at: {$filePath}. Please check if the file was uploaded correctly.");
            }

            $parser = $this->getParser($document->file_type);
            $chunks = $parser->parseWithMetadata($filePath);

            // Process and store chunks
            $chunkIndex = 0;
            foreach ($chunks as $pageData) {
                $text = $pageData['text'];
                $metadata = $pageData['metadata'];

                // Split page into smaller chunks if needed
                $textChunks = $this->chunkText($text);

                foreach ($textChunks as $chunkText) {
                    DocumentChunk::create([
                        'document_id' => $document->id,
                        'chunk_text' => $chunkText,
                        'chunk_index' => $chunkIndex,
                        'metadata' => $metadata,
                    ]);

                    $chunkIndex++;
                }
            }

            $document->update([
                'status' => 'completed',
                'total_chunks' => $chunkIndex,
            ]);

        } catch (\Exception $e) {
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function getParser(string $fileType): \App\Services\DocumentParser\ParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($fileType)) {
                return $parser;
            }
        }

        throw new \Exception("No parser found for file type: {$fileType}");
    }

    protected function chunkText(string $text): array
    {
        $chunks = [];
        $sentences = $this->splitIntoSentences($text);
        
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            // If adding this sentence would exceed chunk size
            if (strlen($currentChunk) + strlen($sentence) > $this->chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                
                // Start new chunk with overlap from previous chunk
                $overlapText = $this->getOverlapText($currentChunk);
                $currentChunk = $overlapText . $sentence;
            } else {
                $currentChunk .= $sentence;
            }
        }
        
        // Add remaining chunk
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }

    protected function splitIntoSentences(string $text): array
    {
        // Simple sentence splitting (can be improved with NLP library)
        $text = preg_replace('/([.!?])\s+/', "$1\n", $text);
        $sentences = explode("\n", $text);
        
        return array_filter($sentences, fn($s) => !empty(trim($s)));
    }

    protected function getOverlapText(string $text): string
    {
        if (strlen($text) <= $this->chunkOverlap) {
            return $text . ' ';
        }
        
        return substr($text, -$this->chunkOverlap) . ' ';
    }
}
