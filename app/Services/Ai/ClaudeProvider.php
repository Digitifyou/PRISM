<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
        $this->model  = config('services.claude.model', 'claude-sonnet-4-6');
    }

    public function complete(string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 2048,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Claude API error: ' . $response->body());
        }

        return $response->json('content.0.text');
    }

    public function generateImage(string $prompt): string
    {
        // Claude does not natively generate images.
        // Falls back to OpenAI DALL-E for image generation.
        $fallback = new OpenAiProvider();

        return $fallback->generateImage($prompt);
    }

    public function getName(): string
    {
        return 'claude';
    }
}
