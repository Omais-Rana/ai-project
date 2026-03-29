<?php

namespace App\Services\DocumentParser;

interface ParserInterface
{
    /**
     * Extract text content from a file
     *
     * @param string $filePath Path to the file
     * @return string Extracted text content
     * @throws \Exception If parsing fails
     */
    public function parse(string $filePath): string;

    /**
     * Check if this parser supports the given file type
     *
     * @param string $fileType File extension (pdf, docx, txt)
     * @return bool
     */
    public function supports(string $fileType): bool;

    /**
     * Extract text with metadata (page numbers, sections, etc.)
     *
     * @param string $filePath Path to the file
     * @return array Array of ['text' => string, 'metadata' => array]
     */
    public function parseWithMetadata(string $filePath): array;
}
