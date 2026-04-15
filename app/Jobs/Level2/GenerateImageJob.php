<?php

namespace App\Jobs\Level2;

use App\Models\Post;
use App\Services\Level2\ImageGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public Post $post) {}

    public function handle(ImageGeneratorService $service): void
    {
        $this->post->loadMissing('contentPlan');

        try {
            $service->generate($this->post);
            Log::info("GenerateImageJob: image created for post #{$this->post->id}");
        } catch (\Throwable $e) {
            // Non-fatal: a post without an image can still be approved/published on FB/LinkedIn
            Log::error("GenerateImageJob failed for post #{$this->post->id}: " . $e->getMessage());
        }
    }
}
