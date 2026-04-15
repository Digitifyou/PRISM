<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $imageModel;

    public function __construct()
    {
        $this->apiKey     = config('services.gemini.api_key');
        $this->model      = config('services.gemini.model', 'gemini-1.5-pro');
        $this->imageModel = config('services.gemini.image_model', 'imagen-3.0-generate-002');
    }

    public function complete(string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function generateImage(string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->imageModel}:predict?key={$this->apiKey}";

        $response = Http::post($url, [
            'instances' => [
                ['prompt' => $prompt],
            ],
            'parameters' => [
                'sampleCount' => 1,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini Image API error: ' . $response->body());
        }

        // Returns base64 — decode and store via filesystem
        $base64 = $response->json('predictions.0.bytesBase64Encoded');

        return 'data:image/png;base64,' . $base64;
    }

    public function getName(): string
    {
        return 'gemini';
    }
}
