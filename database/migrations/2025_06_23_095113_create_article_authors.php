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
        Schema::create('article_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();

            // للمؤلفين المسجلين
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // للمؤلفين غير المسجلين
            $table->string('external_name')->nullable();
            $table->string('external_name_en')->nullable();
            $table->string('external_email')->nullable();
            $table->string('external_affiliation')->nullable();

            $table->boolean('is_main_author')->default(false);
            $table->boolean('is_registered')->default(true);
            $table->timestamps();

            $table->unique(['article_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_authors');
    }
};
