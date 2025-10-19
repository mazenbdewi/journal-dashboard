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
        Schema::create('review_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();

            // أسئلة نعم / لا مع توضيح
            $table->boolean('page_count_appropriate')->nullable();
            $table->text('page_count_comment')->nullable();

            $table->boolean('titles_match_languages')->nullable();
            $table->text('titles_match_comment')->nullable();

            $table->boolean('objective_clearly_defined')->nullable();
            $table->boolean('objective_achieved')->nullable();
            $table->text('objective_comment')->nullable();

            $table->boolean('all_relevant_references')->nullable();
            $table->boolean('references_are_recent')->nullable();
            $table->text('references_comment')->nullable();

            // القيمة العلمية
            $table->enum('scientific_value', [
                'new_theory_new_results',
                'known_theory_new_results',
                'known_theory_known_results',
                'known_theory_strange_results',
                'strange_theory_strange_results',
            ])->nullable();

            $table->boolean('published_before')->nullable();
            $table->text('published_before_comment')->nullable();

            $table->boolean('results_verifiable')->nullable();
            $table->boolean('results_well_documented')->nullable();
            $table->boolean('results_scientifically_acceptable')->nullable();
            $table->text('results_comment')->nullable();

            $table->string('research_methodology')->nullable();
            $table->boolean('methodology_suitable')->nullable();
            $table->text('methodology_comment')->nullable();

            $table->json('research_significance')->nullable(); // مثل ["knowledge_value", "useful_for_grads", "applied_interest"]

            $table->text('if_weak_comment')->nullable();

            $table->text('comments_for_author')->nullable();
            $table->enum('research_type', ['original', 'not_original'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_evaluations');
    }
};
