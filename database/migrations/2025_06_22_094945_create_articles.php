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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('issue_id')->constrained()->cascadeOnDelete()->nullable();
            $table->unsignedBigInteger('issue_id')->nullable();
            $table->foreign('issue_id')->references('id')->on('issues')->onDelete('cascade');
            $table->string('doi')->unique()->nullable();
            $table->enum('status', [
                'pending',
                'revoke',
                'under_review',
                'accepted',
                'rejected',
                'published',
            ])->default('pending')->index();
            $table->dateTime('published_at')->nullable()->index();
            $table->dateTime('submission_at')->nullable();
            $table->dateTime('acceptance_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['issue_id', 'status']);
            $table->index(['created_by', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
