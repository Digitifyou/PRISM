<?php

namespace App\Services\Level1;

use App\Models\ContentPlan;
use App\Models\Post;
use App\Services\Ai\AiProviderFactory;

class WriterService
{
    public function write(ContentPlan $plan, string $topic, string $research, array $strategy): array
    {
        $ai      = AiProviderFactory::make($plan->ai_provider);
        $posts   = [];
        $client  = $plan->client;

        foreach ($plan->platforms as $platform) {
            $platformStrategy = $strategy[$platform] ?? ['tone' => 'engaging', 'hook' => 'Check this out:', 'format' => 'tips', 'cta_type' => 'engagement'];

            $prompt = <<<PROMPT
You are a master social media copywriter specializing in {$platform}.

Write a high-converting {$platform} post based on the strategy below.

### Client Context
- Brand Name: {$client->name}
- Brand Voice: {$client->brand_voice}
- Main Goal: {$client->goals}
- Target Audience: {$client->target_audience} ({$client->target_audience_demographics})
- Key Pain Points: {$client->pain_points}

### Strategy Reference
- Topic: {$topic}
- Main Psychological Angle: {$platformStrategy['psychological_angle']}
- Visual Pattern Interrupt: {$platformStrategy['pattern_interrupt']}
- Opening Hook: {$platformStrategy['hook']}
- Content Format: {$platformStrategy['format']}
- Desired CTA: {$platformStrategy['cta_type']}

### Raw Research Data
{$research}

### High-Performance Writing Rules:
1. **Framework**: Unless specified otherwise, use the **PAS (Problem-Agitate-Solve)** framework for educational posts and **AIDA (Attention-Interest-Desire-Action)** for promotional ones.
2. **The "Anti-AI" Style Guard**:
   - **Strictly BAN**: "In today's fast-paced world", "Unlock the potential", "Navigate the landscape", "Furthermore", "Elevate your strategy".
   - **Tone**: Strictly follow: {$client->brand_voice}. No corporate fluff.
3. **The Rhythm of 3**: Vary sentence rhythm. One short sentence (3-5 words). One longer, flowing sentence. One punchy conclusion. 
4. **Hook**: Start exactly with the 'Opening Hook'. Don't bury it. 
5. **Formatting**: Use clean line breaks. Avoid emoji spam (3-5 max).

### THE PSYCHOLOGY OF THE CREATIVE (Poster Copy)
A winning graphic has two parts: The 'Thumb-Stopper' and The 'Bridge'. 
1. MAIN HEADLINE: Must attack a pain point, challenge a common belief, or invoke intense curiosity. Aggressive and visceral. Max 2-5 words.
2. SUBHEADLINE: Must pay off the headline by offering a measurable, tangible outcome. Max 1 short sentence.
Examples (Mediocre vs Elite):
- "Get Better SEO" -> "YOUR SEO IS DEAD."
- "Time Management" -> "THE TIME IS NOW."

            Return ONLY a valid JSON object with the following keys:
            - "poster_copy": Apply elite direct-response copywriting. Format EXACTLY as two lines. Line 1: 'MAIN HEADLINE: [2-5 word thumb-stopper]'. Line 2: 'SUBHEADLINE: [1 sentence tangible payoff]'. NO EMOJIS.
            - "caption": The full, engaging social media caption following the PAS/AIDA framework.

            No preamble. No markdown. No labels.
            PROMPT;

            $result  = $ai->complete($prompt);
            $result  = preg_replace('/```json\s*|\s*```/', '', trim($result));
            $data    = json_decode($result, true) ?? ['poster_copy' => '', 'caption' => $result];

            $post = Post::create([
                'content_plan_id' => $plan->id,
                'topic'           => $topic,
                'platform'        => $platform,
                'poster_copy'     => trim($data['poster_copy'] ?? ''),
                'caption'         => trim($data['caption'] ?? ''),
                'research_data'   => $research,
                'strategy_notes'  => json_encode($platformStrategy),
                'status'          => Post::STATUS_DRAFT,
            ]);

            $posts[] = $post;
        }

        return $posts;
    }
}
