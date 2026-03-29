<?php

namespace App\Services\DocumentParser;

class TxtParser implements ParserInterface
{
    public function parse(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }

        // Normalize line endings and remove excessive whitespace
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    public function supports(string $fileType): bool
    {
        return strtolower($fileType) === 'txt';
    }

    public function parseWithMetadata(string $filePath): array
    {
        $text = $this->parse($filePath);
        
        // For text files, we can estimate "pages" by line count
        $lines = explode("\n", $text);
        $linesPerPage = 50;
        
        $chunks = [];
        $currentPage = 1;
        $currentChunk = '';
        $lineCount = 0;
        
        foreach ($lines as $line) {
            $currentChunk .= $line . "\n";
            $lineCount++;
            
            if ($lineCount >= $linesPerPage) {
                $chunks[] = [
                    'text' => trim($currentChunk),
                    'metadata' => [
                        'page' => $currentPage,
                        'type' => 'text',
                    ],
                ];
                
                $currentChunk = '';
                $lineCount = 0;
                $currentPage++;
            }
        }
        
        if (!empty($currentChunk)) {
            $chunks[] = [
                'text' => trim($currentChunk),
                'metadata' => [
                    'page' => $currentPage,
                    'type' => 'text',
                ],
            ];
        }
        
        return $chunks;
    }
}
