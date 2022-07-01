<?php

namespace Outl1ne\NovaMediaHub\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaHubController extends Controller
{
    public function getCollections(Request $request)
    {
        $defaultCollections = MediaHub::getDefaultCollections();

        $collections = MediaHub::getMediaModel()::select('collection_name')
            ->groupBy('collection_name')
            ->get()
            ->pluck('collection_name')
            ->merge($defaultCollections)
            ->unique()
            ->values()
            ->toArray();

        return response()->json($collections, 200);
    }

    public function getMedia(Request $request)
    {
        $collectionName = $request->get('collection');

        $mediaQuery = Media::query();

        if ($collectionName) {
            $mediaQuery->where('collection_name', $collectionName);
        }

        $mediaQuery->orderBy('created_at', 'DESC');

        return response()->json($mediaQuery->paginate(18), 200);
    }

    public function uploadMediaToCollection(Request $request)
    {
        $files = $request->allFiles()['files'] ?? [];
        $collectionName = $request->get('collectionName') ?? 'default';

        $exceptions = [];

        $uploadedMedia = [];
        foreach ($files as $file) {
            try {
                $uploadedMedia[] = MediaHub::fileHandler()
                    ->withFile($file)
                    ->withCollection($collectionName)
                    ->save();
            } catch (Exception $e) {
                $exceptions[] = class_basename(get_class($e));
            }
        }

        if (!empty($exceptions)) {
            return response()->json([
                'success_count' => count($files) - count($exceptions),
                'message' => 'Error(s): ' . implode(', ', $exceptions),
            ], 400);
        }

        return response()->json($uploadedMedia, 200);
    }

    public function deleteMedia(Request $request)
    {
        $mediaId = $request->route('mediaId');
        if ($mediaId && $media = MediaHub::getMediaModel()::find($mediaId)) {
            Storage::disk($media->disk)->delete($media->path);
            $media->delete();
        }
        return response()->json('', 204);
    }

    public function moveMediaToCollection(Request $request, $mediaId)
    {
        $collectionName = $request->get('collection');
        if (!$collectionName) return response()->json(['error' => 'Collection name required.'], 400);

        $media = MediaHub::getMediaModel()::findOrFail($mediaId);


        $media->collection_name = $collectionName;
        $media->save();

        return response()->json($media, 200);
    }
}
