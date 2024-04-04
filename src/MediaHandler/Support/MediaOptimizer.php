<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaOptimizer
{
    public static function optimizeOriginalImage(Media $media, $localFilePath = null)
    {
        if (!empty($media->optimized_at)) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;

        $fileSystem = self::getFilesystem();

        $manipulator = MediaHub::getMediaManipulator();
        if (!$manipulator->shouldOptimizeOriginal($media)) return;

        // Copy media from whatever disk to local filesystem for manipulations
        if (!$localFilePath || !is_file($localFilePath)) {
            $localFilePath = FileHelpers::getTemporaryFilePath(extension: $media->getExtension());
            $localFilePath = $fileSystem->copyFromMediaLibrary($media, $localFilePath);
            if (!$localFilePath) return;
        }

        $image = Image::load($localFilePath);
        if ($driver = MediaHub::getImageDriver()) $image->useImageDriver($driver);
        $image->optimize(MediaHub::getOptimizerChain());
        $image = $manipulator->manipulateOriginal($media, $image);
        $image->save();

        $media->mime_type = FileHelpers::getMimeType($localFilePath);
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

        $manipulator = MediaHub::getMediaManipulator();

        // Copy media from whatever disk to local filesystem for manipulations
        if (!$localFilePath || !is_file($localFilePath)) {
            $localFilePath = FileHelpers::getTemporaryFilePath(extension: $media->getExtension());
            $localFilePath = $fileSystem->copyFromMediaLibrary($media, $localFilePath);
            if (!$localFilePath) return;
        }

        $image = Image::load($localFilePath);
        if ($driver = MediaHub::getImageDriver()) $image->useImageDriver($driver);
        $image->optimize(MediaHub::getOptimizerChain());
        $image = $manipulator->manipulateConversion($media, $image, $conversionName, $conversionConfig);
        $image->save();

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
