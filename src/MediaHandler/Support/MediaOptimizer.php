<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use Spatie\Image\Manipulations;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaOptimizer
{
    public static function optimizeOriginalImage(Media $media, $localFilePath = null)
    {
        if (!empty($media->optimized_at)) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;
        if (!$origOptimRules = MediaHub::shouldOptimizeOriginal()) return;

        $fileSystem = self::getFilesystem();

        $manipulations = (new Manipulations());
        $manipulations->optimize(config('nova-media-hub.image_optimizers'));

        if ($maxDimens = $origOptimRules['max_dimensions']) {
            $manipulations->fit(Manipulations::FIT_MAX, $maxDimens, $maxDimens);
        }

        $manipulations->apply();

        // Copy media from whatever disk to local filesystem for manipulations
        if (!$localFilePath || !is_file($localFilePath)) {
            $localFilePath = FileHelpers::getTemporaryFilePath();
            $fileSystem->copyFromMediaLibrary($media, $localFilePath);
        }

        // Load and save modified version
        Image::load($localFilePath)
            ->useImageDriver(config('image.driver'))
            ->manipulate($manipulations)
            ->save();

        $media->size = filesize($localFilePath);
        $media->optimized_at = now();

        $fileSystem->copyFileToMediaLibrary($localFilePath, $media, $media->file_name, Filesystem::TYPE_ORIGINAL, false);

        $media->save();
    }

    public static function makeConversion(Media $media, $localFilePath, $conversionName, $conversionConfig)
    {
        if (!empty($media->conversion[$conversionName])) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;

        $pathMaker = MediaHub::getPathMaker();
        $fileSystem = self::getFilesystem();

        // Check if has necessary data for resize
        $cFitMethod = $conversionConfig['fit'] ?? null;
        $cWidth = $conversionConfig['width'] ?? null;
        $cHeight = $conversionConfig['height'] ?? null;

        $manipulations = (new Manipulations())
            ->optimize(config('nova-media-hub.image_optimizers'))
            ->fit($cFitMethod, $cWidth, $cHeight)
            ->apply();

        // Copy media from whatever disk to local filesystem for manipulations
        if (!$localFilePath || !is_file($localFilePath)) {
            $localFilePath = FileHelpers::getTemporaryFilePath();
            $fileSystem->copyFromMediaLibrary($media, $localFilePath);
        }

        // Load and save modified version
        Image::load($localFilePath)
            ->useImageDriver(config('image.driver'))
            ->manipulate($manipulations)
            ->save();

        $conversionFileName = $pathMaker->getConversionFileName($media, $conversionName);
        $fileSystem->copyFileToMediaLibrary($localFilePath, $media, $conversionFileName, Filesystem::TYPE_CONVERSION, false);

        $newConversions = $media->conversions;
        $newConversions[$conversionName] = $conversionFileName;

        $media->conversions = $newConversions;
        $media->save();
    }

    protected static function getFilesystem(): Filesystem
    {
        return app()->make(Filesystem::class);
    }
}
