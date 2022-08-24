<?php

namespace Outl1ne\NovaMediaHub;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Outl1ne\NovaMediaHub\Models\Media;
use Outl1ne\NovaMediaHub\MediaHandler\FileHandler;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Base64File;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer;
use Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;

class MediaHub extends Tool
{
    public function boot()
    {
        Nova::script('nova-media-hub', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-media-hub', __DIR__ . '/../dist/css/entry.css');

        Nova::provideToScript([
            'novaMediaHub' => [
                'basePath' => MediaHub::getBasePath(),
                'canCreateCollections' => MediaHub::userCanCreateCollections(),
                'locales' => MediaHub::getLocales(),
            ],
        ]);
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__('novaMediaHub.navigationItemTitle'))
            ->path(self::getBasePath())
            ->icon('photograph');
    }

    public static function storeMediaFromDisk($filePath, $disk, $collectionName, $targetDisk = '', $targetConversionsDisk = '')
    {
        $remoteFile = new RemoteFile($filePath, $disk);

        return FileHandler::fromFile($remoteFile)
            ->storeOnDisk($targetDisk)
            ->storeConversionOnDisk($targetConversionsDisk)
            ->withCollection($collectionName)
            ->save();
    }

    public static function storeMediaFromUrl($fileUrl, $collectionName, $targetDisk = '', $targetConversionsDisk = ''): Media
    {
        $remoteFile = new RemoteFile($fileUrl);

        return FileHandler::fromFile($remoteFile)
            ->storeOnDisk($targetDisk)
            ->storeConversionOnDisk($targetConversionsDisk)
            ->withCollection($collectionName)
            ->save();
    }

    public static function storeMediaFromBase64($base64String, $fileName, $collectionName, $targetDisk = '', $targetConversionsDisk = ''): Media
    {
        $base64File = new Base64File($base64String, $fileName);

        return FileHandler::fromFile($base64File)
            ->storeOnDisk($targetDisk)
            ->storeConversionOnDisk($targetConversionsDisk)
            ->withCollection($collectionName)
            ->save();
    }

    public static function getConversionForMedia(Media $media)
    {
        $allConversions = static::getConversions();

        $appliesToAllConversions = $allConversions['*'] ?? [];
        $appliesToCollectionConv = $allConversions[$media->collection_name] ?? [];

        // Create merged conversions array
        $conversions = array_replace_recursive(
            $appliesToAllConversions,
            $appliesToCollectionConv,
        );

        // Remove invalid configurations
        $conversions = array_filter($conversions, function ($c) {
            if (empty($c)) return false;
            if (empty($c['fit'])) return false;
            if (empty($c['height']) && empty($c['width'])) return false;
            return true;
        });

        return $conversions;
    }



    // ------------------------------
    // Getters
    // ------------------------------

    public static function getTableName()
    {
        return config('nova-media-hub.table_name');
    }

    public static function getMediaModel()
    {
        return config('nova-media-hub.model');
    }

    public static function getQuery()
    {
        $model = self::getMediaModel();

        return (new $model)->query();
    }

    public static function getBasePath()
    {
        return config('nova-media-hub.base_path');
    }

    public static function getMaxFileSizeInBytes()
    {
        $maxInBytes = config('nova-media-hub.max_uploaded_file_size_in_kb');
        return $maxInBytes ? $maxInBytes * 1000 : null;
    }

    public static function getPathMaker(): PathMaker
    {
        $pathMakerClass = config('nova-media-hub.path_maker');
        return new $pathMakerClass;
    }

    public static function getFileNamer(): FileNamer
    {
        $fileNamerClass = config('nova-media-hub.file_namer');
        return new $fileNamerClass;
    }

    public static function fileHandler()
    {
        return new FileHandler();
    }

    public static function isOptimizable(Media $media)
    {
        $optimizableMimeTypes = config('nova-media-hub.optimizable_mime_types');
        return in_array($media->mime_type, $optimizableMimeTypes);
    }

    public static function shouldOptimizeOriginal()
    {
        $ogRules = config('nova-media-hub.original_image_manipulations');
        if (!$ogRules['optimize']) return false;
        return $ogRules;
    }

    public static function getLocales()
    {
        return config('nova-media-hub.locales');
    }

    public static function getDefaultCollections(): array
    {
        return config('nova-media-hub.collections', []);
    }

    public static function userCanCreateCollections()
    {
        return config('nova-media-hub.user_can_create_collections', false);
    }

    public static function getConversions()
    {
        return config('nova-media-hub.image_conversions', []);
    }

    public static function getOriginalImageManipulationsJobQueue()
    {
        return config('nova-media-hub.original_image_manipulations_job_queue', config('queue.default'));
    }

    public static function getImageConversionsJobQueue()
    {
        return config('nova-media-hub.image_conversions_job_queue', config('queue.default'));
    }

    public static function getThumbnailConversionName()
    {
        return config('nova-media-hub.thumbnail_conversion_name', null);
    }
}
