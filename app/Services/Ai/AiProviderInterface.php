<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * Send a text prompt and get a completion back.
     */
    public function complete(string $prompt): string;

    /**
     * Generate an image from a text prompt.
     * Returns a public URL or base64 string.
     */
    public function generateImage(string $prompt): string;

    /**
     * Return the provider name: openai | gemini | claude
     */
    public function getName(): string;
}
