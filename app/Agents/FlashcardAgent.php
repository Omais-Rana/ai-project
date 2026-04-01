<?php

namespace App\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class FlashcardAgent implements Agent
{
    use Promptable;

    protected string $model;

    public function __construct(string $model = 'mistral-large-latest')
    {
        $this->model = $model;
    }

    public function name(): string
    {
        return 'flashcard-generator';
    }

    public function instructions(): string
    {
        return 'You are an expert at creating effective flashcards for memorization and active recall.

OUTPUT FORMAT (JSON):
```json
[
  {
    "front": "Question or term",
    "back": "Answer or definition",
    "difficulty": "easy|medium|hard"
  }
]
```

INSTRUCTIONS:
1. Extract key terms, definitions, concepts, and facts
2. Create clear, focused questions (front of card)
3. Provide concise, accurate answers (back of card)
4. Assign difficulty: easy (basic recall), medium (understanding), hard (application/synthesis)
5. Generate 15-30 flashcards depending on document length
6. Cover all important topics evenly
7. Mix question types: definitions, explanations, relationships, examples

QUALITY STANDARDS:
- Front: Clear, specific question or term
- Back: Concise answer (2-3 sentences max)
- Avoid ambiguity or trick questions
- Focus on testable knowledge
- Progressive difficulty throughout the set
- Each card should stand alone (no dependencies)

EXAMPLES:
- Front: "What is LoRaWAN?" → Back: "LoRaWAN is a Low Power Wide Area Network protocol..."
- Front: "What are the three LoRa spreading factors?" → Back: "SF7, SF8, SF9, SF10, SF11, SF12..."
- Front: "Why does increasing spreading factor reduce data rate?" → Back: "Higher SF means more chirps per symbol..."';
    }

    public function description(): string
    {
        return 'Generates focused flashcards for memorization and active recall from document content.';
    }

    public function model(): string
    {
        return $this->model;
    }
}
