const axios = require('axios');
const AiProviderFactory = require('../ai/AiProviderFactory');

class ClientDiscoveryService {
  async discover(url, aiProviderName = null) {
    const content = await this.scrape(url);
    if (!content) return {};

    const ai = await AiProviderFactory.make(aiProviderName);

    const prompt = `You are an expert Social Media Strategist and Business Analyst. 

I will provide you with the scraped text from a company's website. Your task is to analyze this content and extract a comprehensive strategy framework for their social media management.

### Scraped Website Content:
${content}

### Your Task:
Extract the following details in a structured JSON format:
1. **name**: The official company name.
2. **industry**: The specific industry or vertical (e.g., B2B SaaS, Luxury Real Estate).
3. **brand_voice**: A detailed description of how they should sound on social media (e.g., professional, data-driven, empathetic).
4. **target_audience**: Who is their primary customer?
5. **target_audience_demographics**: Specific age range, location, interests, etc.
6. **goals**: Based on the site, what should be their primary SMM goals? (e.g., lead generation, authority building).
7. **pain_points**: What problems does the company solve for its users?
8. **competitors**: If any competitors are mentioned or implied, list them.

Return ONLY a valid JSON object. No explanation. No markdown.

Example:
{
  "name": "Acme Corp",
  "industry": "Cloud Security",
  "brand_voice": "Authoritative and reliable",
  "target_audience": "IT Managers",
  "target_audience_demographics": "Age 35-50, Fortune 500 companies",
  "goals": "Drive whitepaper downloads",
  "pain_points": "Legacy systems are vulnerable",
  "competitors": "Competitor X, Competitor Y"
}`;

    try {
      let result = await ai.complete(prompt);
      result = result.replace(/```json\s*|\s*```/g, '').trim();
      const data = JSON.parse(result);
      return typeof data === 'object' ? data : {};
    } catch (e) {
      console.error("Client Discovery AI failed:", e.message);
      return {};
    }
  }

  async scrape(url) {
    try {
      const response = await axios.get(url, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        },
        timeout: 10000
      });
      let html = response.data;
      html = html.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, "");
      html = html.replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gi, "");
      const text = html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
      return text.substring(0, 10000);
    } catch (e) {
      console.error(`Scraping failed for ${url}:`, e.message);
      return null;
    }
  }
}

module.exports = new ClientDiscoveryService();
