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
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileValidator;
use Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;

class MediaHub extends Tool
{
    public $hideFromMenu = false;
    public $customFields = [];

    public function __construct()
    {
        parent::__construct();

        $this->withCustomFields([
            'alt' => __('novaMediaHub.altTextTitle'),
            'title' => __('novaMediaHub.titleTextTitle'),
        ]);
    }

    public function boot()
    {
        Nova::script('nova-media-hub', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-media-hub', __DIR__ . '/../dist/css/entry.css');

        Nova::provideToScript([
            'novaMediaHub' => [
                'basePath' => MediaHub::getBasePath(),
                'canCreateCollections' => MediaHub::userCanCreateCollections(),
                'locales' => MediaHub::getLocales(),
                'mediaDataFields' => $this->customFields,
            ],
        ]);
    }

    /**
     * Allows custom (text) fields and data to be included with each media item.
     *
     * @param array $fields Key-value pairs of fields where key is the field attribute
     * and value is the string displayed to the user.
     *
     * For example: ['copyright' => __('Copyright')]
     *
     * @param bool $overwrite Optionally force overwrite pre-existing fields.
     *
     * @return self
     **/
    public function withCustomFields(array $fields, $overwrite = false)
    {
        if ($overwrite) {
            $this->customFields = $fields;
        } else {
            $this->customFields = array_merge($this->customFields, $fields);
        }
        return $this;
    }

    public function menu(Request $request)
    {
        if ($this->hideFromMenu) return;

        return MenuSection::make(__('novaMediaHub.navigationItemTitle'))
            ->path(self::getBasePath())
            ->icon('photograph');
    }

    public static function getDataFields(): array
    {
        $mediaHubTool = static::getSelfTool();
        return $mediaHubTool?->customFields ?? [];
    }

    public static function getSelfTool(): MediaHub|null
    {
        return collect(Nova::registeredTools())->first(fn ($tool) => $tool instanceof MediaHub);
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

    public function hideFromMenu()
    {
        $this->hideFromMenu = true;
        return $this;
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

    public static function getFileValidator(): FileValidator
    {
        $fileValidatorClass = config('nova-media-hub.file_validator');
        return new $fileValidatorClass;
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
        return config('nova-media-hub.original_image_manipulations_job_queue', null);
    }

    public static function getImageConversionsJobQueue()
    {
        return config('nova-media-hub.image_conversions_job_queue', null);
    }

    public static function getThumbnailConversionName()
    {
        return config('nova-media-hub.thumbnail_conversion_name', null);
    }

    public static function getImageDriver()
    {
        return config('nova-media-hub.image_driver', config('image.driver'));
    }
}
