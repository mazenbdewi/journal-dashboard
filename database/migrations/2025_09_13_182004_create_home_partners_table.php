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
        Schema::create('home_partners', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->string('link')->nullable();
            $table->string('image')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('home_partner_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_partner_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_partner_translations');
        Schema::dropIfExists('home_partners');
    }
};
