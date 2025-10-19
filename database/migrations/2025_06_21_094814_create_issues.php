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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('volume')->index();
            $table->string('number')->index();
            $table->integer('year')->index();
            $table->date('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();

            $table->index(['journal_id', 'year']);
            $table->index(['volume', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
