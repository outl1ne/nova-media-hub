<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use Spatie\Image\Manipulations;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaOptimizer
{
    public static function optimizeOriginalImage(Media $media)
    {
        if (!empty($media->optimized_at)) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;
        if (!$origOptimRules = MediaHub::shouldOptimizeOriginal()) return;

        $pathToFile = MediaHub::getPathMaker()->getFullPathWithFileName($media);

        $manipulations = (new Manipulations());
        $manipulations->optimize(config('nova-media-hub.image_optimizers'));

        if ($maxDimens = $origOptimRules['max_dimensions']) {
            $manipulations->fit(Manipulations::FIT_MAX, $maxDimens, $maxDimens);
        }

        $manipulations->apply();

        $tempFilePath = static::getTemporaryFilePath();
        Image::load($pathToFile)
            ->manipulate($manipulations)
            ->save($tempFilePath);

        $fileSystem = self::getFilesystem();
        $fileSystem->copyFileToMediaFolder($tempFilePath, $media, $media->file_name, Filesystem::TYPE_ORIGINAL);

        $media->size = filesize($pathToFile);
        $media->optimized_at = now();
        $media->save();
    }

    public static function makeConversion(Media $media, $conversioName, $conversionConfig)
    {
        if (!empty($media->conversion[$conversioName])) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;

        $pathMaker = MediaHub::getPathMaker();

        // Check if has necessary data for resize
        $cFitMethod = $conversionConfig['fit'] ?? null;
        $cWidth = $conversionConfig['width'] ?? null;
        $cHeight = $conversionConfig['height'] ?? null;

        $pathToOriginalFile = $pathMaker->getFullPathWithFileName($media);

        $manipulations = (new Manipulations())
            ->optimize(config('nova-media-hub.image_optimizers'))
            ->fit($cFitMethod, $cWidth, $cHeight)
            ->apply();


        $tempFilePath = static::getTemporaryFilePath();
        Image::load($pathToOriginalFile)
            ->manipulate($manipulations)
            ->save($tempFilePath);

        $fileSystem = self::getFilesystem();
        $conversionFileName = $pathMaker->getConversionFileName($media, $conversioName);
        $fileSystem->copyFileToMediaFolder($tempFilePath, $media, $conversionFileName, Filesystem::TYPE_CONVERSION);

        $newConversions = $media->conversions;
        $newConversions[$conversioName] = $conversionFileName;

        $media->conversions = $newConversions;
        $media->save();
    }

    protected static function getTemporaryFilePath()
    {
        return tempnam(sys_get_temp_dir(), 'media-');
    }

    protected static function getFilesystem(): Filesystem
    {
        return app()->make(Filesystem::class);
    }
}
