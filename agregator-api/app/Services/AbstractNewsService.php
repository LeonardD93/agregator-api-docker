<?php

namespace App\Services;

abstract class AbstractNewsService
{
    // Each service must implement this method to fetch the latest articles
    abstract public function fetchLatestArticles();

    // Each service must implement this method to map the external API data to the Article model structure
    abstract public function getMapping(): array;
}