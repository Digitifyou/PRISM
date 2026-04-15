<?php

namespace App\Services\Level2;

use App\Models\Post;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageGeneratorService
{
    public function generate(Post $post): void
    {
        $post->loadMissing('contentPlan');

        $ai = AiProviderFactory::make($post->contentPlan->ai_provider);

        $platformSizes = [
            'facebook'  => '1200x630',
            'instagram' => '1080x1080 square',
            'linkedin'  => '1200x627',
        ];

        $sizeHint = $platformSizes[$post->platform] ?? '1200x630';

        $prompt = "Create a professional, eye-catching social media image for {$post->platform}. "
            . "Topic: {$post->topic}. Niche: {$post->contentPlan->niche}. "
            . "Size: {$sizeHint}. Style: modern, clean, vibrant, no text overlay. "
            . "Suitable for a brand social media post.";

        /*
        $result = $ai->generateImage($prompt);

        // Handle base64 data URI (Gemini) vs CDN URL (DALL-E / OpenAI)
        if (str_starts_with($result, 'data:image/')) {
            $url = $this->storeBase64($result, $post->id);
        } else {
            // DALL-E CDN URLs expire in ~1 hour — download immediately
            $url = $this->downloadAndStore($result, $post->id);
        }

        $post->update([
            'image_url'    => $url,
            'image_prompt' => $prompt,
        ]);
        */
    }

    private function storeBase64(string $dataUri, int $postId): string
    {
        $base64 = substr($dataUri, strpos($dataUri, ',') + 1);
        $binary = base64_decode($base64);
        $path   = "post-images/{$postId}-" . uniqid() . ".png";

        Storage::disk('public')->put($path, $binary);

        return Storage::disk('public')->url($path);
    }

    private function downloadAndStore(string $url, int $postId): string
    {
        try {
            $binary = Http::timeout(30)->get($url)->body();
        } catch (\Throwable $e) {
            Log::warning("ImageGeneratorService: failed to download image from {$url}: " . $e->getMessage());
            // Return the original URL as fallback — it may still be accessible for a while
            return $url;
        }

        $path = "post-images/{$postId}-" . uniqid() . ".jpg";
        Storage::disk('public')->put($path, $binary);

        return Storage::disk('public')->url($path);
    }
}
