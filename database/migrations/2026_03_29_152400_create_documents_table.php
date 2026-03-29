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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('filename'); // Stored filename
            $table->string('original_name'); // Original upload name
            $table->string('file_path'); // Storage path
            $table->string('file_type', 10); // pdf, docx, txt
            $table->integer('file_size'); // Size in bytes
            $table->integer('total_chunks')->default(0);
            $table->enum('status', ['uploading', 'processing', 'completed', 'failed'])->default('uploading');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
