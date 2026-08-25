<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MinigameController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ArticleController::class, 'index'])->name('articles.index.get');
Route::get('/search', [ArticleController::class, 'search'])->name('articles.search.get');
Route::get('/article/{slug}_{hashid}', [ArticleController::class, 'show'])
    ->where(['slug' => '[a-z0-9-]+', 'hashid' => '[a-z0-9]{6,}'])
    ->name('articles.view.get');
Route::get('/restaurant/{slug}_{hashid}', [ArticleController::class, 'show'])
    ->where(['slug' => '[a-z0-9-]+', 'hashid' => '[a-z0-9]{6,}'])
    ->name('articles.restaurant.view.get');
Route::get('/hotel/{slug}_{hashid}', [ArticleController::class, 'show'])
    ->where(['slug' => '[a-z0-9-]+', 'hashid' => '[a-z0-9]{6,}'])
    ->name('articles.hotel.view.get');
Route::get('/tag/{slug}', [ArticleController::class, 'tag'])
    ->where('slug', '[a-z0-9-]+')
    ->name('articles.tag.get');

Route::get('/data-privacy', [PageController::class, 'dataPrivacy'])->name('pages.data-privacy.get');
Route::get('/site-notice', [PageController::class, 'siteNotice'])->name('pages.site-notice.get');

Route::get('/umfrage-ergebnisse', [PollController::class, 'results'])->name('polls.results.get');
Route::get('/umfrage/{categoryId}', [PollController::class, 'category'])
    ->whereNumber('categoryId')
    ->name('polls.category.get');

Route::get('/minigame', [MinigameController::class, 'index'])->name('minigame.index');
Route::get('/minigame/play/{secret}', [MinigameController::class, 'play'])
    ->where('secret', '[a-f0-9]{32}')
    ->name('minigame.play');
Route::get('/minigame/{categoryId}', [MinigameController::class, 'category'])
    ->whereNumber('categoryId')
    ->name('minigame.category');
Route::get('/minigame/{categoryId}/{secret}', [MinigameController::class, 'session'])
    ->whereNumber('categoryId')
    ->where('secret', '[a-f0-9]{32}')
    ->name('minigame.session');

Route::get('/feed', [ArticleController::class, 'feed'])->name('feed');
