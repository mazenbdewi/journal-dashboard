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
        Schema::create('home_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->default(0);
            $table->string('icon')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('home_stat_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_stat_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('label'); // Students, Courses ...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_stat_translations');
        Schema::dropIfExists('home_stats');
    }
};
