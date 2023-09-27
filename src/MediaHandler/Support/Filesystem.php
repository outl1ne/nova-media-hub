<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Exception;
use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Contracts\Filesystem\Factory;
use Outl1ne\NovaMediaHub\Exceptions\FileDoesNotExistException;

class Filesystem
{
    const TYPE_ORIGINAL = 'original';
    const TYPE_CONVERSION = 'conversion';

    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $filesystem;

    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function deleteFromMediaLibrary(Media $media)
    {
        // Delete main file
        $this->filesystem->disk($media->disk)->delete("{$media->path}{$media->file_name}");

        // Delete conversions
        $conversionsPath = $media->conversionsPath;
        $conversions = $media->conversions ?? [];

        foreach ($conversions as $conversionName => $fileName) {
            $this->filesystem->disk($media->conversions_disk)->delete("{$conversionsPath}/{$fileName}");
        }

        // Check if conversions folder is empty, if so, delete it
        $convDisk = $this->filesystem->disk($media->conversions_disk);
        if ($convDisk->exists($conversionsPath)) {
            $fileCount = count($convDisk->files($conversionsPath));
            $dirCount = count(array_filter(
                $convDisk->allDirectories($conversionsPath),
                fn ($path) => rtrim($path, '/') !== rtrim($conversionsPath, '/')
            ));

            if ($fileCount === 0 && $dirCount === 0) {
                $convDisk->deleteDirectory($conversionsPath);
            }
        }

        // Check if main media folder is empty, if so, delete it
        $mainDisk = $this->filesystem->disk($media->disk);
        if ($mainDisk->exists($media->path)) {
            $fileCount = count($convDisk->files($media->path));
            $dirCount = count(array_filter(
                $convDisk->allDirectories($media->path),
                fn ($path) => rtrim($path, '/') !== rtrim($media->path, '/')
            ));

            if ($fileCount === 0 && $dirCount === 0) {
                $convDisk->deleteDirectory($media->path);
            }
        }

        return true;
    }

    public function copyFileToMediaLibrary(string $pathToFile, Media $media, ?string $targetFileName = null, ?string $type = null, $deleteFile = true)
    {
        $forOriginal = $type !== static::TYPE_CONVERSION;
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

        // Delete old file
        if ($deleteFile && $pathToFile !== $destination) {
            unlink($pathToFile);
        }
    }

    public function copyFromMediaLibrary(Media $media, string $targetFilePath): ?string
    {
        $filePath = $this->getMediaDirectory($media) . $media->file_name;

        $exists = $this->filesystem->disk($media->disk)->exists($filePath);
        if (!$exists) {
            report(new FileDoesNotExistException("Tried to copy file for media [$media->id] but it did not exist."));
            return null;
        }

        $fileStream = $this->filesystem->disk($media->disk)->readStream($filePath);
        file_put_contents($targetFilePath, $fileStream);
        if (is_resource($fileStream)) fclose($fileStream);
        return $targetFilePath;
    }

    public function getMediaDirectory(Media $media, ?string $type = null): string
    {
        $forOriginal = $type !== static::TYPE_CONVERSION;
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

    public function makeTemporaryCopy($localFilePath)
    {
        if (!is_file($localFilePath)) throw new FileDoesNotExistException($localFilePath);
        $newFilePath = FileHelpers::getTemporaryFilePath('tmp-conversion-copy');
        if (!copy($localFilePath, $newFilePath)) {
            $err = error_get_last();
            throw new Exception($err['message'] ?? 'Copy failed due to unknown error.');
        }
        return $newFilePath;
    }
}
