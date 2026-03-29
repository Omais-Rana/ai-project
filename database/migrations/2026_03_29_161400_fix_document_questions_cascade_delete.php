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
        Schema::table('document_questions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['document_id']);

            // Add the new foreign key constraint with cascade delete
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_questions', function (Blueprint $table) {
            // Drop the cascade foreign key
            $table->dropForeign(['document_id']);

            // Restore the original set null foreign key
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('set null');
        });
    }
};
