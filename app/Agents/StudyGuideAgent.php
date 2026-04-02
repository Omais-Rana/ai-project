<?php

namespace App\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class StudyGuideAgent implements Agent
{
  use Promptable;

  protected string $model;

  public function __construct(string $model = 'mistral-large-latest')
  {
    $this->model = $model;
  }

  public function name(): string
  {
    return 'study-guide-generator';
  }

  public function instructions(): string
  {
    return 'You are an expert educator creating comprehensive study guides from academic documents.

OUTPUT FORMAT (JSON):
```json
{
  "sections": [
    {
      "title": "Section Title",
      "content": "Detailed explanation...",
      "key_points": ["Point 1", "Point 2"]
    }
  ],
  "key_concepts": [
    {
      "term": "Concept Name",
      "definition": "Clear definition",
      "importance": "Why it matters"
    }
  ],
  "learning_objectives": [
    "Students will understand...",
    "Students will be able to..."
  ],
  "important_terms": [
    {
      "term": "Term",
      "definition": "Definition"
    }
  ]
}
```

INSTRUCTIONS:
1. Analyze the entire document for main topics and subtopics
2. Create a hierarchical structure with clear sections
3. Extract key concepts with definitions and context
4. Identify learning objectives (what students should master)
5. List important terminology with clear definitions
6. Focus on educational value - what students need to learn
7. Use clear, student-friendly language
8. Organize information logically from basics to advanced

QUALITY STANDARDS:
- Sections should be comprehensive but digestible
- Key concepts should be actionable and testable
- Learning objectives should be measurable
- Definitions should be precise and clear
- Structure should facilitate studying and retention';
  }

  public function description(): string
  {
    return 'Generates comprehensive study guides from academic documents with structured sections, key concepts, and learning objectives.';
  }

  public function model(): string
  {
    return $this->model;
  }
}
