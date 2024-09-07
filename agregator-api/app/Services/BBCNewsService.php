<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BBCNewsService extends AbstractNewsService
{
    protected $apiKey;

    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('BBCNEWS_KEY');
    }

    // Fetch the latest articles from BBC News API
    public function fetchLatestArticles()
    {
        $endpoint = 'https://bbc.example.com/api/news'; // TODO TEST
        $params = [
            'api_key' => $this->apiKey,
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status() == 200)
            return $response->json()['articles'];

        \Log::info($response);
        return [];
    }

    // Mapping between BBC News API fields and database fields
    public function getMapping(): array
    {
        return [
            'title' => 'headline',
            'author' => 'author',
            'content' => 'description',
            'category' => null, // No category mapping available
            'published_at' => 'published_at',
            'url' => 'url',
            'source_name' => function () { return 'BBC News'; }, // Static value
        ];
    }
}
