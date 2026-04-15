<?php

namespace App\Services\Level2;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class FacebookPublisher
{
    private string $pageId;
    private string $accessToken;
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->pageId      = config('services.facebook.page_id', '');
        $this->accessToken = config('services.facebook.access_token', '');
    }

    public function publish(Post $post): string
    {
        if (!$this->pageId || !$this->accessToken) {
            throw new \RuntimeException('Facebook credentials not configured. Add them in Settings.');
        }

        $base = "https://graph.facebook.com/{$this->apiVersion}";

        if ($post->image_url) {
            // Post with image
            $response = Http::post("{$base}/{$this->pageId}/photos", [
                'url'          => $post->image_url,
                'caption'      => $post->caption,
                'access_token' => $this->accessToken,
            ]);
        } else {
            // Text-only post
            $response = Http::post("{$base}/{$this->pageId}/feed", [
                'message'      => $post->caption,
                'access_token' => $this->accessToken,
            ]);
        }

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("Facebook API error: {$error}");
        }

        return $response->json('id') ?? $response->json('post_id') ?? 'unknown';
    }
}
