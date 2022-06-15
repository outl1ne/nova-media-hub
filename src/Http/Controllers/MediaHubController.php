<?php

namespace Outl1ne\NovaMediaHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MediaHubController extends Controller
{
    public function uploadMediaToCollection(Request $request)
    {
        $files = $request->allFiles()['files'] ?? [];
        $collectionName = $request->route('collection') ?? 'default';

        ray($files, $collectionName);

        return response('', 204);
    }
}
