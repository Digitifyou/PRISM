<?php

namespace App\Jobs\Level2;

use App\Models\Insight;
use App\Models\Post;
use App\Services\Level2\InsightService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchInsightsJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public Post $post) {}

    public function handle(InsightService $service): void
    {
        try {
            $metrics = $service->fetch($this->post);

            Insight::updateOrCreate(
                ['post_id' => $this->post->id, 'platform' => $this->post->platform],
                array_merge($metrics, ['fetched_at' => now()])
            );

            Log::info("FetchInsightsJob: insights saved for post #{$this->post->id}");
        } catch (\Throwable $e) {
            // Non-fatal: insights can be retried later
            Log::warning("FetchInsightsJob failed for post #{$this->post->id}: " . $e->getMessage());
        }
    }
}
