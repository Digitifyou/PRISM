<?php

namespace App\Services\Level1;

use App\Models\Client;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Support\Facades\Log;

class PillarGeneratorService
{
    /**
     * Generate strategic content pillars based on client identity.
     */
    public function generate(Client $client, ?string $aiProvider = null): array
    {
        $ai = AiProviderFactory::make($aiProvider);

        $prompt = <<<PROMPT
You are a world-class Social Media Strategist. Your goal is to define the "Architectural Pillars" for a client's social media strategy. 

These pillars represent the core themes that will guide all content generation. You must suggest 4 distinct pillars that balance educational value, authority building, and lead generation.

### Client Profile:
- **Name**: {$client->name}
- **Industry**: {$client->industry}
- **Goals**: {$client->goals}
- **Target Audience**: {$client->target_audience_demographics}
- **Pain Points**: {$client->pain_points}
- **Brand Voice**: {$client->brand_voice}

### Your Task:
Suggest 4 strategic content pillars. For each pillar, provide:
1. **title**: A punchy, descriptive name (e.g. "Authority Clips", "The Problem Solver", "Behind the Scenes").
2. **description**: Detailed AI instructions on what this pillar focuses on, the tone, and the goal.

### Output Format:
Return ONLY a valid JSON array of objects. No preamble, no markdown.

Example:
[
  {
    "title": "Educational Deep-Dives",
    "description": "Focus on solving [Pain Point] with step-by-step guides. Use a helpful, authoritative tone."
  },
  {
    "title": "Client Success Stories",
    "description": "Highlight transformations and social proof. Goal is to build trust and authority."
  }
]
PROMPT;

        try {
            $result = $ai->complete($prompt);
            $result = preg_replace('/```json\s*|\s*```/', '', trim($result));
            $data = json_decode($result, true);

            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error("Pillar Generation AI failed: " . $e->getMessage());
            return [];
        }
    }
}
