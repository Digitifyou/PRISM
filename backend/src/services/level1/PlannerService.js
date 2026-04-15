const AiProviderFactory = require('../ai/AiProviderFactory');

class PlannerService {
  async generate(plan) {
    const ai = await AiProviderFactory.make(plan.ai_provider);
    const platformArr = Array.isArray(plan.platforms) ? plan.platforms : JSON.parse(plan.platforms || '[]');
    const platforms = platformArr.join(', ');
    let count = 4;
    if (plan.frequency === 'daily') count = 7;

    const prompt = `You are a social media content strategist.

Generate ${count} unique content topic ideas for the niche: "${plan.niche || ''}".
These topics will be posted on: ${platforms}.
Frequency: ${plan.frequency || ''}.

Rules:
- Each topic must be specific, engaging, and relevant to the niche.
- Topics should vary: tips, stories, questions, trends, behind-the-scenes.
- Return ONLY a valid JSON array of strings. No explanation. No markdown.

Example output:
["Topic 1", "Topic 2", "Topic 3"]`;

    let topics = [];
    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      topics = JSON.parse(result);
      if (!Array.isArray(topics)) throw new Error('Not an array');
    } catch (e) {
      console.error("Planner AI failed:", e.message);
      topics = [
        `Content tips for ${plan.niche || 'your industry'}`,
        `Why ${plan.niche || 'this topic'} matters`,
        `Top trends in ${plan.niche || 'your niche'}`,
        `Getting started with ${plan.niche || 'your niche'}`
      ];
    }

    await plan.update({ topics });
    return topics;
  }
}

module.exports = new PlannerService();
