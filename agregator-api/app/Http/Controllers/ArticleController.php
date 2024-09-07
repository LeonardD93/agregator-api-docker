<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @OA\Post(
     *     path="/api/articles/search",
     *     summary="Search articles with filters",
     *     description="Performs a search for articles based on the provided filters.",
     *     operationId="searchArticles",
     *     tags={"Articles"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Search filters",
     *         @OA\JsonContent(
     *             @OA\Property(property="page", type="integer", example=1, description="Page number for pagination (optional)"),
     *             @OA\Property(property="keyword", type="string", example="technology", description="Keyword to search articles (optional)"),
     *             @OA\Property(property="category", type="string", example="news", description="Category to filter articles (optional)"),
     *             @OA\Property(property="source_name", type="string", example="TechCrunch", description="Source name for the articles (optional)"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01", description="Start date to filter articles (optional)"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-12-31", description="End date to filter articles (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success. Returns a list of articles.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="New tech device..."),
     *                     @OA\Property(property="category", type="string", example="tech"),
     *                     @OA\Property(property="source_name", type="string", example="TechCrunch"),
     *                     @OA\Property(property="published_at", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="content", type="string", example="...")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Invalid parameters."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. Invalid or missing Bearer token."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error."
     *     )
     * )
     */
    public function search(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20; 
        $from = ($page - 1) * $perPage;

        $filters = [
            'keyword'    => $request->input('keyword'),
            'category'   => $request->input('category'),
            'source_name'=> $request->input('source_name'),
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
        ];

        $response = $this->articleService->searchArticlesWithFilters($filters, $from, $perPage);
        return response()->json($response->asArray());
    }


    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     operationId="getArticleById",
     *     tags={"Articles"},
     *     summary="Get article details by ID",
     *     description="Returns a single article",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of article to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Article Title"),
     *             @OA\Property(property="author", type="string", example="John Doe"),
     *             @OA\Property(property="content", type="string", example="This is the content of the article."),
     *             @OA\Property(property="category", type="string", example="Technology"),
     *             @OA\Property(property="published_at", type="string", format="date-time", example="2024-09-06T12:34:56Z"),
     *             @OA\Property(property="url", type="string", example="https://example.com/article"),
     *             @OA\Property(property="source_name", type="string", example="Example Source")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     ),
     *     security={{"BearerAuth":{}}},
     * )
    */
    // public function show( Article $article)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' => $article
    //     ], 200);
    // }
    public function show(string $id)
    {
        try {
            // Search for the article by ID in Elasticsearch
            $response = $this->articleService->getArticleByIdFromElasticsearch($id);
    
            // Convert response to array
            $response = $response->asArray();
    
            // If article is not found in Elasticsearch
            if (!isset($response['_source'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }
    
            return response()->json([
                'success' => true,
                'data' => $response['_source']
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve article: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/articles/personalized",
     *     summary="Get personalized news feed",
     *     description="Retrieve articles based on user preferences for news sources, categories, and authors, ordered by published date.",
     *     tags={"User Preferences", "Articles"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="published_at", type="string", format="date-time"),
     *                     @OA\Property(property="source", type="string"),
     *                     @OA\Property(property="category", type="string"),
     *                     @OA\Property(property="author", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function personalizedNews()
    {
        $user = Auth::user();
        $preferences = $user->preferences;

        // Define default pagination settings
        $page = 1;
        $perPage = 20; 
        $from = ($page - 1) * $perPage;

        // Create filters based on user preferences
        $filters = [
            'category'   => $preferences->preferred_categories ?? null,
            'source_name'=> $preferences->preferred_sources ?? null,
            'author'     => $preferences->preferred_authors ?? null,
        ];

        // Call the search service with the filters
        $response = $this->articleService->searchArticlesWithFilters($filters, $from, $perPage);

        return response()->json($response->asArray());
    }
}


