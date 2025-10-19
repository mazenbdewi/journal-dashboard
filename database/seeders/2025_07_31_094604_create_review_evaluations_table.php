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
        Schema::create('review_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('review_id')->nullable()->constrained()->onDelete('cascade');

            // Answers
            $table->enum('page_count_appropriate', ['yes', 'no'])->nullable();
            $table->text('page_count_reason')->nullable();
            $table->enum('title_match', ['yes', 'no'])->nullable();
            $table->text('title_match_reason')->nullable();
            $table->enum('research_goal_clear', ['yes', 'no'])->nullable();
            $table->enum('research_goal_achieved', ['yes', 'no'])->nullable();
            $table->text('goal_not_achieved_reason')->nullable();
            $table->enum('relevant_references', ['yes', 'no'])->nullable();
            $table->enum('references_relevance', ['yes', 'no'])->nullable();
            $table->text('references_shortcomings')->nullable();
            $table->enum('knowledge_value', [
                'new_theory_new_results',
                'known_theory_new_results',
                'known_theory_known_results',
                'known_theory_strange_results',
                'strange_theory_strange_results',
            ])->nullable();
            $table->enum('previously_published', ['yes', 'no'])->nullable();
            $table->text('publication_details')->nullable();
            $table->enum('verifiable_results', ['yes', 'no'])->nullable();
            $table->enum('results_documented', ['yes', 'no'])->nullable();
            $table->enum('results_reliable', ['yes', 'no'])->nullable();
            $table->text('unreliable_reason')->nullable();
            $table->string('research_methodology')->nullable();
            $table->enum('methodology_appropriate', ['yes', 'no'])->nullable();
            $table->text('methodology_notes')->nullable();
            $table->json('research_value')->nullable();
            $table->text('low_value_reason')->nullable();
            $table->text('researcher_notes')->nullable();
            $table->enum('research_type', [
                'original',
                'non_original',
            ])->nullable();
            $table->enum('decision', [
                'publish_without_modification',
                'minor_revisions_required',
                'resubmit_for_review',
                'resubmit_elsewhere',
                'reject',
            ])->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('editorial_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_forms');
    }
};
