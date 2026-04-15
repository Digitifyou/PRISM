<?php

namespace App\Services\Level1;

use App\Models\ContentPlan;
use App\Services\Ai\AiProviderFactory;

class PlannerService
{
    public function generate(ContentPlan $plan): array
    {
        $ai        = AiProviderFactory::make($plan->ai_provider);
        $platforms = implode(', ', $plan->platforms);
        $count     = match ($plan->frequency) {
            'daily'  => 7,
            default  => 4,
        };

        $prompt = <<<PROMPT
You are a social media content strategist.

Generate {$count} unique content topic ideas for the niche: "{$plan->niche}".
These topics will be posted on: {$platforms}.
Frequency: {$plan->frequency}.

Rules:
- Each topic must be specific, engaging, and relevant to the niche.
- Topics should vary: tips, stories, questions, trends, behind-the-scenes.
- Return ONLY a valid JSON array of strings. No explanation. No markdown.

Example output:
["Topic 1", "Topic 2", "Topic 3"]
PROMPT;

        $result = $ai->complete($prompt);

        // Strip markdown code fences if present
        $result = preg_replace('/```json\s*|\s*```/', '', trim($result));

        $topics = json_decode($result, true);

        if (!is_array($topics)) {
            $topics = ["Content tips for {$plan->niche}", "Why {$plan->niche} matters", "Top trends in {$plan->niche}", "Getting started with {$plan->niche}"];
        }

        $plan->update(['topics' => $topics]);

        return $topics;
    }
}
