<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewYorkTimesService extends AbstractNewsService
{
    protected $apiKey;

    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('NYTIMES_KEY');
    }

    // Fetch the latest articles from New York Times API
    public function fetchLatestArticles()
    {
        $endpoint = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
        $params = [
            'api-key' => $this->apiKey,
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status() == 200)
            return $response->json()['response']['docs'];

        \Log::info($response);
        return [];
    }

    // Mapping between New York Times API fields and database fields
    public function getMapping(): array
    {
        return [
            'title' => 'abstract',
            'author' => 'byline.original',
            'content' => 'lead_paragraph',
            'category' => 'section_name',
            'published_at' => 'pub_date',
            'url' => 'web_url',
            'source_name' => 'source',
        ];
    }
}
