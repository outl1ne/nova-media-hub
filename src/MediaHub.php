<?php

namespace Outl1ne\NovaMediaHub;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Outl1ne\NovaMediaHub\Models\Media;
use Spatie\ImageOptimizer\OptimizerChain;
use Outl1ne\NovaMediaHub\MediaHandler\FileHandler;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer;
use Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Base64File;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileValidator;
use Outl1ne\NovaMediaHub\MediaHandler\Support\MediaManipulator;

class MediaHub extends Tool
{
    public $hideFromMenu = false;
    public $customFields = [];

    protected static ?OptimizerChain $optimizerChain = null;

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
                'mediaDataFields' => static::getDataFields(),
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

    public static function withOptimizerChain(?OptimizerChain $optimizerChain)
    {
        static::$optimizerChain = $optimizerChain;
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
        return static::getSelfTool()?->customFields ?? [];
    }

    public static function getOptimizerChain(): ?OptimizerChain
    {
        return static::$optimizerChain;
    }

    public static function defaultOptimizerChain(): OptimizerChain
    {
        return (new \Spatie\ImageOptimizer\OptimizerChain)
            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Jpegoptim([
                '-m85', // set maximum quality to 85%
                '--force', // ensure that progressive generation is always done also if a little bigger
                '--strip-all', // this strips out all text information such as comments and EXIF data
                '--all-progressive', // this will make sure the resulting image is a progressive one
            ]))

            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Pngquant([
                '--force', // required parameter for this package
            ]))

            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Optipng([
                '-i0', // this will result in a non-interlaced, progressive scanned image
                '-o2', // this set the optimization level to two (multiple IDAT compression trials)
                '-quiet', // required parameter for this package
            ]))

            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Svgo([
                '--disable=cleanupIDs', // disabling because it is known to cause troubles
            ]))

            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Gifsicle([
                '-b', // required parameter for this package
                '-O3', // this produces the slowest but best results
            ]))

            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Cwebp([
                '-m 6', // for the slowest compression method in order to get the best compression.
                '-pass 10', // for maximizing the amount of analysis pass.
                '-mt', // multithreading for some speed improvements.
                '-q 90', //quality factor that brings the least noticeable changes.
            ]));
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
            ->deleteOriginal()
            ->storeOnDisk($targetDisk)
            ->storeConversionOnDisk($targetConversionsDisk)
            ->withCollection($collectionName)
            ->save();
    }

    public static function storeMediaFromBase64($base64String, $fileName, $collectionName, $targetDisk = '', $targetConversionsDisk = ''): Media
    {
        $base64File = new Base64File($base64String, $fileName);

        return FileHandler::fromFile($base64File)
            ->deleteOriginal()
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

    public static function getAllowedMimeTypes()
    {
        return config('nova-media-hub.allowed_mime_types', []);
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

    public static function getMediaManipulator(): MediaManipulator
    {
        $mediaManipulatorClass = config('nova-media-hub.media_manipulator', \Outl1ne\NovaMediaHub\MediaHandler\Support\MediaManipulator::class);
        return new $mediaManipulatorClass;
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
