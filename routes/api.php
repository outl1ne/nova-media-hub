<?php

use Illuminate\Support\Facades\Route;
use Outl1ne\NovaMediaHub\Http\Controllers\MediaHubController;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::prefix('/nova-vendor/media-hub')->group(function () {
    Route::get('/media', [MediaHubController::class, 'getMedia']);
    Route::get('/collections', [MediaHubController::class, 'getCollections']);

    Route::post('/media/{mediaId}/move', [MediaHubController::class, 'moveMediaToCollection']);
    Route::post('/media/save', [MediaHubController::class, 'uploadMediaToCollection']);
    Route::delete('media/{mediaId}', [MediaHubController::class, 'deleteMedia']);
});
