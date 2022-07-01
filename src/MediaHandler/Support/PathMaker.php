<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Outl1ne\NovaMediaHub\Models\Media;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Traits\PathMakerHelpers;

class PathMaker
{
    use PathMakerHelpers;

    // Get the path for the given media, relative to the root storage path.
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    // Get the path for the conversions of media, relative to the root storage path.
    public function getConversionsPath(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    // Get a unique base path for the given media.
    protected function getBasePath(Media $media): string
    {
        $prefix = config('nova-media-hub.path_prefix', '');
        return $prefix . '/' . $media->getKey();
    }
}
