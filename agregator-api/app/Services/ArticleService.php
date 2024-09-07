<?php

namespace App\Services;

// use Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientBuilder;
use App\Models\Article;
use Carbon\Carbon;


class ArticleService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(config('services.elastic.hosts'))->build();
    }

    public function getClient()
    {
        return $this->client;
    }

    public function indexArticle(Article $article, $forceRefresh = false)
    {
        $params = [
            'index' => 'articles',
            'id'    => $article->id,
            'body'  => [
                'title'       => $article->title,
                'author'      => $article->author,
                'content'     => $article->content,
                'category'    => $article->category,
                'published_at'=> Carbon::parse($article->published_at)->toIso8601String(),
                'url'         => $article->url,
                'source_name' => $article->source_name,
            ]
        ];
        if ($forceRefresh)
            $params['refresh'] = true;

        try {
            $response = $this->client->index($params);
            return $response;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function searchArticles($query)
    {
        $params = [
            'index' => 'articles',
            'body'  => [
                'query' => [
                    'match' => ['title' => $query]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            return $response;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function removeArticleFromElasticsearch(Article $article, $forceRefresh = false)
    {
        // Elasticsearch expects the index and the document ID to delete
        $params = [
            'index' => 'articles',   // The index name in Elasticsearch
            'id'    => $article->id, // The ID of the document to delete
            // 'refresh' => $forceRefresh ? 'true' : 'false'
        ];

        try {
            $response = $this->client->delete($params);
            return $response;
        } catch (\Exception $e) {
            // Handle any exception or logging as needed
            throw new \Exception('Failed to delete article from Elasticsearch: ' . $e->getMessage());
        }
    }

    public function indexMap(string $indexName)
    {
        return [
            'index' => $indexName,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'title' => ['type' => 'text'],
                        'author' => ['type' => 'text'],
                        'content' => ['type' => 'text'],
                        'category' => ['type' => 'keyword'],
                        'published_at' => ['type' => 'date'],
                        'url' => ['type' => 'keyword'],
                        'source_name' => ['type' => 'keyword'],
                    ],
                ],
            ],
        ];

    }
   
    public function searchArticlesWithFilters(array $filters, int $from, int $size)
    {
        $query = [
            'bool' => [
                'must' => [],
                'filter' => []
            ]
        ];

        // Filter keyword
        if (!empty($filters['keyword'])) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query'  => $filters['keyword'],
                    'fields' => ['title^3', 'content'],
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        // Filter category
        if (!empty($filters['category'])) {
            $query['bool']['filter'][] = [
                'term' => ['category' => $filters['category']]
            ];
        }

        // Filter source_name
        if (!empty($filters['source_name'])) {
            $query['bool']['filter'][] = [
                'term' => ['source_name' => $filters['source_name']]
            ];
        }

        // filter date range
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $dateRange = [];
            if (!empty($filters['start_date'])) {
                $dateRange['gte'] = Carbon::parse($filters['start_date'])->toDateString();
            }
            if (!empty($filters['end_date'])) {
                $dateRange['lte'] = Carbon::parse($filters['end_date'])->toDateString();
            }

            $query['bool']['filter'][] = [
                'range' => [
                    'published_at' => $dateRange
                ]
            ];
        }

        $params = [
            'index' => 'articles',
            'body'  => [
                'query' => $query,
                'from'  => $from,
                'size'  => $size,
                'sort'  => [
                    'published_at' => ['order' => 'desc']
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('Failed to search articles: ' . $e->getMessage());
        }
    }

    public function getArticleByIdFromElasticsearch($articleId)
    {
        $params = [
            'index' => 'articles',
            'id'    => $articleId
        ];
    
        try {
            return $this->client->get($params);
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve article from Elasticsearch: ' . $e->getMessage());
        }
    }
}
