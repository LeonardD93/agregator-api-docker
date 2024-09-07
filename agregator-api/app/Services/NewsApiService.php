<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsApiService extends AbstractNewsService
{
    protected $apiKey;
    
    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('NEWSAPI_KEY');
    }

    // Fetch the latest articles from the NewsAPI
    public function fetchLatestArticles()
    {
        $endpoint = 'https://newsapi.org/v2/top-headlines';
        $params = [
            'apiKey' => $this->apiKey,
            'language' => 'en',
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status()==200)
            return $response->json()['articles'];

        \Log::info($response);
        return [];
    }

    // Map the articles from NewsAPI to the Article model structure
    public function getMapping(): array
    {
        return [
            'title' => 'title',
            'author' => 'author',
            'content' => 'description', // Content comes from 'description'
            'category' => 'category',   // Assuming the API has a 'category' field
            'published_at' => 'publishedAt',
            'url' => "url",
            'source_name' => 'source.name', // Nested field
        ];
    }
}
