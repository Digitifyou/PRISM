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
        Schema::table('clients', function (Blueprint $table) {
            $table->text('goals')->nullable()->after('target_audience');
            $table->text('competitors')->nullable()->after('goals');
            $table->text('target_audience_demographics')->nullable()->after('competitors');
            $table->text('pain_points')->nullable()->after('target_audience_demographics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['goals', 'competitors', 'target_audience_demographics', 'pain_points']);
        });
    }
};
