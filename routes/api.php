<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\HomePageController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('journals', [JournalController::class, 'index']);
    Route::get('journals/{journal}', [JournalController::class, 'show']);

    // Route::get('/journal-slug/translate', [JournalController::class, 'getTranslatedSlug']);

    Route::get('issues/{slug}', [IssueController::class, 'show']);

    Route::get('articles/{article:slug}', [ArticleController::class, 'show']);

    Route::get('menu/{journalSlug?}', [MenuController::class, 'show']);

    Route::get('/home', [HomePageController::class, 'index']);

    Route::get('/pages/{slug}', [PageController::class, 'show']);

    Route::get('articles/article/search', [ArticleController::class, 'search']);

});
