<?php

namespace App\Services\DocumentParser;

use Smalot\PdfParser\Parser as PdfParserLib;

class PdfParser implements ParserInterface
{
    public function parse(string $filePath): string
    {
        if (!class_exists(PdfParserLib::class)) {
            throw new \Exception("PDF parser not installed. Run: composer require smalot/pdfparser");
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $parser = new PdfParserLib();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            $text = preg_replace('/\s+/', ' ', $text);
            $text = preg_replace('/\n{3,}/', "\n\n", $text);
            
            return trim($text);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse PDF: " . $e->getMessage());
        }
    }

    public function supports(string $fileType): bool
    {
        return strtolower($fileType) === 'pdf';
    }

    public function parseWithMetadata(string $filePath): array
    {
        if (!class_exists(PdfParserLib::class)) {
            throw new \Exception("PDF parser not installed. Run: composer require smalot/pdfparser");
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $parser = new PdfParserLib();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            
            $chunks = [];
            $pageNumber = 1;
            
            foreach ($pages as $page) {
                $text = $page->getText();
                
                if (!empty(trim($text))) {
                    $chunks[] = [
                        'text' => trim($text),
                        'metadata' => [
                            'page' => $pageNumber,
                            'type' => 'pdf',
                        ],
                    ];
                }
                
                $pageNumber++;
            }
            
            return $chunks;
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse PDF with metadata: " . $e->getMessage());
        }
    }
}
