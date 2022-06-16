<?php

namespace Outl1ne\NovaMediaHub\MediaHandler;

use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Support\Facades\Storage;
use Outl1ne\NovaMediaHub\Exceptions\DiskDoesNotExistException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;
use Outl1ne\NovaMediaHub\Exceptions\FileTooLargeException;
use Outl1ne\NovaMediaHub\Exceptions\UnknownFileTypeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Outl1ne\NovaMediaHub\Exceptions\FileDoesNotExistException;
use Outl1ne\NovaMediaHub\Exceptions\NoFileProvidedException;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileHelpers;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer;

class FileHandler
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|string */
    protected $file;

    protected string $fileName = '';
    protected string $pathToFile = '';
    protected string $diskName = '';
    protected string $conversionsDiskName = '';
    protected string $collectionName = '';

    public function __construct()
    {
        $this->filesystem = app()->make(Filesystem::class);
        $this->fileNamer = config('nova-media-hub.file_namer');
    }

    public static function fromFile($file): self
    {
        return (new static)->withFile($file);
    }

    public function withFile($file): self
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->fileName = pathinfo($file, PATHINFO_BASENAME);
            return $this;
        }

        if ($file instanceof RemoteFile) {
            $this->pathToFile = $file->getKey();
            $this->fileName = $file->getFilename();
            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->fileName = $file->getClientOriginalName();
            return $this;
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->fileName = pathinfo($file->getFilename(), PATHINFO_BASENAME);
            return $this;
        }

        $this->file = null;
        throw new UnknownFileTypeException();
    }

    public function withCollection(string $collectionName)
    {
        $this->collectionName = $collectionName;
        return $this;
    }

    public function storeOnDisk($diskName)
    {
        $this->diskName = $diskName;
        return $this;
    }

    public function storeConversionOnDisk($diskName)
    {
        $this->conversionsDiskName = $diskName;
        return $this;
    }

    public function create($file = null): Media
    {
        if (!empty($file)) $this->withFile($file);

        if (empty($this->file)) throw new NoFileProvidedException();

        if (!is_file($this->pathToFile)) throw new FileDoesNotExistException($this->pathToFile);

        $maxSizeBytes = MediaHub::getMaxFileSizeInBytes();
        if ($maxSizeBytes && filesize($this->pathToFile) > $maxSizeBytes) {
            throw new FileTooLargeException($this->pathToFile);
        }

        $sanitizedFileName = $this->sanitizeFileName($this->fileName);
        [$fileName, $extension] = $this->splitNameAndExtension($sanitizedFileName);
        $fileName = $this->getFileNamer()->formatOriginalFileName($fileName, $extension);
        $this->fileName = $this->appendExtension($fileName, $extension);

        $mediaClass = MediaHub::getMediaModel();
        $media = new $mediaClass();

        $media->name = $this->mediaName;
        $media->file_name = $this->fileName;
        $media->collection_name = $this->collectionName;
        $media->size = filesize($this->pathToFile);
        $media->mime_type = FileHelpers::getMimeType($this->pathToFile);
        $media->conversions = [];

        $media->disk = $this->getDiskName();
        $this->ensureDiskExists($media->disk);

        $media->conversions_disk = $this->getConversionsDiskName();
        $this->ensureDiskExists($media->conversions_disk);

        $media->save();

        // TODO: Dispatch conversions jobs

        return $media;
    }


    // Helpers

    protected function getDiskName(): string
    {
        return $this->diskName ?: config('nova-media-hub.disk_name');
    }

    protected function getConversionsDiskName(): string
    {
        return $this->conversionsDiskName ?: config('nova-media-hub.conversions_disk_name');
    }

    protected function ensureDiskExists(string $diskName)
    {
        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw new DiskDoesNotExistException($diskName);
        }
    }

    protected function getFileNamer(): FileNamer
    {
        $fileNamerClass = config('nova-media-hub.file_namer');
        return new $fileNamerClass;
    }

    protected function sanitizeFileName(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    protected function splitNameAndExtension(string $fileName): array
    {
        $name = pathinfo($fileName, PATHINFO_BASENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        return [$name, $extension];
    }

    protected function appendExtension($fileName, $extension)
    {
        return $extension ? "{$fileName}.{$extension}" : $fileName;
    }
}
