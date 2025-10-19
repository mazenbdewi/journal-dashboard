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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('email');
            $table->string('orcid')->unique()->nullable()->after('status');
            $table->text('affiliation')->nullable()->after('orcid');
            $table->text('bio')->nullable()->after('affiliation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['orcid', 'affiliation', 'bio']);

        });
    }
};
