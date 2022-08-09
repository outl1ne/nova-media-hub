<?php

namespace Outl1ne\NovaMediaHub\MediaHandler;

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Support\Facades\File;
use Outl1ne\NovaMediaHub\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Filesystem;
use Outl1ne\NovaMediaHub\Exceptions\FileTooLargeException;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileHelpers;
use Outl1ne\NovaMediaHub\Jobs\MediaHubCreateConversionsJob;
use Outl1ne\NovaMediaHub\Exceptions\NoFileProvidedException;
use Outl1ne\NovaMediaHub\Exceptions\UnknownFileTypeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Outl1ne\NovaMediaHub\Exceptions\FileDoesNotExistException;
use Outl1ne\NovaMediaHub\Exceptions\DiskDoesNotExistException;
use Outl1ne\NovaMediaHub\Jobs\MediaHubOptimizeOriginalMediaJob;

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
    protected array $modelData = [];

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
            $file->downloadFileToCurrentFilesystem();
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

    public function withModelData(array $modelData)
    {
        $this->modelData = $modelData;
        return $this;
    }

    public function save($file = null): Media
    {
        if (!empty($file)) $this->withFile($file);

        if (empty($this->file)) throw new NoFileProvidedException();

        if (!is_file($this->pathToFile)) throw new FileDoesNotExistException($this->pathToFile);

        $maxSizeBytes = MediaHub::getMaxFileSizeInBytes();
        if ($maxSizeBytes && filesize($this->pathToFile) > $maxSizeBytes) {
            throw new FileTooLargeException($this->pathToFile);
        }

        $sanitizedFileName = FileHelpers::sanitizeFileName($this->fileName);
        [$fileName, $rawExtension] = FileHelpers::splitNameAndExtension($sanitizedFileName);
        $extension = File::guessExtension($this->pathToFile) ?? $rawExtension;

        $this->fileName = MediaHub::getFileNamer()->formatFileName($fileName, $extension);

        $mediaClass = MediaHub::getMediaModel();
        $media = new $mediaClass($this->modelData);

        $media->file_name = $this->fileName;
        $media->collection_name = $this->collectionName;
        $media->size = File::size($this->pathToFile);
        $media->mime_type = File::mimeType($this->pathToFile);
        $media->original_file_hash = FileHelpers::getFileHash($this->pathToFile);
        $media->data = [];
        $media->conversions = [];

        $media->disk = $this->getDiskName();
        $this->ensureDiskExists($media->disk);

        $media->conversions_disk = $this->getConversionsDiskName();
        $this->ensureDiskExists($media->conversions_disk);

        $media->save();

        $this->filesystem->create($this->pathToFile, $media, $this->fileName);

        MediaHubOptimizeOriginalMediaJob::dispatch($media);
        MediaHubCreateConversionsJob::dispatch($media);

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
}
