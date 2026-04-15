<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $imageModel;

    public function __construct()
    {
        $this->apiKey     = config('services.openai.api_key');
        $this->model      = config('services.openai.model', 'gpt-4o');
        $this->imageModel = config('services.openai.image_model', 'dall-e-3');
    }

    public function complete(string $prompt): string
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content');
    }

    public function generateImage(string $prompt): string
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'   => $this->imageModel,
                'prompt'  => $prompt,
                'n'       => 1,
                'size'    => '1024x1024',
                'quality' => 'standard',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI Image API error: ' . $response->body());
        }

        return $response->json('data.0.url');
    }

    public function getName(): string
    {
        return 'openai';
    }
}
