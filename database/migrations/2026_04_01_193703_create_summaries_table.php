<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('length_mode'); // tldr, brief, detailed
            $table->text('content');
            $table->integer('word_count')->default(0);
            $table->timestamps();

            $table->index('document_id');
            $table->unique(['document_id', 'length_mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
