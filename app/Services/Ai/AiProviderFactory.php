<?php

namespace App\Services\Ai;

use InvalidArgumentException;

class AiProviderFactory
{
    /**
     * Resolve a provider by name.
     * Pass null to use the default from config/services.php (AI_PROVIDER env).
     */
    public static function make(?string $provider = null): AiProviderInterface
    {
        $provider = $provider ?? config('services.ai_provider', 'openai');

        return match ($provider) {
            'openai' => new OpenAiProvider(),
            'gemini' => new GeminiProvider(),
            'claude' => new ClaudeProvider(),
            default  => throw new InvalidArgumentException(
                "Unknown AI provider [{$provider}]. Supported: openai, gemini, claude."
            ),
        };
    }

    /**
     * Return list of all supported provider names.
     */
    public static function supported(): array
    {
        return ['openai', 'gemini', 'claude'];
    }

    /**
     * Return provider display labels for UI dropdowns.
     */
    public static function labels(): array
    {
        return [
            'openai' => 'ChatGPT (GPT-4o)',
            'gemini' => 'Google Gemini',
            'claude' => 'Claude (Anthropic)',
        ];
    }
}
