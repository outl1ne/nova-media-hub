<?php

namespace Outl1ne\NovaMediaHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaHubController extends Controller
{
    public function getCollections(Request $request)
    {
        $collections = Media::select('collection_name')
            ->groupBy('collection_name')
            ->get()
            ->pluck('collection_name');

        return response()->json($collections, 200);
    }

    public function getCollectionMedia(Request $request, $collectionName)
    {
        $media = Media::where('collection_name', $collectionName)->paginate();

        return response()->json($media, 200);
    }

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

    public function deleteMedia(Request $request)
    {
        $mediaId = $request->route('mediaId');
        if ($mediaId && $media = Media::find($mediaId)) {
            Storage::disk($media->disk)->delete($media->path);
            $media->delete();
        }
        return response()->json('', 204);
    }
}
