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
        Schema::table('content_plans', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('client_pillar_id')->nullable()->constrained()->onDelete('cascade');
            // Niche was formally required, we'll make it nullable so old data doesn't break,
            // but going forward we rely on client/pillar contextual rules instead of abstract typed niches.
            $table->string('niche')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_plans', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['client_pillar_id']);
            $table->dropColumn(['client_id', 'client_pillar_id']);
            $table->string('niche')->nullable(false)->change();
        });
    }
};
