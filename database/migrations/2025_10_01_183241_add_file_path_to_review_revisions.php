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
        Schema::table('review_revisions', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('notes_for_editor');
            $table->text('note')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_revisions', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'note']);
        });
    }
};
