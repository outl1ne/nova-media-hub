<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

class RemoteFile
{
    protected $key = null;
    protected $disk = null;

    public function __construct(string $key, string $disk)
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
        return basename($this->key);
    }

    public function getName(): string
    {
        return pathinfo($this->getFilename(), PATHINFO_FILENAME);
    }
}
