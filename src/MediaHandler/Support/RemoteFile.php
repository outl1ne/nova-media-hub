<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RemoteFile
{
    protected $key = null;
    protected $disk = null;
    protected $originalFileName = null;

    public function __construct(string $key, string|null $disk = null)
    {
        $this->key = $key;
        $this->disk = $disk;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getFilename(): string
    {
        return $this->originalFileName ?? basename($this->key);
    }

    public function getName(): string
    {
        return pathinfo($this->getFilename(), PATHINFO_FILENAME);
    }

    public function downloadFileToCurrentFilesystem()
    {
        $tempFilePath = FileHelpers::getTemporaryFilePath();

        if ($this->disk) {
            Storage::disk('local')->writeStream($tempFilePath, Storage::disk($this->disk)->readStream($this->getKey()));
        } else {
            Http::get($this->getKey())->sink($tempFilePath);
        }

        $this->originalFileName = $this->getName();
        $this->key = $tempFilePath;

        return $tempFilePath;
    }
}
