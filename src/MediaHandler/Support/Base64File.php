<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Illuminate\Support\Str;
use Outl1ne\NovaMediaHub\Exceptions\UnknownFileTypeException;

class Base64File
{
    protected $base64Data = null;
    protected $fileName = null;
    protected $disk = null;

    public function __construct(string $base64Data, string|null $fileName = null)
    {
        $this->base64Data = $base64Data;
        $this->fileName = $fileName;
    }

    public function getBase64Data(): string
    {
        return $this->base64Data;
    }

    public function getFilename(): string
    {
        return $this->fileName ?? microtime();
    }

    public function saveBase64ImageToTemporaryFile()
    {
        $fileData = $this->base64Data;
        if (Str::contains($fileData, [';', ','])) $fileData = explode(',', $fileData)[1];
        [$mimeType, $extension] = FileHelpers::getBase64FileInfo($fileData);
        if (!$mimeType && !$extension) throw new UnknownFileTypeException('File had no detectable mime-type or extension.');
        $tmpFilePath = $this->getTemporaryFilePath() . ".$extension";
        file_put_contents($tmpFilePath, base64_decode($fileData));
        $this->fileName = $this->getFilename() . ".$extension";
        return $tmpFilePath;
    }

    protected function getTemporaryFilePath()
    {
        return tempnam(sys_get_temp_dir(), 'base64-file-');
    }
}
