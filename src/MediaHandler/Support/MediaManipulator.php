<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaManipulator
{
    /**
     * Call formatting functions on $image to define new constraits/formats etc.
     * The rest (handling, mime type, saving etc) is handled by the MediaOptimizer.
     *
     * Return null if you don't want to modify the original image and store it as-is.
     *
     * @param \Outl1ne\NovaMediaHub\Models\Media $media
     * @param \Spatie\Image\Image $image
     * @return ?\Spatie\Image\Image
     **/
    public function manipulateOriginal(Media $media, Image $image): ?Image
    {
        if (!$origOptimRules = $this->shouldOptimizeOriginal($media)) return null;

        if ($maxDimens = $origOptimRules['max_dimensions']) {
            $image->fit(Fit::Max, $maxDimens, $maxDimens);
        }

        return $image;
    }

    public function manipulateConversion(Media $media, Image &$image, string $collectionName, array $conversionConfig): Image
    {
        // Check if has necessary data for resize
        $cFormat = $conversionConfig['format'] ?? null;
        $cFitMethod = $conversionConfig['fit'] ?? null;
        $cWidth = $conversionConfig['width'] ?? null;
        $cHeight = $conversionConfig['height'] ?? null;

        if ($cFitMethod) $image->fit($cFitMethod, $cWidth, $cHeight);
        if ($cFormat) $image->format($cFormat);

        return $image;
    }

    public function shouldOptimizeOriginal(Media $media)
    {
        $ogRules = config('nova-media-hub.original_image_manipulations');
        if (!$ogRules['optimize']) return false;

        $allConversions = MediaHub::getConversions();

        $allOgDisabled = $allConversions['*']['original'] ?? null;
        $appliesToCollectionConv = $allConversions[$media->collection_name]['original'] ?? null;
        if ($allOgDisabled === false || $appliesToCollectionConv === false) return false;

        return $ogRules;
    }
}
