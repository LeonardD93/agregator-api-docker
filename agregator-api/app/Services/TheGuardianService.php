<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TheGuardianService extends AbstractNewsService
{
    protected $apiKey;

    public function __construct()
    {
        // Set API key directly from .env
        $this->apiKey = env('THEGUARDIAN_KEY');
    }

    // Fetch the latest articles from The Guardian API
    public function fetchLatestArticles()
    {
        $endpoint = 'https://content.guardianapis.com/search';
        $params = [
            'api-key' => $this->apiKey,
            // 'section' => 'technology',
        ];

        $response = Http::get($endpoint, $params);

        if ($response->status()==200)
            return $response->json()['response']['results'];

        \Log::info($response);
        return [];
    }

    // Map the articles from The Guardian to the Article model structure
    public function getMapping(): array
    {
        return [
            'title' => 'webTitle',
            'author' => null, // The Guardian API might not provide the author field
            'content' => null, // The content might not be provided
            'category' => 'sectionName',
            'published_at' => 'webPublicationDate',
            'url' => 'webUrl',
            'source_name' => function(){ return 'The Guardian'; }, // Static value since source is known
        ];
    }
}