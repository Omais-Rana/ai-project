<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('conversation_id', 36)->nullable()->index();
            $table->text('question');
            $table->longText('answer');
            $table->json('citations')->nullable(); // Array of chunk IDs and metadata
            $table->json('retrieved_chunks')->nullable(); // Top-K chunks used
            $table->decimal('confidence', 3, 2)->nullable(); // 0.00 to 1.00
            $table->integer('tokens_used')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['document_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_questions');
    }
};
