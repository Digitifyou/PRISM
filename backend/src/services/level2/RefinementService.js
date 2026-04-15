const AiProviderFactory = require('../ai/AiProviderFactory');
const { Post, ContentPlan, Client } = require('../../models');
const PosterCompositeService = require('./PosterCompositeService');
const ImageGeneratorService = require('./ImageGeneratorService');

class RefinementService {
  async refine(post, instruction = '') {
    // 1. Fetch full context
    const fullPost = await Post.findByPk(post.id, {
      include: [
        { model: ContentPlan },
        { model: Client }
      ]
    });

    if (!fullPost) throw new Error('Post not found in database');

    // Robust client lookup
    let client = fullPost.Client;
    if (!client && fullPost.client_id) {
        client = await Client.findByPk(fullPost.client_id);
    }
    if (!client && fullPost.ContentPlan) {
        client = await Client.findByPk(fullPost.ContentPlan.client_id);
    }

    if (!client) throw new Error('Client context not found. Ensure this post is linked to a valid client.');

    const aiProvider = fullPost.ContentPlan?.ai_provider || 'gemini';
    const ai = await AiProviderFactory.make(aiProvider);

    // 2. Build the Refinement Prompt
    const prompt = `You are a world-class social media performance designer and copywriter. Your goal is to refine and improve the following social media post.

### Original Content
- Platform: ${fullPost.platform}
- Graphic Headline: ${fullPost.poster_copy}
- Caption: ${fullPost.caption}
- Current Visual Concept: ${fullPost.image_prompt || 'N/A'}

### Context
- Brand: ${client.name}
- Brand Voice: ${client.brand_voice}
- Main Goal: ${client.goals}
- Target Audience: ${client.target_audience}

### REFINEMENT INSTRUCTION
${instruction ? `SPECIFIC DIRECTION: ${instruction}` : `GENERAL DIRECTION: Perform an elite copywriting polish and ensure the visual design concept is world-class.`}

### Requirements:
1. **Maintain the Platform Style**: Ensure it remains perfectly optimized for ${fullPost.platform}.
2. **Follow Writing Rules**: No corporate fluff. Use PAS or AIDA frameworks.
3. **Visual Design**: If the instruction implies a change in background or style, provide a new 'image_prompt'.
4. **Format**: Return ONLY a valid JSON object.

Example JSON output:
{
  "poster_copy": "MAIN HEADLINE: [New Hook]\\nSUBHEADLINE: [New Payoff]",
  "caption": "[New high-performance caption]",
  "image_prompt": "[Highly descriptive background prompt - no text]",
  "visual_refinement_needed": true/false
}

Return ONLY the JSON. No preamble. No markdown.`;

    // 3. Execute AI Completion
    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      const refinedData = JSON.parse(result);
      
      // 4. Update the post text fields
      await fullPost.update({
        caption: refinedData.caption,
        poster_copy: refinedData.poster_copy,
        image_prompt: refinedData.image_prompt || fullPost.image_prompt
      });

      // 5. Detect if we need a totally new background image
      if (refinedData.visual_refinement_needed) {
          console.log('Visual refinement requested. Triggering new background generation...');
          await ImageGeneratorService.generate(fullPost);
      } else {
          // If only text changed, just re-composite on the existing background
          console.log('Text refinement only. Re-compositing typography...');
          const finalUrl = await PosterCompositeService.composite(fullPost, fullPost.image_url);
          await fullPost.update({ image_url: finalUrl });
      }

      return fullPost;
    } catch (e) {
      console.error('Refinement AI failed:', e.message);
      throw new Error('AI Refinement failed: ' + e.message);
    }
  }
}

module.exports = new RefinementService();
