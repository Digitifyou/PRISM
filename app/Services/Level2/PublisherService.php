<?php

namespace App\Services\Level2;

use App\Models\Post;

class PublisherService
{
    public function publish(Post $post): string
    {
        return match ($post->platform) {
            'facebook'  => (new FacebookPublisher())->publish($post),
            'instagram' => (new InstagramPublisher())->publish($post),
            'linkedin'  => (new LinkedInPublisher())->publish($post),
            default     => throw new \InvalidArgumentException("Unknown platform: {$post->platform}"),
        };
    }
}
