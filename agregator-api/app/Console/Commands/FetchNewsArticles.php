<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArticleService;

class FetchNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        parent::__construct();
        $this->articleService = $articleService;
    }

    public function handle()
    {
        \Log::info("the command FetchNewsArticles is running");
        // List of services, each extending the AbstractNewsService
        $newsServices = [
            // Uncomment or remove services that you don't want to use
            \App\Services\NewsApiService::class, // OK
            // \App\Services\OpenNewsService::class, // not working
            // \App\Services\NewsCredService::class,  // point to optimizely.com
            \App\Services\TheGuardianService::class, // OK    
            \App\Services\NewYorkTimesService::class, //OK
            // \App\Services\BBCNewsService::class,
        ];

        foreach ($newsServices as $serviceClass) {
            // Dynamically resolve the service class
            $this->info("start importing service " . $serviceClass);
            $service = app($serviceClass);
            
            // Fetch the raw articles from the external API
            $articles = $service->fetchLatestArticles();

            // Map the articles to the structure required by the database
            $map = $service->getMapping();

            // Store mapped articles in the database and index them in Elasticsearch
            $this->articleService->storeArticles($articles, $map);
        }

        $this->info('News articles fetched and stored successfully.');
    }
}
