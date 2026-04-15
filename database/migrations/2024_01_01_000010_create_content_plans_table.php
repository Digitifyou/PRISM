<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->string('niche');
            $table->json('topics');                          // array of planned topics
            $table->string('frequency')->default('weekly'); // daily|weekly
            $table->json('platforms');                       // ["facebook","instagram","linkedin"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_plans');
    }
};
