<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

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
        if ($this->disk) {
            $fileContents = Storage::disk($this->disk)->get($this->getKey());
        } else {
            $fileContents = file_get_contents($this->getKey());
        }

        $tempFilePath = $this->getTemporaryFilePath();
        file_put_contents($tempFilePath, $fileContents);

        $this->originalFileName = $this->getName();
        $this->key = $tempFilePath;

        return $tempFilePath;
    }

    protected function getTemporaryFilePath()
    {
        return tempnam(sys_get_temp_dir(), 'remote-file-');
    }
}
