<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Support\Facades\Storage;

class PathMaker
{
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

    public function getFullPathWithFileName(Media $media)
    {
        $filePath = $this->getPath($media) . $media->file_name;
        return Storage::disk($media->disk)->path($filePath);
    }

    // Get a unique base path for the given media.
    protected function getBasePath(Media $media): string
    {
        $prefix = config('nova-media-hub.path_prefix', '');

        if (empty($prefix)) return '';

        return $prefix . '/' . $media->getKey();
    }
}
