<?php

namespace App\Console\Commands;

use App\Jobs\Level2\PublishPostJob;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature   = 'posts:publish-scheduled';
    protected $description = 'Dispatch PublishPostJob for approved posts whose scheduled_at is now or past';

    public function handle(): void
    {
        $posts = Post::where('status', Post::STATUS_APPROVED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            return;
        }

        foreach ($posts as $post) {
            // Clear scheduled_at to prevent double-dispatch before job runs
            $post->update(['scheduled_at' => null]);
            PublishPostJob::dispatch($post);
            $this->line("Dispatched PublishPostJob for post #{$post->id} ({$post->platform})");
        }

        $this->info("Dispatched {$posts->count()} scheduled post(s).");
    }
}
