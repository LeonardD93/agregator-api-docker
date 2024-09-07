<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles/search', [ArticleController::class, 'search']);
    Route::get('/articles/personalized', [ArticleController::class, 'personalizedNews']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);


    Route::post('/user/preferences', [UserPreferenceController::class, 'setPreferences']);
    Route::get('/user/preferences', [UserPreferenceController::class, 'getPreferences']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
