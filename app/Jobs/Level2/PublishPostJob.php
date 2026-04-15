<?php

namespace App\Jobs\Level2;

use App\Models\Post;
use App\Services\Level2\PublisherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PublishPostJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(public Post $post) {}

    public function handle(PublisherService $publisher): void
    {
        // Don't re-publish if already published (safety guard)
        $this->post->refresh();
        if ($this->post->isPublished()) {
            return;
        }

        try {
            $platformPostId = $publisher->publish($this->post);

            $this->post->update([
                'status'           => Post::STATUS_PUBLISHED,
                'platform_post_id' => $platformPostId,
                'published_at'     => now(),
                'failure_reason'   => null,
            ]);

            Log::info("PublishPostJob: post #{$this->post->id} published to {$this->post->platform} as {$platformPostId}");

            // Fetch insights 30 minutes later once the platform processes the post
            FetchInsightsJob::dispatch($this->post)->delay(now()->addMinutes(30));

        } catch (\Throwable $e) {
            Log::error("PublishPostJob: failed for post #{$this->post->id}: " . $e->getMessage());

            $this->post->update([
                'status'         => Post::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);

            // Let Laravel handle retry logic; on final failure, job goes to failed_jobs
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                throw $e; // Allow retry
            }
        }
    }
}
