<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->json('questions'); // Array of {type, question, options, correct_answer, explanation, difficulty}
            $table->string('difficulty')->default('mixed'); // easy, medium, hard, mixed
            $table->integer('total_questions')->default(0);
            $table->timestamps();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
