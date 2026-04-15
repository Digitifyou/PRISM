<?php

namespace App\Services\Level2;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class InstagramPublisher
{
    private string $accountId;
    private string $accessToken;
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->accountId   = config('services.instagram.account_id', '');
        $this->accessToken = config('services.instagram.access_token', '');
    }

    public function publish(Post $post): string
    {
        if (!$this->accountId || !$this->accessToken) {
            throw new \RuntimeException('Instagram credentials not configured. Add them in Settings.');
        }

        if (!$post->image_url) {
            throw new \RuntimeException(
                'Instagram requires an image. This post has no image_url. ' .
                'Wait for image generation to complete or regenerate the image.'
            );
        }

        $base = "https://graph.facebook.com/{$this->apiVersion}";

        // Step 1: Create media container
        $containerResponse = Http::post("{$base}/{$this->accountId}/media", [
            'image_url'    => $post->image_url,
            'caption'      => $post->caption,
            'access_token' => $this->accessToken,
        ]);

        if ($containerResponse->failed()) {
            $error = $containerResponse->json('error.message') ?? $containerResponse->body();
            throw new \RuntimeException("Instagram container creation failed: {$error}");
        }

        $creationId = $containerResponse->json('id');

        if (!$creationId) {
            throw new \RuntimeException("Instagram did not return a creation_id.");
        }

        // Step 2: Publish the container
        $publishResponse = Http::post("{$base}/{$this->accountId}/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $this->accessToken,
        ]);

        if ($publishResponse->failed()) {
            $error = $publishResponse->json('error.message') ?? $publishResponse->body();
            throw new \RuntimeException("Instagram publish failed: {$error}");
        }

        return $publishResponse->json('id') ?? 'unknown';
    }
}
