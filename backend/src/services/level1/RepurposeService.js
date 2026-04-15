const axios = require('axios');
const AiProviderFactory = require('../ai/AiProviderFactory');

class RepurposeService {
  async repurpose(client, source, aiProviderName = 'openai') {
    let content = source;

    // Very simple URL check
    if (/^https?:\/\//i.test(source)) {
      try {
        const response = await axios.get(source, { timeout: 10000 });
        if (response.data) {
          let html = response.data;
          html = html.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, "");
          html = html.replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gi, "");
          content = html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
          content = content.substring(0, 8000);
        }
      } catch (e) {
        // Fallback to raw text
        console.error("Repurpose failed to fetch URL:", e.message);
      }
    }

    const ai = await AiProviderFactory.make(aiProviderName);

    const prompt = `You are a Content Repurposing Specialist. 

I will provide you with a piece of content (source). Your job is to break it down into 5-7 distinct, high-value social media post ideas ("topics") tailored for the following client.

### Client Context
- Name: ${client.name || ''}
- Industry: ${client.industry || ''}
- Goal: ${client.goals || ''}

### Source Content
${content}

### Your Task
Identify 5-7 specific angles or topics from this source content that would make great social media posts. 
For each topic, provide a short, descriptive title.

Return ONLY a valid JSON array of strings. No explanation. No markdown.

Example:
["The hidden benefit of X", "How to achieve Y in 3 steps", "Why industry experts are wrong about Z"]`;

    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      const topics = JSON.parse(result);
      return Array.isArray(topics) ? topics : [];
    } catch (e) {
      console.error("Repurpose AI failed:", e.message);
      return [];
    }
  }
}

module.exports = new RepurposeService();
