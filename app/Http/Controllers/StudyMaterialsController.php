<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FlashcardService;
use App\Services\QuizService;
use App\Services\SummaryService;
use Illuminate\Http\Request;

class StudyMaterialsController extends Controller
{
    protected FlashcardService $flashcardService;
    protected QuizService $quizService;
    protected SummaryService $summaryService;

    public function __construct(
        FlashcardService $flashcardService,
        QuizService $quizService,
        SummaryService $summaryService
    ) {
        $this->flashcardService = $flashcardService;
        $this->quizService = $quizService;
        $this->summaryService = $summaryService;
        
        // Increase execution time limit for AI generation
        set_time_limit(300); // 5 minutes
    }

    // ==================== FLASHCARDS ====================

    /**
     * Generate flashcards for a document
     */
    public function generateFlashcards(Document $document, Request $request)
    {
        try {
            $count = $request->input('count', 20);
            $flashcards = $this->flashcardService->generateFlashcards($document, $count);

            return response()->json([
                'success' => true,
                'flashcards' => $flashcards,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View flashcards (auto-generate if doesn't exist)
     */
    public function viewFlashcards(Document $document)
    {
        $flashcards = $document->flashcards()->ordered()->get();

        // Auto-generate if doesn't exist
        if ($flashcards->isEmpty()) {
            try {
                $flashcards = $this->flashcardService->generateFlashcards($document, 20);
                // Reload after generation
                $flashcards = $document->flashcards()->ordered()->get();
            } catch (\Exception $e) {
                return redirect()->route('documents.show', $document)
                    ->with('error', 'Failed to generate flashcards: ' . $e->getMessage());
            }
        }

        return view('study-materials.flashcards', [
            'document' => $document,
            'flashcards' => $flashcards,
        ]);
    }

    /**
     * Regenerate flashcards
     */
    public function regenerateFlashcards(Document $document, Request $request)
    {
        try {
            $count = $request->input('count', 20);
            $flashcards = $this->flashcardService->regenerateFlashcards($document, $count);

            return response()->json([
                'success' => true,
                'flashcards' => $flashcards,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export flashcards to Anki CSV
     */
    public function exportFlashcardsAnki(Document $document)
    {
        $csv = $this->flashcardService->exportToAnki($document);

        $filename = str_replace(' ', '_', $document->original_name) . '_flashcards.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export flashcards to JSON
     */
    public function exportFlashcardsJson(Document $document)
    {
        $json = $this->flashcardService->exportToJson($document);

        $filename = str_replace(' ', '_', $document->original_name) . '_flashcards.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ==================== QUIZ ====================

    /**
     * Generate quiz for a document
     */
    public function generateQuiz(Document $document, Request $request)
    {
        try {
            $difficulty = $request->input('difficulty', 'mixed');
            $count = $request->input('count', 10);

            $quiz = $this->quizService->generateQuiz($document, $difficulty, $count);

            return response()->json([
                'success' => true,
                'quiz' => $quiz,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View quiz (auto-generate if doesn't exist)
     */
    public function viewQuiz(Document $document, Request $request)
    {
        $difficulty = $request->query('difficulty', 'mixed');
        $quiz = $document->quizzes()->where('difficulty', $difficulty)->first();

        // Auto-generate if doesn't exist
        if (!$quiz) {
            try {
                $quiz = $this->quizService->generateQuiz($document, $difficulty, 10);
            } catch (\Exception $e) {
                return redirect()->route('documents.show', $document)
                    ->with('error', 'Failed to generate quiz: ' . $e->getMessage());
            }
        }

        return view('study-materials.quiz', [
            'document' => $document,
            'quiz' => $quiz,
        ]);
    }

    /**
     * Check quiz answer
     */
    public function checkQuizAnswer(Request $request)
    {
        $question = $request->input('question');
        $userAnswer = $request->input('answer');

        $result = $this->quizService->checkAnswer($question, $userAnswer);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Regenerate quiz
     */
    public function regenerateQuiz(Document $document, Request $request)
    {
        try {
            $difficulty = $request->input('difficulty', 'mixed');
            $count = $request->input('count', 10);

            $quiz = $this->quizService->regenerateQuiz($document, $difficulty, $count);

            return response()->json([
                'success' => true,
                'quiz' => $quiz,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ==================== SUMMARY ====================

    /**
     * Generate summary for a document
     */
    public function generateSummary(Document $document, Request $request)
    {
        try {
            $lengthMode = $request->input('mode', 'brief');
            $summary = $this->summaryService->generateSummary($document, $lengthMode);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View summary (auto-generate if doesn't exist)
     */
    public function viewSummary(Document $document, Request $request)
    {
        $lengthMode = $request->query('mode', 'brief');
        $summary = $document->summaries()->where('length_mode', $lengthMode)->first();

        // Auto-generate if doesn't exist
        if (!$summary) {
            try {
                $summary = $this->summaryService->generateSummary($document, $lengthMode);
            } catch (\Exception $e) {
                return redirect()->route('documents.show', $document)
                    ->with('error', 'Failed to generate summary: ' . $e->getMessage());
            }
        }

        return view('study-materials.summary', [
            'document' => $document,
            'summary' => $summary,
        ]);
    }

    /**
     * Regenerate summary
     */
    public function regenerateSummary(Document $document, Request $request)
    {
        try {
            $lengthMode = $request->input('mode', 'brief');
            $summary = $this->summaryService->regenerateSummary($document, $lengthMode);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
