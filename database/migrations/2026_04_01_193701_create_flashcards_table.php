<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->text('front'); // Question/Term
            $table->text('back'); // Answer/Definition
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->integer('order')->default(0); // Display order
            $table->timestamps();

            $table->index('document_id');
            $table->index(['document_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
