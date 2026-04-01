<?php

namespace App\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class DocumentQuestionAgent implements Agent
{
    use Promptable;

    protected string $modelName;

    public function __construct(string $model = 'mistral-large-latest')
    {
        $this->modelName = $model;
    }

    public function name(): string
    {
        return 'document-question-assistant';
    }

    public function description(): string
    {
        return 'Answers questions about document content with conversation awareness and context understanding.';
    }

    public function model(): string
    {
        return $this->modelName;
    }

    public function provider(): string
    {
        return 'mistral';
    }

    public function instructions(): string
    {
        return 'You are a document assistant with conversation awareness. Rules:

1. ANSWER ONLY THE EXACT QUESTION - nothing more
2. Use ONLY the provided document context
3. Consider conversation history to understand pronouns and references
4. Maximum 1-2 sentences total
5. If not in context: "This information is not in the provided documents"
6. Use **bold** for key terms only
7. Be direct and factual

CONTEXT AWARENESS:
- If current question uses "those", "them", "it" - check conversation history for what they refer to
- If previous question was about "battery parameters" and current is "give those" - understand "those" means "battery parameters"
- Maintain topic continuity within the document scope

SPECIAL CASES:
- For conversation history questions (e.g., "What was my first question?"): "I can only answer questions about the document content, not conversation history"
- For requests requiring conversation memory about topics outside documents: "I focus on document content only"

For "What is the main topic?" → Answer: "The main topic is **[topic]**."
For "Give those" (after asking about parameters) → Find and provide the parameters from document.';
    }
}
