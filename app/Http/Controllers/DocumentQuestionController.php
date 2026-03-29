<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentQuestion;
use App\Services\DocumentQuestionService;
use Illuminate\Http\Request;

class DocumentQuestionController extends Controller
{
    public function __construct(
        protected DocumentQuestionService $questionService
    ) {}

    public function ask(Request $request, Document $document)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        try {
            $questionRecord = $this->questionService->askQuestion(
                $document,
                $request->input('question'),
                null, // No user_id needed
                $request->input('conversation_id')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $questionRecord->id,
                    'question' => $questionRecord->question,
                    'answer' => $questionRecord->answer,
                    'citations' => $questionRecord->citations,
                    'confidence' => $questionRecord->confidence,
                    'confidence_percentage' => $questionRecord->getConfidencePercentage(),
                    'is_high_confidence' => $questionRecord->isHighConfidence(),
                    'conversation_id' => $questionRecord->conversation_id,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function askAcrossDocuments(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        try {
            $questionRecord = $this->questionService->askQuestionAcrossDocuments(
                null, // No user_id needed
                $request->input('question'),
                $request->input('conversation_id')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $questionRecord->id,
                    'question' => $questionRecord->question,
                    'answer' => $questionRecord->answer,
                    'citations' => $questionRecord->citations,
                    'confidence' => $questionRecord->confidence,
                    'confidence_percentage' => $questionRecord->getConfidencePercentage(),
                    'is_high_confidence' => $questionRecord->isHighConfidence(),
                    'conversation_id' => $questionRecord->conversation_id,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request)
    {
        // Get all conversation history (no user filtering)
        $questions = DocumentQuestion::with(['document'])
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    public function documentHistory(Request $request, Document $document)
    {
        // Get conversation history for specific document
        $questions = DocumentQuestion::where('document_id', $document->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questions->map(function($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'answer' => $question->answer,
                    'citations' => $question->citations,
                    'confidence' => $question->confidence,
                    'confidence_percentage' => $question->getConfidencePercentage(),
                    'is_high_confidence' => $question->isHighConfidence(),
                    'conversation_id' => $question->conversation_id,
                    'created_at' => $question->created_at,
                ];
            })
        ]);
    }
}
