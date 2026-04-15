const AiProviderFactory = require('../ai/AiProviderFactory');

class PillarGeneratorService {
  async generate(client, aiProviderName = null) {
    const ai = await AiProviderFactory.make(aiProviderName);

    const prompt = `You are a world-class Social Media Strategist. Your goal is to define the "Architectural Pillars" for a client's social media strategy. 

These pillars represent the core themes that will guide all content generation. You must suggest 4 distinct pillars that balance educational value, authority building, and lead generation.

### Client Profile:
- **Name**: ${client.name || ''}
- **Industry**: ${client.industry || ''}
- **Goals**: ${client.goals || ''}
- **Target Audience**: ${client.target_audience_demographics || ''}
- **Pain Points**: ${client.pain_points || ''}
- **Brand Voice**: ${client.brand_voice || ''}

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
]`;

    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      const data = JSON.parse(result);
      return Array.isArray(data) ? data : [];
    } catch (e) {
      console.error("Pillar Generation AI failed:", e.message);
      return [];
    }
  }
}

module.exports = new PillarGeneratorService();
