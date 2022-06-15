<?php

namespace Outl1ne\NovaMediaHub\MediaHandler;

use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;
use Outl1ne\NovaMediaHub\Exceptions\FileTooLargeException;
use Outl1ne\NovaMediaHub\Exceptions\UnknownFileTypeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Outl1ne\NovaMediaHub\Exceptions\FileDoesNotExistException;

class MediaHandler
{
    /** @var Filesystem */
    protected $filesystem;

    protected string $fileName = '';
    protected string $pathToFile = '';
    protected string $diskName = '';
    protected string $conversionsDiskName = '';

    public function __construct(Filesystem $fileSystem)
    {
        $this->filesystem = $fileSystem;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);
            return $this;
        }

        if ($file instanceof RemoteFile) {
            $this->pathToFile = $file->getKey();
            $this->setFileName($file->getFilename());
            $this->mediaName = $file->getName();
            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            return $this;
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            return $this;
        }

        throw new UnknownFileTypeException();
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    // ------------
    // Finalize
    // ------------

    public function saveFromRemote(string $collectionName = 'default', string $diskName = ''): Media
    {
        $storage = Storage::disk($this->file->getDisk());

        if (!$storage->exists($this->pathToFile)) {
            throw new FileDoesNotExistException($this->pathToFile);
        }

        if ($storage->size($this->pathToFile) > config('media-library.max_file_size')) {
            throw new FileTooLargeException($this->pathToFile . ' (' . $storage->size($this->pathToFile) . ')');
        }

        $mediaClass = MediaHub::getMediaModel();
        $media = new $mediaClass();

        $media->name = $this->mediaName;

        $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
        $fileName = app(config('media-library.file_namer'))->originalFileName($sanitizedFileName);
        $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($diskName, $collectionName);
        $this->ensureDiskExists($media->disk);
        $media->conversions_disk = $this->determineConversionsDiskName($media->disk, $collectionName);
        $this->ensureDiskExists($media->conversions_disk);

        $media->collection_name = $collectionName;

        $media->mime_type = $storage->mimeType($this->pathToFile);
        $media->size = $storage->size($this->pathToFile);
        $media->custom_properties = $this->customProperties;

        $media->generated_conversions = [];
        $media->responsive_images = [];

        $media->manipulations = $this->manipulations;

        if (filled($this->customHeaders)) {
            $media->setCustomHeaders($this->customHeaders);
        }

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }

    public function save(string $collectionName = 'default', string $diskName = ''): Media
    {
        $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
        $fileName = app(config('media-library.file_namer'))->originalFileName($sanitizedFileName);
        $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));

        if ($this->file instanceof RemoteFile) {
            return $this->saveFromRemote($collectionName, $diskName);
        }

        if (!is_file($this->pathToFile)) throw new FileDoesNotExistException($this->pathToFile);

        if (filesize($this->pathToFile) > config('media-library.max_file_size')) {
            throw new FileTooLargeException($this->pathToFile);
        }

        $mediaClass = MediaHub::getMediaModel();
        $media = new $mediaClass();

        $media->name = $this->mediaName;

        $media->file_name = $this->fileName;

        $media->disk = $this->determineDiskName($diskName, $collectionName);
        $this->ensureDiskExists($media->disk);

        $media->conversions_disk = $this->determineConversionsDiskName($media->disk, $collectionName);
        $this->ensureDiskExists($media->conversions_disk);

        $media->collection_name = $collectionName;

        $media->mime_type = File::getMimeType($this->pathToFile);
        $media->size = filesize($this->pathToFile);

        if (!is_null($this->order)) {
            $media->order_column = $this->order;
        }

        $media->custom_properties = $this->customProperties;

        $media->generated_conversions = [];
        $media->responsive_images = [];

        $media->manipulations = $this->manipulations;

        if (filled($this->customHeaders)) {
            $media->setCustomHeaders($this->customHeaders);
        }

        $media->fill($this->properties);

        $this->attachMedia($media);

        return $media;
    }
}
