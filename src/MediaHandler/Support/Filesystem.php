<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Illuminate\Contracts\Filesystem\Factory;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;

class Filesystem
{
    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $filesystem;

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function deleteFromMediaFolder(Media $media)
    {
        $path = MediaHub::getPathMaker()->getPath($media);
        return $this->filesystem->disk($media->disk)->delete($path);
    }

    public function create(string $file, Media $media, ?string $targetFileName = null): void
    {
        $this->copyFileToMediaFolder($file, $media, $targetFileName);
    }

    public function copyFileToMediaFolder(string $pathToFile, Media $media, ?string $targetFileName = null, ?string $type = null)
    {
        $forOriginal = $type !== 'conversions';
        $newFileName = $targetFileName ?: pathinfo($pathToFile, PATHINFO_BASENAME);
        $destination = $this->getMediaDirectory($media, $type) . $newFileName;

        $file = fopen($pathToFile, 'r');

        $diskName = $forOriginal
            ? $media->disk
            : $media->conversions_disk;

        $this->filesystem
            ->disk($diskName)
            ->put($destination, $file);

        if (is_resource($file)) fclose($file);
    }

    public function getMediaDirectory(Media $media, ?string $type = null): string
    {
        $forOriginal = $type !== 'conversions';
        $pathMaker = MediaHub::getPathMaker();

        $directory = $forOriginal
            ? $pathMaker->getPath($media)
            : $pathMaker->getConversionsPath($media);

        $diskName = $forOriginal
            ? $media->disk
            : $media->conversions_disk;

        $this->filesystem->disk($diskName)->makeDirectory($directory);

        return $directory;
    }
}
