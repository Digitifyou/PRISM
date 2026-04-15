<?php

namespace App\Services\Level1;

use App\Models\ContentPlan;
use App\Services\Ai\AiProviderFactory;

class StrategistService
{
    public function analyze(ContentPlan $plan, string $topic, string $research): array
    {
        $ai        = AiProviderFactory::make($plan->ai_provider);
        $platforms = implode(', ', $plan->platforms);
        $client    = $plan->client;

        $prompt = <<<PROMPT
You are a senior social media strategist. Based on the client profile and research below, define a highly targeted content strategy for each platform.

### Client Profile
- Niche: {$plan->niche}
- Goals: {$client->goals}
- Target Audience: {$client->target_audience} ({$client->target_audience_demographics})
- Key Pain Points: {$client->pain_points}
- Competitors: {$client->competitors}

### Current Topic & Research
Topic: {$topic}
Platforms: {$platforms}

Research Data:
{$research}

### Task
For each platform, define a strategy that stops the scroll and drives action.
Define:
- tone: Specific brand voice alignment (e.g., Punchy/Witty; Authoritative/Deep; Empathetic/Direct).
- psychological_angle: (Select ONE: Fear of Missing Out; Professional Authority; Radical Transformation; Human Storytelling; Shared Identity).
- hook: A 'Curiosity Gap' or 'Stop-Your-Scroll' headline. Never start with a generic question.
- pattern_interrupt: A bold statement for the IMAGE/GRAPHIC that creates context or curiosity.
- format: (e.g., 5-part carousel, PAS-copy, story-led insight, myth-buster).
- cta_type: (e.g., site visit, high-value comment, direct DM, bridge to lead magnet).

Return ONLY a valid JSON object. No explanation. No markdown.

Example:
{
  "facebook": {"tone": "punchy-authoritative", "psychological_angle": "Professional Authority", "hook": "The gap between your current SEO and a revenue-driving content moat is exactly 4 steps.", "pattern_interrupt": "YOUR SEO IS LYING TO YOU", "format": "PAS-copy", "cta_type": "comment"},
  "instagram": {"tone": "visual-educational", "psychological_angle": "Radical Transformation", "hook": "Stop chasing 1M viral views. Start building a Content Moat.", "pattern_interrupt": "VIEWS != REVENUE", "format": "5-part carousel", "cta_type": "link-in-bio"}
}
PROMPT;

        $result = $ai->complete($prompt);
        $result = preg_replace('/```json\s*|\s*```/', '', trim($result));
        $strategy = json_decode($result, true);

        if (!is_array($strategy)) {
            $strategy = [
                'facebook'  => ['tone' => 'casual',       'hook' => 'Did you know...', 'format' => 'story'],
                'instagram' => ['tone' => 'inspiring',    'hook' => 'Transform your...', 'format' => 'tips list'],
                'linkedin'  => ['tone' => 'professional', 'hook' => 'Industry insight:', 'format' => 'stat-based'],
            ];
        }

        return $strategy;
    }
}
