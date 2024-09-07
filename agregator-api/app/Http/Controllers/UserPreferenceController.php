<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/preferences",
     *     summary="Set user preferences",
     *     description="Set the user's preferences for news sources, categories, and authors",
     *     tags={"User Preferences"},
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string")),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferences updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="preferences", type="object",
     *                 @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string")),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function setPreferences(Request $request)
    {
        $request->validate([
            'preferred_sources' => 'array',
            'preferred_categories' => 'array',
            'preferred_authors' => 'array',
        ]);

        $user = $request->user();
        $preferences = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['preferred_sources', 'preferred_categories', 'preferred_authors'])
        );

        return response()->json(['message' => 'Preferences updated successfully', 'preferences' => $preferences]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/preferences",
     *     summary="Get user preferences",
     *     description="Retrieve the current user's preferences for news sources, categories, and authors",
     *     tags={"User Preferences"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string")),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getPreferences()
    {
        $preferences = Auth::user()->preferences;

        return response()->json($preferences);
    }

}
