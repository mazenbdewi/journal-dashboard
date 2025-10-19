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
        Schema::create('issue_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->index();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable()->fulltext();
            $table->timestamps();

            $table->unique(['issue_id', 'locale']);
            $table->index(['title', 'locale']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_translations');
    }
};
