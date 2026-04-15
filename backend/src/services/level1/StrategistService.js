const AiProviderFactory = require('../ai/AiProviderFactory');

class StrategistService {
  async analyze(plan, topic, research) {
    const ai = await AiProviderFactory.make(plan.ai_provider);
    const platformArr = Array.isArray(plan.platforms) ? plan.platforms : JSON.parse(plan.platforms || '[]');
    const platforms = platformArr.join(', ');
    
    // Support either plan.client eager loaded or fallback
    const clientData = plan.Client || plan.client || {};

    const prompt = `You are a senior social media strategist. Based on the client profile and research below, define a highly targeted content strategy for each platform.

### Client Profile
- Niche: ${plan.niche || ''}
- Goals: ${clientData.goals || ''}
- Target Audience: ${clientData.target_audience || ''} (${clientData.target_audience_demographics || ''})
- Key Pain Points: ${clientData.pain_points || ''}
- Competitors: ${clientData.competitors || ''}

### Current Topic & Research
Topic: ${topic}
Platforms: ${platforms}

Research Data:
${research}

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
}`;

    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      let strategy = JSON.parse(result);
      if (typeof strategy !== 'object') throw new Error('Not an object');
      return strategy;
    } catch (e) {
      console.error("Strategist AI failed:", e.message);
      return {
        facebook: { tone: 'casual', hook: 'Did you know...', format: 'story' },
        instagram: { tone: 'inspiring', hook: 'Transform your...', format: 'tips list' },
        linkedin: { tone: 'professional', hook: 'Industry insight:', format: 'stat-based' },
      };
    }
  }
}

module.exports = new StrategistService();
