<?php

namespace Outl1ne\NovaMediaHub\Http\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Filesystem;

class MediaHubController extends Controller
{
    public function getCollections(Request $request)
    {
        return response()->json($this->getCollectionNames(), 200);
    }

    public function renameCollection(Request $request)
    {
        if (!MediaHub::userCanCreateCollections()) {
            return response()->json(['error' => 'Renaming collections is not allowed.'], 403);
        }

        $from = str(trim((string) $request->get('from')))->lower()->toString();
        $to = str(trim((string) $request->get('to')))->lower()->toString();

        if ($from === '' || $to === '') {
            return response()->json(['error' => 'Both the current and the new collection name are required.'], 400);
        }

        if (mb_strlen($to) > 255 || preg_match('/[\/\\\\]/', $to)) {
            return response()->json(['error' => 'Invalid collection name.'], 400);
        }

        if ($from === $to) {
            return response()->json(['collection' => $to, 'success_count' => 0], 200);
        }

        $collections = $this->getCollectionNames();

        if (!in_array($from, $collections, true)) {
            return response()->json(['error' => 'Collection not found.'], 404);
        }

        if (in_array($to, $collections, true)) {
            return response()->json(['error' => 'A collection with that name already exists.'], 409);
        }

        // Media files are stored under a path derived from the media ID, not the
        // collection name, so renaming is purely a database operation.
        $updatedCount = MediaHub::getQuery()
            ->where(...$this->collectionNameWhere($from))
            ->update(['collection_name' => $to]);

        return response()->json([
            'collection' => $to,
            'success_count' => $updatedCount,
        ], 200);
    }

    public function getMedia()
    {
        $media = app(Pipeline::class)
            ->send(MediaHub::getQuery())->through([
                \Outl1ne\NovaMediaHub\Filters\Collection::class,
                \Outl1ne\NovaMediaHub\Filters\Search::class,
                \Outl1ne\NovaMediaHub\Filters\Sort::class,
            ])->thenReturn()->paginate(72);


        $newCollection = $media->getCollection()->map->formatForNova();
        $media->setCollection($newCollection);

        return response()->json($media, 200);
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
                    ->deleteOriginal()
                    ->withCollection($collectionName)
                    ->save();
            } catch (Exception $e) {
                $exceptions[] = $e;
                report($e);
            }
        }

        $uploadedMedia = collect($uploadedMedia);
        $coreResponse = [
            'media' => $uploadedMedia->map->formatForNova(),
            'hadExisting' => $uploadedMedia->where(fn ($m) => $m->wasExisting)->count() > 0,
            'success_count' => count($files) - count($exceptions),
        ];

        if (!empty($exceptions)) {
            return response()->json([
                ...$coreResponse,
                'errors' => Arr::map($exceptions, function ($e) {
                    $className = class_basename(get_class($e));
                    return "{$className}: {$e->getMessage()}";
                }),
            ], 400);
        }

        return response()->json($coreResponse, 200);
    }

    public function deleteMedia(Request $request)
    {
        $mediaId = $request->route('mediaId');
        if ($mediaId && $media = MediaHub::getQuery()->find($mediaId)) {
            /** @var Filesystem */
            $fileSystem = app()->make(Filesystem::class);
            $fileSystem->deleteFromMediaLibrary($media);
            $media->delete();
        }
        return response()->json('', 204);
    }

    public function moveMediaToCollection(Request $request)
    {
        $collectionName = $request->get('collection');
        $mediaIds = $request->get('mediaIds');
        if (!$collectionName) return response()->json(['error' => 'Collection name required.'], 400);
        if (count($mediaIds) === 0) return response()->json(['error' => 'Media IDs required.'], 400);

        $updatedCount = MediaHub::getQuery()
            ->whereIn('id', $mediaIds)
            ->update(['collection_name' => $collectionName]);

        return response()->json([
            'success_count' => $updatedCount,
        ], 200);
    }

    public function moveMediaItemToCollection(Request $request, $mediaId)
    {
        $collectionName = $request->get('collection');
        if (!$collectionName) return response()->json(['error' => 'Collection name required.'], 400);

        $media = MediaHub::getQuery()->findOrFail($mediaId);

        $media->collection_name = $collectionName;
        $media->save();

        return response()->json($media, 200);
    }

    public function updateMediaData(Request $request, $mediaId)
    {
        $media = MediaHub::getQuery()->findOrFail($mediaId);
        $locales = MediaHub::getLocales();
        $fieldKeys = array_keys(MediaHub::getDataFields());
        $mediaData = is_array($media->data) ? $media->data : json_decode($media->data ?? '[]', true);

        // No translations, we hardcoded frontend to always send data as 'en'
        foreach ($fieldKeys as $key) {
            $mediaData[$key] = $request->input(empty($locales) ? "{$key}.en" : $key) ?? null;
        }

        $media->data = $mediaData;
        $media->save();

        return response()->json($media, 200);
    }

    public function replaceMediaInPlace(Request $request, $mediaId)
    {
        $file = $request->allFiles()['file'] ?? null;
        if (!$file) return response()->json(['error' => 'File required.'], 400);

        /** @var \Outl1ne\NovaMediaHub\Models\Media */
        $media = MediaHub::getQuery()->findOrFail($mediaId);

        $newMediaItem = null;
        try {
            $newMediaItem = MediaHub::fileHandler()
                ->withModelData([
                    'id' => $media->id,
                    'created_at' => $media->created_at,
                    'collection_name' => $media->collection_name,
                ])
                ->allowDuplicates()
                ->withFile($file)
                ->deleteOriginal()
                ->save();

            if ($media->original_file_hash !== $newMediaItem->original_file_hash) {
                /** @var Filesystem */
                $fileSystem = app()->make(Filesystem::class);
                $fileSystem->deleteFromMediaLibrary($media);
            }
        } catch (Exception $e) {
            report($e);

            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
            ], 400);
        }

        return response()->json([
            'media' => $newMediaItem->formatForNova(),
            'success' => true,
        ], 200);
    }

    protected function getCollectionNames(): array
    {
        $defaultCollections = MediaHub::getDefaultCollections();

        return MediaHub::getMediaModel()
            ::distinct()
            ->pluck('collection_name')
            ->merge($defaultCollections)
            ->map(fn ($name) => str($name)->lower()->toString())
            ->unique()
            ->values()
            ->toArray();
    }

    // Mirrors the Collection filter so lookups stay case insensitive on the
    // databases that support it.
    protected function collectionNameWhere(string $collectionName): array
    {
        $connectionName = MediaHub::getMediaModel()::getConnectionResolver()->getDefaultConnection();
        $isProperSql = in_array($connectionName, ['mysql', 'pgsql', 'sqlite']);

        return $isProperSql
            ? [DB::raw('LOWER(collection_name)'), '=', mb_strtolower($collectionName)]
            : ['collection_name', '=', $collectionName];
    }
}
