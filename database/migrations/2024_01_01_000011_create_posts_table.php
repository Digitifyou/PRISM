<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_plan_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->string('platform');                          // facebook|instagram|linkedin
            $table->text('caption');
            $table->string('image_url')->nullable();
            $table->string('image_prompt')->nullable();          // DALL-E prompt used
            $table->string('status')->default('draft');          // draft|approved|published|failed
            $table->text('research_data')->nullable();           // raw Tavily research
            $table->text('strategy_notes')->nullable();          // GPT-4 strategy output
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
