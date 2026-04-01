<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentQuestionController;
use App\Http\Controllers\StudyMaterialsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/documents');
});

Route::prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/history', [DocumentQuestionController::class, 'history'])->name('documents.history');
    Route::post('/ask-all', [DocumentQuestionController::class, 'askAcrossDocuments'])->name('documents.ask-all');

    // Document-specific routes MUST come before /{document} route
    Route::get('/{document}/history', [DocumentQuestionController::class, 'documentHistory'])->name('documents.document-history');
    Route::get('/{document}/chat', [DocumentController::class, 'chat'])->name('documents.chat');
    Route::post('/{document}/ask', [DocumentQuestionController::class, 'ask'])->name('documents.ask');

    // Study Materials routes
    Route::post('/{document}/flashcards/generate', [StudyMaterialsController::class, 'generateFlashcards'])->name('study-materials.flashcards.generate');
    Route::get('/{document}/flashcards', [StudyMaterialsController::class, 'viewFlashcards'])->name('study-materials.flashcards.view');
    Route::post('/{document}/flashcards/regenerate', [StudyMaterialsController::class, 'regenerateFlashcards'])->name('study-materials.flashcards.regenerate');
    Route::get('/{document}/flashcards/export/anki', [StudyMaterialsController::class, 'exportFlashcardsAnki'])->name('study-materials.flashcards.export.anki');
    Route::get('/{document}/flashcards/export/json', [StudyMaterialsController::class, 'exportFlashcardsJson'])->name('study-materials.flashcards.export.json');

    Route::post('/{document}/quiz/generate', [StudyMaterialsController::class, 'generateQuiz'])->name('study-materials.quiz.generate');
    Route::get('/{document}/quiz', [StudyMaterialsController::class, 'viewQuiz'])->name('study-materials.quiz.view');
    Route::post('/{document}/quiz/regenerate', [StudyMaterialsController::class, 'regenerateQuiz'])->name('study-materials.quiz.regenerate');

    Route::post('/{document}/summary/generate', [StudyMaterialsController::class, 'generateSummary'])->name('study-materials.summary.generate');
    Route::get('/{document}/summary', [StudyMaterialsController::class, 'viewSummary'])->name('study-materials.summary.view');
    Route::post('/{document}/summary/regenerate', [StudyMaterialsController::class, 'regenerateSummary'])->name('study-materials.summary.regenerate');

    // Test/Debug route
    Route::get('/{document}/test-buttons', function (\App\Models\Document $document) {
        return view('documents.test-buttons', compact('document'));
    })->name('documents.test-buttons');

    // Generic document routes last
    Route::get('/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});

// Quiz answer checking (not document-specific)
Route::post('/quiz/check-answer', [StudyMaterialsController::class, 'checkQuizAnswer'])->name('study-materials.quiz.check-answer');
