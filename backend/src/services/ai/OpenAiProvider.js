const axios = require('axios');
const { Setting } = require('../../models');

class OpenAiProvider {
  async getApiKey() {
    const setting = await Setting.findOne({ where: { key: 'openai_api_key' } });
    return setting?.value || process.env.OPENAI_API_KEY;
  }

  async complete(prompt) {
    const apiKey = await this.getApiKey();
    const model = process.env.OPENAI_MODEL || 'gpt-4o';

    if (!apiKey) throw new Error('OpenAI API key is missing');

    const response = await axios.post('https://api.openai.com/v1/chat/completions', {
      model: model,
      messages: [{ role: 'user', content: prompt }],
      temperature: 0.7
    }, {
      headers: { Authorization: `Bearer ${apiKey}` }
    });

    return response.data.choices[0].message.content;
  }

  async generateImage(prompt) {
    const apiKey = await this.getApiKey();
    const imageModel = process.env.OPENAI_IMAGE_MODEL || 'dall-e-3';

    if (!apiKey) throw new Error('OpenAI API key is missing');

    const response = await axios.post('https://api.openai.com/v1/images/generations', {
      model: imageModel,
      prompt: prompt,
      n: 1,
      size: '1024x1024',
      quality: 'standard'
    }, {
      headers: { Authorization: `Bearer ${apiKey}` }
    });

    return response.data.data[0].url;
  }

  getName() {
    return 'openai';
  }
}

module.exports = OpenAiProvider;
