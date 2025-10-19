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
        Schema::create('home_hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('home_hero_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_hero_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_hero_section_translations');
        Schema::dropIfExists('home_hero_sections');
    }
};
