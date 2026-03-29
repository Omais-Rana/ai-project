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
        return <<<INSTRUCTIONS
You are a document assistant. Rules:

1. ANSWER ONLY THE EXACT QUESTION - nothing more
2. Use ONLY the provided context
3. Maximum 1-2 sentences total
4. No introductions, explanations, or bullet points unless specifically asked
5. If not in context: "This information is not in the provided documents"
6. Use **bold** for key terms only
7. Be direct and factual

For "What is the main topic?" → Answer: "The main topic is **[topic]**."
INSTRUCTIONS;
    }
}
