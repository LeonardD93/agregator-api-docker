<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ArticleService;
use App\Models\Article;
use Carbon\Carbon;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Response\Elasticsearch as ElasticSearchResponse;

class ElasticsearchServiceTest extends TestCase
{
    protected $client;
    protected $articleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleService = new ArticleService();
        $this->client = $this->articleService->getClient();
    }

    public function test_elasticsearch_is_reachable()
    {
        $response = $this->client->ping();
        $this->assertTrue($response->getStatusCode()==200);
    }


    public function test_index_article_in_elasticsearch()
    {
        $article = new Article([
            'title'       => 'Test Article',
            'author'      => 'John Doe',
            'content'     => 'This is a test article.',
            'category'    => 'Testing',
            'published_at'=> Carbon::now()->toDateTimeString(),
            'url'         => 'https://example.com/test-article',
            'source_name' => 'Test Source',
        ]);
        $article->save();
        
        $response = $this->articleService->indexArticle($article, true);
        if(gettype($response)=='object')
            $response = $response->asArray();

        if (isset($response['error'])) {
            $this->fail('Failed to index article: ' . $response['error']);
        }

        $this->assertArrayHasKey('_id', $response);
        $this->assertEquals($article->id, $response['_id']);
    }

    public function test_search_article_in_elasticsearch()
    {
        $query = 'Test Article';
        $results = $this->articleService->searchArticles($query);

        if(gettype($results)=='object')
            $results = $results->asArray();
        
        if (isset($results['error'])) {
            $this->fail('Failed to search articles: ' . $results['error']);
        }
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, $results['hits']['total']['value']);
        $this->assertEquals('Test Article', $results['hits']['hits'][0]['_source']['title']);

        // clean database from test data
        $articles = Article::where('title','Test Article')->get();   
        foreach($articles as $article) {
            $this->articleService->removeArticleFromElasticsearch($article, true);
            $article->delete();
        }
    }
}
