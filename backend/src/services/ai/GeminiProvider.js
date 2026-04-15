const axios = require('axios');
const { Setting } = require('../../models');

class GeminiProvider {
  async getApiKey() {
    const setting = await Setting.findOne({ where: { key: 'gemini_api_key' } });
    return setting?.value || process.env.GEMINI_API_KEY;
  }

  async complete(prompt) {
    const apiKey = await this.getApiKey();
    const model = process.env.GEMINI_MODEL || 'gemini-1.5-pro';

    if (!apiKey) throw new Error('Gemini API key is missing');

    const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

    const response = await axios.post(url, {
      contents: [{ parts: [{ text: prompt }] }],
      generationConfig: { temperature: 0.7 }
    });

    if (response.data.candidates && response.data.candidates.length > 0) {
      return response.data.candidates[0].content.parts[0].text;
    }
    
    throw new Error('Failed to get completion from Gemini');
  }

  async generateImage(prompt) {
    const apiKey = await this.getApiKey();
    // Use the optimized image model for AI Studio
    const imageModel = process.env.GEMINI_IMAGE_MODEL || 'imagen-3.0-generate-001';

    if (!apiKey) throw new Error('Gemini API key is missing');

    const url = `https://generativelanguage.googleapis.com/v1beta/models/${imageModel}:generateImages?key=${apiKey}`;

    const response = await axios.post(url, {
      prompt: prompt,
      config: {
        number_of_images: 1
      }
    });

    if (response.data.generatedImages && response.data.generatedImages.length > 0) {
      const base64 = response.data.generatedImages[0].image.bytesBase64Encoded;
      return `data:image/png;base64,${base64}`;
    }

    throw new Error('Failed to generate image with Gemini');
  }

  getName() {
    return 'gemini';
  }
}

module.exports = GeminiProvider;
