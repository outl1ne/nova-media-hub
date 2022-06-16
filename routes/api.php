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

Route::post('/nova-vendor/media-hub/media/save', [MediaHubController::class, 'uploadMediaToCollection']);
