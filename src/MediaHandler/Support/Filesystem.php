<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Illuminate\Contracts\Filesystem\Factory;
use Outl1ne\NovaMediaHub\Models\Media;

class Filesystem
{
    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $filesystem;

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function delete(Media $media, string $path)
    {
        return $this->filesystem->disk($media->disk)->delete($path);
    }
}
