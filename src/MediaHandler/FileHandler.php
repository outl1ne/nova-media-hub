<?php

namespace Outl1ne\NovaMediaHub\MediaHandler;

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Support\Facades\File;
use Outl1ne\NovaMediaHub\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Base64File;
use Outl1ne\NovaMediaHub\MediaHandler\Support\RemoteFile;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Filesystem;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileHelpers;
use Outl1ne\NovaMediaHub\Jobs\MediaHubOptimizeAndConvertJob;
use Outl1ne\NovaMediaHub\Exceptions\NoFileProvidedException;
use Outl1ne\NovaMediaHub\Exceptions\FileValidationException;
use Outl1ne\NovaMediaHub\Exceptions\UnknownFileTypeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Outl1ne\NovaMediaHub\Exceptions\FileDoesNotExistException;
use Outl1ne\NovaMediaHub\Exceptions\DiskDoesNotExistException;
use Illuminate\Support\Facades\Event;

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
    protected bool $deleteOriginal = false;
    protected bool $allowDuplicates = false;
    protected ?\Closure $shouldSave = null;

    public function __construct()
    {
        $this->filesystem = app()->make(Filesystem::class);
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
            if ($file->getError()) {
                throw new FileValidationException($file->getErrorMessage());
            } else {
                $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
                $this->fileName = $file->getClientOriginalName();
                return $this;
            }
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->fileName = pathinfo($file->getFilename(), PATHINFO_BASENAME);
            return $this;
        }

        if ($file instanceof Base64File) {
            $filePath = $file->saveBase64ImageToTemporaryFile();
            $this->pathToFile = $filePath;
            $this->fileName = pathinfo($file->getFilename(), PATHINFO_BASENAME);
            return $this;
        }

        $this->file = null;
        throw new UnknownFileTypeException();
    }

    public function deleteOriginal($deleteOriginal = true)
    {
        $this->deleteOriginal = $deleteOriginal;
        return $this;
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

    public function shouldSave(\Closure $shouldSave)
    {
        $this->shouldSave = $shouldSave;
        return $this;
    }

    public function save($file = null): ?Media
    {
        if (!empty($file)) $this->withFile($file);
        if (empty($this->file)) throw new NoFileProvidedException();
        if (!is_file($this->pathToFile)) throw new FileDoesNotExistException($this->pathToFile);

        try {
            return $this->saveFile();
        } finally {
            // Ensure cleanup
            if ($this->deleteOriginal && is_file($this->pathToFile)) {
                unlink($this->pathToFile);
            }
        }
    }

    public function allowDuplicates($allowDuplicates = true)
    {
        $this->allowDuplicates = $allowDuplicates;
        return $this;
    }

    private function saveFile(): ?Media
    {
        // Check if file already exists
        $fileHash = FileHelpers::getFileHash($this->pathToFile);

        if (!$this->allowDuplicates) {
            if (config('nova-media-hub.check_legacy_hashes')) {
                $legacyFileHash = FileHelpers::getLegacyFileHash($this->pathToFile);

                $existingMedia = MediaHub::getQuery()->whereIn('original_file_hash', [$fileHash, $legacyFileHash])->first();
            } else {
                $existingMedia = MediaHub::getQuery()->where('original_file_hash', $fileHash)->first();
            }

            if ($existingMedia) {
                $existingMedia->updated_at = now();
                $existingMedia->save();
                $existingMedia->wasExisting = true;

                // Delete original
                if ($this->deleteOriginal && is_file($this->pathToFile)) {
                    unlink($this->pathToFile);
                }

                if (! $existingMedia->optimized_at) {
                    MediaHubOptimizeAndConvertJob::dispatch($existingMedia);
                }

                return $existingMedia;
            }
        }

        $sanitizedFileName = FileHelpers::sanitizeFileName($this->fileName);
        [$fileName, $rawExtension] = FileHelpers::splitNameAndExtension($sanitizedFileName);
        $extension = File::guessExtension($this->pathToFile) ?? $rawExtension;
        $mimeType = File::mimeType($this->pathToFile);
        $fileSize = File::size($this->pathToFile);

        $this->fileName = MediaHub::getFileNamer()->formatFileName($fileName, $extension);

        // Validate file
        $fileValidator = MediaHub::getFileValidator();
        $fileValidator->validateFile($this->collectionName, $this->pathToFile, $this->fileName, $extension, $mimeType, $fileSize);

        $mediaClass = MediaHub::getMediaModel();

        /** @var \Outl1ne\NovaMediaHub\Models\Media */
        $media = new $mediaClass();
        $media->forceFill($this->modelData ?? []);

        if ($media->id) {
            $media->exists = true;
        }

        $media->file_name = $this->fileName;
        $media->collection_name ??= $this->collectionName;
        $media->size = $fileSize;
        $media->mime_type = $mimeType;
        $media->original_file_hash = $fileHash;
        $media->data = [];
        $media->conversions = [];

        $media->disk = $this->getDiskName();
        $this->ensureDiskExists($media->disk);

        $media->conversions_disk = $this->getConversionsDiskName();
        $this->ensureDiskExists($media->conversions_disk);

        // Last pre-save check
        if (is_callable($this->shouldSave)) {
            $shouldSave = call_user_func($this->shouldSave, $media);
            if (!$shouldSave) {
                if ($this->deleteOriginal && $this->pathToFile) {
                    unlink($this->pathToFile);
                }
                return null;
            }
        }

        $media->save();

        if ($this->filesystem->copyFileToMediaLibrary($this->pathToFile, $media, $this->fileName, Filesystem::TYPE_ORIGINAL, $this->deleteOriginal)) {

            Event::dispatch('nova-media-hub.upload.success', [
                'media' => $media,
                'filehandler' => $this
            ]);
            
            MediaHubOptimizeAndConvertJob::dispatch($media);

        } else {

            // Emit an event
            Event::dispatch('nova-media-hub.upload.failed', [
                'media' => $media,
                'filehandler' => $this
            ]);

            return null;
        }


        return $media;
    }

    // Helpers
    protected function getDiskName(): string
    {
        return $this->diskName ?:  config('nova-media-hub.disks.' . $this->collectionName . '.assets') ?: config('nova-media-hub.disk_name');
    }

    protected function getConversionsDiskName(): string
    {
        return $this->conversionsDiskName ?: config('nova-media-hub.disks.' . $this->collectionName . '.conversions') ?: config('nova-media-hub.conversions_disk_name');
    }

    protected function ensureDiskExists(string $diskName)
    {
        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw new DiskDoesNotExistException($diskName);
        }
    }
}
