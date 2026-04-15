<?php

namespace App\Services\Level1;

use App\Services\Ai\AiProviderFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientDiscoveryService
{
    /**
     * Discover client details from a website URL.
     */
    public function discover(string $url, ?string $aiProvider = null): array
    {
        $content = $this->scrape($url);

        if (!$content) {
            return [];
        }

        $ai = AiProviderFactory::make($aiProvider);

        $prompt = <<<PROMPT
You are an expert Social Media Strategist and Business Analyst. 

I will provide you with the scraped text from a company's website. Your task is to analyze this content and extract a comprehensive strategy framework for their social media management.

### Scraped Website Content:
{$content}

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
}
PROMPT;

        try {
            $result = $ai->complete($prompt);
            $result = preg_replace('/```json\s*|\s*```/', '', trim($result));
            $data = json_decode($result, true);

            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error("Client Discovery AI failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Simple HTML to Text scraper.
     */
    protected function scrape(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get($url);

            if ($response->successful()) {
                $html = $response->body();
                
                // Remove scripts and styles
                $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
                $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $html);
                
                $text = strip_tags($html);
                $text = preg_replace('/\s+/', ' ', $text); // Clean up whitespace
                
                return substr(trim($text), 0, 10000); // Limit context size
            }
        } catch (\Exception $e) {
            Log::error("Scraping failed for {$url}: " . $e->getMessage());
        }

        return null;
    }
}
