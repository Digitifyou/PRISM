const axios = require('axios');
const { Setting } = require('../../models');

class ResearcherService {
  async research(topic) {
    const setting = await Setting.findOne({ where: { key: 'tavily_api_key' } });
    const apiKey = setting?.value || process.env.TAVILY_API_KEY;

    if (!apiKey) {
      return this.fallbackResearch(topic);
    }

    try {
      const response = await axios.post('https://api.tavily.com/search', {
        query: topic,
        search_depth: 'advanced',
        include_answer: true,
        include_raw_content: false,
        max_results: 5
      }, {
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json'
        }
      });

      const data = response.data;
      const answer = data.answer || '';
      const results = data.results || [];

      let summary = `Topic: ${topic}\n\n`;
      summary += `Summary: ${answer}\n\n`;
      summary += `Key sources:\n`;

      results.slice(0, 3).forEach(result => {
        summary += `- ${result.title}: ${result.content}\n`;
      });

      return summary;
    } catch (e) {
      console.error("Tavily API error:", e.response?.data || e.message);
      throw new Error(`Tavily API error: ${e.message}`);
    }
  }

  fallbackResearch(topic) {
    return `Topic: ${topic}\n\nNo Tavily API key configured. Using topic title as research basis. Add TAVILY_API_KEY to .env for live research.`;
  }
}

module.exports = new ResearcherService();
