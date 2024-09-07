<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArticleService;
use App\Models\Article;

class ReindexElasticsearchArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reindex-elasticsearch-article';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change the indexing in elasticsearch';


    public function __construct(ArticleService $articleService)
    {
        parent::__construct();
        $this->articleService = $articleService;
        $this->client = $this->articleService->getClient();
    }

    public function handle()
    {
        $indexName = 'articles';

        if ($this->client->indices()->exists(['index' => $indexName])->asBool()) {
            try {
                $this->client->indices()->delete(['index' => $indexName]);
                $this->info("'$indexName' index already exists in Elasticsearch and it was deleted successfully");
            } catch (\Exception $e) {
                $this->error('Error creating index: ' . $e);
            }
        }
        $params = $this->articleService->indexMap($indexName);

        try {
            $response = $this->client->indices()->create($params);
            $this->info('Article index created successfully');
        } catch (\Exception $e) {
            $this->error('Error creating index: ' . $response['error']);
        }
        Article::chunk(100, function ($articles) {
            foreach ($articles as $article) {
                $this->articleService->indexArticle($article, true);
                $this->info('Indexed event ID: ' . $article->id);
            }
        });
    }
}
