<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

Route::post('login', 'AuthController@login');

Route::prefix('officialArticles')->group(function () {
    Route::get('/', 'ArticleController@publicIndex');
    Route::get('/{id}', 'ArticleController@publicShow');
});

Route::middleware(['auth:super_admin'])->group(function () {
    Route::prefix('articles')->group(function () {
        Route::get('/', 'ArticleController@index');
        Route::get('/{id}', 'ArticleController@show');
        Route::put('/batch-delete', 'ArticleController@destroy');
        Route::put('/{id}', 'ArticleController@update');
        Route::post('/', 'ArticleController@store');
        Route::put('/publish', 'ArticleController@publish');
        Route::put('/un-publish', 'ArticleController@unPublish');
    });
});
