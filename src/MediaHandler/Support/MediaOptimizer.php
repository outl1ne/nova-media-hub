<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use Spatie\Image\Manipulations;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaOptimizer
{
    public static function optimizeOriginalFile(Media $media)
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

        Image::load($pathToFile)
            ->manipulate($manipulations)
            ->save();

        $media->size = filesize($pathToFile);
        $media->optimized_at = now();
        $media->save();
    }
}
