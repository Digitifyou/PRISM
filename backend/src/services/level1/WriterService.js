const AiProviderFactory = require('../ai/AiProviderFactory');
const { Post } = require('../../models');

class WriterService {
  async write(plan, topic, research, strategy) {
    const ai = await AiProviderFactory.make(plan.ai_provider);
    const posts = [];
    
    // Support eager-loaded client or fallback
    const clientData = plan.Client || plan.client || {};
    const platforms = Array.isArray(plan.platforms) ? plan.platforms : JSON.parse(plan.platforms || '[]');

    for (const platform of platforms) {
      const platformStrategy = strategy[platform] || { tone: 'engaging', hook: 'Check this out:', format: 'tips', cta_type: 'engagement' };
      
      const isGMB = platform === 'google_business';
      const localContext = isGMB ? `
### LOCAL SEO DATA (CRITICAL)
- Primary Location: ${clientData.location || 'Local Area'}
- Targeted Neighborhoods: ${clientData.service_area || 'Surrounding areas'}
` : '';

      const prompt = `You are a master social media copywriter specializing in ${platform}.

Write a high-converting ${platform} post based on the strategy below.

### Client Context
- Brand Name: ${clientData.name || ''}
- Brand Voice: ${clientData.brand_voice || ''}
- Main Goal: ${clientData.goals || ''}
- Target Audience: ${clientData.target_audience || ''} (${clientData.target_audience_demographics || ''})
- Key Pain Points: ${clientData.pain_points || ''}
${localContext}

### Strategy Reference
- Topic: ${topic}
- Main Psychological Angle: ${platformStrategy.psychological_angle || ''}
- Visual Pattern Interrupt: ${platformStrategy.pattern_interrupt || ''}
- Opening Hook: ${platformStrategy.hook || ''}
- Content Format: ${platformStrategy.format || ''}
- Desired CTA: ${platformStrategy.cta_type || ''}

### Raw Research Data
${research}

### High-Performance Writing Rules:
1. **Framework**: Unless specified otherwise, use the **PAS (Problem-Agitate-Solve)** framework for educational posts and **AIDA (Attention-Interest-Desire-Action)** for promotional ones.
2. **The "Anti-AI" Style Guard**:
   - **Strictly BAN**: "In today's fast-paced world", "Unlock the potential", "Navigate the landscape", "Furthermore", "Elevate your strategy".
   - **Tone**: Strictly follow: ${clientData.brand_voice || ''}. No corporate fluff.
3. **The Rhythm of 3**: Vary sentence rhythm. One short sentence (3-5 words). One longer, flowing sentence. One punchy conclusion. 
4. **Hook**: Start exactly with the 'Opening Hook'. Don't bury it. 
5. **Formatting**: Use clean line breaks. Avoid emoji spam (3-5 max).
${isGMB ? `6. **GMB LOCAL POWER RULES**:
   - You MUST weave in mention of "${clientData.location || ''}" and "${clientData.service_area || ''}" naturally.
   - Use 'Near me' contextual phrasing (e.g., 'Looking for [Service] in [City]?').
   - High local relevance is preferred over generic tips.
` : ''}

### THE CREATIVE DIRECTION (Poster Concept)
Determine the most effective visual layout based on topic research. Choose one of:
1. **HOOK**: Massive, visceral 2-5 word headline (e.g. "YOUR BACK IS BROKEN.")
2. **LIST**: Bold headline + 3 valuable expert tips (e.g. "3 SIGNS OF RELAPSE.")
3. **STAT**: High-authority number/stat hero (e.g. "98% SUCCESS RATE.")
4. **POSTER**: Center-aligned professional insight or quote (e.g. "HEALING TAKES TIME.")

### Format Requirements:
Return ONLY a valid JSON object:
- "visual_style": The archetype name (HOOK, LIST, STAT, or POSTER).
- "poster_copy": 
   - HOOK/POSTER: Use "HEADLINE: [Text]" and optional "SUBTEXT: [Text]".
   - LIST: Use "HEADLINE: [Text]" and "POINTS: [P1]; [P2]; [P3]".
   - STAT: Use "BIG_NUMBER: [e.g. 98%]" and "HEADLINE: [Context]".
- "caption": The full social media caption.
Return ONLY JSON. No preamble. No markdown.`;

      let data = { poster_copy: '', caption: '', visual_style: 'HOOK' };
      try {
        let result = await ai.complete(prompt);
        result = result.replace(/```json\s*|\s*```/g, '').trim();
        const parsed = JSON.parse(result) || {};
        
        // Normalize poster_copy with the style tag so the renderer knows what to do
        let rawCopy = parsed.poster_copy;
        if (typeof rawCopy === 'object' && rawCopy !== null) {
            // Flatten object into string format
            rawCopy = Object.entries(rawCopy).map(([key, val]) => `${key.toUpperCase()}: ${val}`).join('\n');
        } else if (!rawCopy) {
            rawCopy = `HEADLINE: ${parsed.headline || ''}`;
        }

        data.poster_copy = `STYLE: ${parsed.visual_style || 'HOOK'}\n${rawCopy}`;
        data.caption = parsed.caption || '';
      } catch (e) {
        console.error(`Writer AI failed for ${platform}:`, e.message);
        data.caption = "Failed to generate content: " + e.message;
      }

      const post = await Post.create({
        client_id: plan.client_id,
        content_plan_id: plan.id,
        topic: topic,
        platform: platform,
        poster_copy: (data.poster_copy || '').trim(),
        caption: (data.caption || '').trim(),
        research_data: research,
        strategy_notes: JSON.stringify(platformStrategy),
        status: 'draft',
      });

      posts.push(post);
    }

    return posts;
  }
}

module.exports = new WriterService();
