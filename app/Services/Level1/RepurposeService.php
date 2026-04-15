<?php

namespace App\Services\Level1;

use App\Models\Client;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Support\Facades\Http;

class RepurposeService
{
    /**
     * Repurpose content from a URL or raw text into a list of specific social media topics.
     */
    public function repurpose(Client $client, string $source, string $aiProvider = 'openai'): array
    {
        $content = $source;

        // If source looks like a URL, try to fetch it
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::get($source);
                if ($response->successful()) {
                    // Simple HTML to text (could be improved with a dedicated scraper)
                    $content = strip_tags($response->body());
                    // Limit content size for AI context window
                    $content = substr($content, 0, 8000);
                }
            } catch (\Exception $e) {
                // Fallback to source as raw text if fetch fails
            }
        }

        $ai = AiProviderFactory::make($aiProvider);

        $prompt = <<<PROMPT
You are a Content Repurposing Specialist. 

I will provide you with a piece of content (source). Your job is to break it down into 5-7 distinct, high-value social media post ideas ("topics") tailored for the following client.

### Client Context
- Name: {$client->name}
- Industry: {$client->industry}
- Goal: {$client->goals}

### Source Content
{$content}

### Your Task
Identify 5-7 specific angles or topics from this source content that would make great social media posts. 
For each topic, provide a short, descriptive title.

Return ONLY a valid JSON array of strings. No explanation. No markdown.

Example:
["The hidden benefit of X", "How to achieve Y in 3 steps", "Why industry experts are wrong about Z"]
PROMPT;

        $result = $ai->complete($prompt);
        $result = preg_replace('/```json\s*|\s*```/', '', trim($result));
        $topics = json_decode($result, true);

        return is_array($topics) ? $topics : [];
    }
}
