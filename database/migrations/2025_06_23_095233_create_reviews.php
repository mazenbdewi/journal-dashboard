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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete(); // reviewer = user
            $table->date('review_date')->nullable();
            $table->enum('status', [
                'pending',
                'revoke',
                'under_review',
                'accepted',
                'rejected',
                'published',
            ])->default('pending')->index();
            $table->enum('decision', [
                'accept',
                'minor_revision',
                'major_revision',
                'resubmit_elsewhere',
                'reject',
            ])->nullable(); // القرار النهائي
            $table->text('editor_notes')->nullable(); // خاص بهيئة التحرير
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
