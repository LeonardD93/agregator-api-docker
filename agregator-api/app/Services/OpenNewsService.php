<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenNewsService extends AbstractNewsService
{
    protected $apiKey;

    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('OPENNEWS_KEY');
    }

    // Fetch the latest articles from OpenNews
    public function fetchLatestArticles()
    {
        $endpoint = 'https://opennews.example.com/v1/articles'; //TODO TEST and change url
        $params = [
            'apiKey' => $this->apiKey,
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status() == 200)
            return $response->json()['articles'];

        \Log::info($response);
        return [];
    }

    // Mapping between OpenNews fields and database fields
    public function getMapping(): array
    {
        return [
            'title' => 'headline',
            'author' => 'author.name', // Nested field
            'content' => 'summary',
            'category' => 'category',
            'published_at' => 'published_date',
            'url' => 'url',
            'source_name' => function () { return 'OpenNews'; }, // Static value
        ];
    }
}
