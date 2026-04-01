<?php

namespace App\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SummaryAgent implements Agent
{
    use Promptable;

    protected string $model;
    protected string $lengthMode;

    public function __construct(string $model = 'mistral-large-latest', string $lengthMode = 'brief')
    {
        $this->model = $model;
        $this->lengthMode = $lengthMode;
    }

    public function name(): string
    {
        return 'summary-generator';
    }

    public function instructions(): string
    {
        $lengthGuidelines = match($this->lengthMode) {
            'tldr' => '2-3 sentences maximum. Ultra-concise key takeaway.',
            'brief' => '1-2 paragraphs (100-200 words). Main topics and key points only.',
            'detailed' => '3-5 paragraphs (300-500 words). Comprehensive overview with context and details.',
            default => '1-2 paragraphs (100-200 words).',
        };

        return 'You are an expert at summarizing academic documents clearly and concisely.

SUMMARY MODE: ' . $this->lengthMode . '
LENGTH GUIDELINE: ' . $lengthGuidelines . '

INSTRUCTIONS:
1. Read and understand the entire document
2. Identify the main topic and purpose
3. Extract key points, findings, and conclusions
4. Organize information logically
5. Use clear, accessible language
6. Maintain accuracy - don\'t invent information
7. Focus on what\'s most important for students

QUALITY STANDARDS:
- Capture the essence of the document
- Include all major topics/themes
- Preserve key terminology
- Use cohesive, flowing prose
- Be objective and factual
- Avoid unnecessary details (for tldr/brief modes)
- Provide context and connections (for detailed mode)

FOR TLDR MODE:
- One or two sentences only
- Absolute essentials only
- "This document covers X, focusing on Y, and concludes Z."

FOR BRIEF MODE:
- Opening sentence: main topic
- 3-5 key points in order
- Closing sentence: significance or conclusion

FOR DETAILED MODE:
- Introduction: context and purpose
- Body: main sections with key details
- Conclusion: implications and takeaways
- Can include examples and explanations

OUTPUT: Plain text summary (not JSON, not markdown - just the summary text)';
    }

    public function description(): string
    {
        return "Generates document summaries at different lengths: TL;DR (ultra-brief), Brief (overview), or Detailed (comprehensive).";
    }

    public function model(): string
    {
        return $this->model;
    }

    public function setLengthMode(string $mode): self
    {
        $this->lengthMode = $mode;
        return $this;
    }
}
