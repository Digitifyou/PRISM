<?php

namespace App\Services\Level1;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ResearcherService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.tavily.api_key');
    }

    public function research(string $topic): string
    {
        if (empty($this->apiKey)) {
            return $this->fallbackResearch($topic);
        }

        $response = Http::withToken($this->apiKey)
            ->post('https://api.tavily.com/search', [
                'query'            => $topic,
                'search_depth'     => 'advanced',
                'include_answer'   => true,
                'include_raw_content' => false,
                'max_results'      => 5,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Tavily API error: ' . $response->body());
        }

        $data    = $response->json();
        $answer  = $data['answer'] ?? '';
        $results = $data['results'] ?? [];

        $summary = "Topic: {$topic}\n\n";
        $summary .= "Summary: {$answer}\n\n";
        $summary .= "Key sources:\n";

        foreach (array_slice($results, 0, 3) as $result) {
            $summary .= "- {$result['title']}: {$result['content']}\n";
        }

        return $summary;
    }

    private function fallbackResearch(string $topic): string
    {
        return "Topic: {$topic}\n\nNo Tavily API key configured. Using topic title as research basis. Add TAVILY_API_KEY to .env for live research.";
    }
}
