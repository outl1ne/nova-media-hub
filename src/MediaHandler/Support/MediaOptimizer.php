<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use Spatie\Image\Manipulations;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaOptimizer
{
    public static function optimizeFile(Media $media)
    {
        if (!empty($media->optimized_at)) return;
        if (!Str::startsWith($media->mime_type, 'image')) return;
        if (!MediaHub::isOptimizable($media)) return;

        $pathToFile = MediaHub::getPathMaker()->getFullPathWithFileName($media);

        $manipulations = (new Manipulations())
            ->optimize(config('nova-media-hub.image_optimizers'))
            ->apply();

        Image::load($pathToFile)
            ->manipulate($manipulations)
            ->save();

        $newFileSize = filesize($pathToFile);

        $media->size = $newFileSize;
        $media->optimized_at = now();
        $media->save();
    }
}
