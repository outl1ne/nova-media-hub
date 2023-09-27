<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Spatie\Image\Manipulations;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class MediaManipulator
{
    /**
     * Add manipulations to $manipulations to define new constraits/formats etc.
     * The rest (handling, mime type, saving etc) is handled by the MediaOptimizer.
     *
     * Return null if you don't want to modify the original image and store it as-is.
     *
     * @param Media $media
     * @param Manipulations $manipulations
     * @return ?Manipulations
     **/
    public function manipulateOriginal(Media $media, Manipulations &$manipulations): ?Manipulations
    {
        if (!$origOptimRules = $this->shouldOptimizeOriginal($media)) return null;

        if ($maxDimens = $origOptimRules['max_dimensions']) {
            $manipulations->fit(Manipulations::FIT_MAX, $maxDimens, $maxDimens);
        }

        return $manipulations;
    }

    public function manipulateConversion(Media $media, Manipulations &$manipulations, string $collectionName, array $conversionConfig): Manipulations
    {
        // Check if has necessary data for resize
        $cFormat = $conversionConfig['format'] ?? null;
        $cFitMethod = $conversionConfig['fit'] ?? null;
        $cWidth = $conversionConfig['width'] ?? null;
        $cHeight = $conversionConfig['height'] ?? null;

        $manipulations->fit($cFitMethod, $cWidth, $cHeight);

        if ($cFormat) $manipulations->format($cFormat);

        return $manipulations;
    }

    protected function shouldOptimizeOriginal(Media $media)
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
