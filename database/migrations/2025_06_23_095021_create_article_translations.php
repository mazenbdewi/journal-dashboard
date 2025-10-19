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
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->index();
            $table->string('title')->index();
            $table->string('slug')->unique()->nullable();
            $table->text('abstract')->nullable()->fulltext();
            $table->longText('content')->nullable();
            $table->text('keywords')->nullable()->fulltext();
            $table->timestamps();

            $table->unique(['article_id', 'locale']);
            $table->unique(['title', 'locale']);

            $table->index(['title', 'locale']);
            $table->index(['article_id', 'locale', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_translations');
    }
};
