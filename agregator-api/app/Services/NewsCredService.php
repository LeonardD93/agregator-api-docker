<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsCredService extends AbstractNewsService
{
    protected $apiKey;

    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('NEWSCRED_KEY');
    }

    // Fetch the latest articles from NewsCred
    public function fetchLatestArticles()
    {
        $endpoint = 'https://newscred.example.com/api/v1/articles'; //TODO TEST and change url
        $params = [
            'api_key' => $this->apiKey,
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status() == 200)
            return $response->json()['articles'];

        \Log::info($response);
        return [];
    }

    // Mapping between NewsCred fields and database fields
    public function getMapping(): array
    {
        return [
            'title' => 'headline',
            'author' => 'byline',
            'content' => 'summary',
            'category' => 'tags', // Assuming tags can serve as categories
            'published_at' => 'publish_date',
            'url' => 'url',
            'source_name' => function () { return 'NewsCred'; }, // Static value
        ];
    }
}
