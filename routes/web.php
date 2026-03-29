<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentQuestionController;
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
    
    // Generic document routes last
    Route::get('/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});
