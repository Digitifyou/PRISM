const axios = require('axios');
const OpenAiProvider = require('./OpenAiProvider');
const { Setting } = require('../../models');

class ClaudeProvider {
  async getApiKey() {
    const setting = await Setting.findOne({ where: { key: 'anthropic_api_key' } });
    return setting?.value || process.env.ANTHROPIC_API_KEY;
  }

  async complete(prompt) {
    const apiKey = await this.getApiKey();
    const model = process.env.ANTHROPIC_MODEL || 'claude-sonnet-4-6';

    if (!apiKey) throw new Error('Claude API key is missing');

    const response = await axios.post('https://api.anthropic.com/v1/messages', {
      model: model,
      max_tokens: 2048,
      messages: [{ role: 'user', content: prompt }]
    }, {
      headers: {
        'x-api-key': apiKey,
        'anthropic-version': '2023-06-01',
        'Content-Type': 'application/json'
      }
    });

    return response.data.content[0].text;
  }

  async generateImage(prompt) {
    // Claude does not natively generate images; Fallback to OpenAI DALL-E
    const fallback = new OpenAiProvider();
    return fallback.generateImage(prompt);
  }

  getName() {
    return 'claude';
  }
}

module.exports = ClaudeProvider;
