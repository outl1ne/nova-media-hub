<?php

namespace Outl1ne\NovaMediaHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Outl1ne\NovaMediaHub\MediaHub;

class MediaHubController extends Controller
{
    public function uploadMediaToCollection(Request $request)
    {
        $files = $request->allFiles()['files'] ?? [];
        $collectionName = $request->get('collectionName') ?? 'default';

        $uploadedMedia = [];
        foreach ($files as $file) {
            $uploadedMedia[] = MediaHub::fileHandler()
                ->withFile($file)
                ->withCollection($collectionName)
                ->save();
        }

        return response()->json($uploadedMedia, 200);
    }
}
