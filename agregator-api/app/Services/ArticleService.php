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
    
        // Filter by keyword (optional)
        if (!empty($filters['keyword'])) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query'  => $filters['keyword'],
                    'fields' => ['title^3', 'content'],
                    'fuzziness' => 'AUTO'
                ]
            ];
        }
    
        // Filter by multiple categories (optional)
        if (!empty($filters['category'])) {
            $query['bool']['filter'][] = [
                'terms' => ['category' => $filters['category']]
            ];
        }
    
        // Filter by multiple sources (optional)
        if (!empty($filters['source_name'])) {
            $query['bool']['filter'][] = [
                'terms' => ['source_name' => $filters['source_name']]
            ];
        }
    
        // Filter by multiple authors (optional)
        if (!empty($filters['author'])) {
            $query['bool']['filter'][] = [
                'terms' => ['author' => $filters['author']]
            ];
        }
    
        // Filter by date range (optional)
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
    
        // Build the Elasticsearch search query
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

    // Method to store articles in the database and index them in Elasticsearch
    public function storeArticles(array $articles, array $mapping)
    {
        foreach ($articles as $articleData) {

            $mappedData = $this->mapArticleFields($articleData, $mapping);
            
            // Check for valid data: Skip the article if the URL, title, or published_at are missing or incorrect
            if (empty($mappedData['url']) || empty($mappedData['title']) || !$this->isValidDate($mappedData['published_at'])) {
                // Optionally, log invalid articles for debugging purposes
                \Log::warning('Invalid article data', ['article_data' => $mappedData]);
                continue; // Skip this article
            }

            // Update or create the article in the database, ensuring uniqueness based on the URL
            $article = Article::updateOrCreate(
                ['url' => $mappedData['url']],  // Ensure URL uniqueness
                [
                    'title' => $mappedData['title'],
                    'author' => $mappedData['author'] ?? '',
                    'content' => $mappedData['content'] ?? 'No content available',
                    'category' => $mappedData['category'] ?? 'General',
                    'published_at' => isset($mappedData['published_at']) 
                        ? Carbon::parse($mappedData['published_at'])->toDateTimeString() 
                        : Carbon::now()->toDateTimeString(),
                    'url' => $mappedData['url'],
                    'source_name' => $mappedData['source_name'],
                ]
            );

            // Index the article into Elasticsearch
            $this->indexArticle($article);
        }
    }

    // Map the article data to the database structure based on the provided mapping
    protected function mapArticleFields(array $articleData, array $mapping): array
    {
        $mappedData = [];

        foreach ($mapping as $dbField => $apiField) {

            if(is_callable($apiField) && $apiField!='url' ) { // special case url is also callable
                $value = call_user_func($apiField);
                $mappedData[$dbField] = $value;
                continue;
            }

            if ($apiField === null) {
                $mappedData[$dbField] = null;
                continue;
            }
            
            $value = $this->getNestedValue($articleData, $apiField);
            $mappedData[$dbField] = $value;
        }

        return $mappedData;
    }

    // Helper function to retrieve nested values from the API response
    protected function getNestedValue(array $data, string $field)
    {
        if (strpos($field, '.') === false) {
            return $data[$field] ?? null;
        }

        $fields = explode('.', $field);
        foreach ($fields as $key) {
            if (!isset($data[$key])) {
                return null;
            }
            $data = $data[$key];
        }

        return $data;
    }

    // Helper function to check if the published_at value is a valid date
    protected function isValidDate($date)
    {
        if (!$date) {
            return false;
        }

        try {
            $carbonDate = Carbon::parse($date);
            // Optionally, exclude specific invalid dates (e.g., 1970-01-01)
            if ($carbonDate->year < 1971) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
