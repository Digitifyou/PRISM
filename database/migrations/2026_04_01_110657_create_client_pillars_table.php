<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_pillars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('title'); // e.g., "Educational - SEO Tips"
            $table->text('description')->nullable(); // Detailed instructions for the AI on this pillar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_pillars');
    }
};
