<?php

namespace App\Services;

use Laravel\Ai\Embeddings;
use Illuminate\Support\Facades\Cache;

class EmbeddingService
{
    protected string $model;
    protected bool $cacheEnabled;

    public function __construct()
    {
        $this->model = config('ai.embedding_model', 'mistral-embed');
        $this->cacheEnabled = config('ai.caching.embeddings.cache', true);
    }

    /**
     * Generate embedding for a single text
     */
    public function generateEmbedding(string $text): array
    {
        $cacheKey = 'embedding:' . md5($text);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Use Laravel AI Embeddings facade
            $response = Embeddings::for([$text])
                ->generate('mistral', $this->model);
            
            // Get the embedding vector with safe extraction
            $embedding = [];
            if (is_object($response) && property_exists($response, 'embeddings')) {
                $embeddings = $response->embeddings;
                if (is_array($embeddings) && !empty($embeddings)) {
                    $embedding = $embeddings[0] ?? [];
                }
            }
            
            if (empty($embedding)) {
                throw new \Exception("Failed to extract embedding from API response - response structure invalid");
            }
            
            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $embedding, now()->addDays(30));
            }

            return $embedding;
        } catch (\Exception $e) {
            throw new \Exception("Failed to generate embedding: " . $e->getMessage());
        }
    }

    /**
     * Generate embeddings for multiple texts in batch
     */
    public function generateEmbeddings(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->generateEmbedding($text);
        }

        return $embeddings;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            throw new \Exception("Vectors must be of the same length");
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
