const GeminiProvider = require('./GeminiProvider');
const OpenAiProvider = require('./OpenAiProvider');
const ClaudeProvider = require('./ClaudeProvider');
const { Setting } = require('../../models');

class AiProviderFactory {
  static async make(providerName = null) {
    if (!providerName) {
      const setting = await Setting.findOne({ where: { key: 'ai_provider' } });
      providerName = setting?.value || process.env.AI_PROVIDER || 'gemini';
    }

    switch (providerName) {
      case 'openai': return new OpenAiProvider();
      case 'gemini': return new GeminiProvider();
      case 'claude': return new ClaudeProvider();
      default:
        throw new Error(`Unknown AI provider [${providerName}]. Supported: openai, gemini, claude.`);
    }
  }

  static supported() {
    return ['openai', 'gemini', 'claude'];
  }

  static labels() {
    return {
      openai: 'ChatGPT (GPT-4o)',
      gemini: 'Google Gemini',
      claude: 'Claude (Anthropic)'
    };
  }
}

module.exports = AiProviderFactory;
