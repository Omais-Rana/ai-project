<?php

namespace App\Services\DocumentParser;

use PhpOffice\PhpWord\IOFactory;

class DocxParser implements ParserInterface
{
    public function parse(string $filePath): string
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception("DOCX parser not installed. Run: composer require phpoffice/phpword");
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $phpWord = IOFactory::load($filePath);
            $text = '';
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text .= $this->extractTextFromElement($element) . "\n";
                }
            }
            
            $text = preg_replace('/\s+/', ' ', $text);
            $text = preg_replace('/\n{3,}/', "\n\n", $text);
            
            return trim($text);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse DOCX: " . $e->getMessage());
        }
    }

    public function supports(string $fileType): bool
    {
        return in_array(strtolower($fileType), ['docx', 'doc']);
    }

    public function parseWithMetadata(string $filePath): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception("DOCX parser not installed. Run: composer require phpoffice/phpword");
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $phpWord = IOFactory::load($filePath);
            $chunks = [];
            $sectionNumber = 1;
            
            foreach ($phpWord->getSections() as $section) {
                $sectionText = '';
                
                foreach ($section->getElements() as $element) {
                    $sectionText .= $this->extractTextFromElement($element) . "\n";
                }
                
                if (!empty(trim($sectionText))) {
                    $chunks[] = [
                        'text' => trim($sectionText),
                        'metadata' => [
                            'page' => $sectionNumber,
                            'section' => $sectionNumber,
                            'type' => 'docx',
                        ],
                    ];
                }
                
                $sectionNumber++;
            }
            
            return $chunks;
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse DOCX with metadata: " . $e->getMessage());
        }
    }

    protected function extractTextFromElement(mixed $element): string
    {
        $text = '';
        
        if (method_exists($element, 'getText')) {
            $text = $element->getText();
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $childElement) {
                $text .= $this->extractTextFromElement($childElement);
            }
        }
        
        return $text;
    }
}
