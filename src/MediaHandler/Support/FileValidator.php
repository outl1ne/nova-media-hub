<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Outl1ne\NovaMediaHub\MediaHub;
use Outl1ne\NovaMediaHub\Exceptions\FileTooLargeException;
use Outl1ne\NovaMediaHub\Exceptions\MimeTypeNotAllowedException;

class FileValidator
{
    public function validateFile(string $collectionName, string $localFilePath, string $fileName, string $extension, string $mimeType, int $fileSize): bool
    {
        $this->validateFileSize($fileSize, $fileName);
        $this->validateMimeType($mimeType, $fileName);
        return true;
    }

    protected function validateFileSize(int $fileSize, string $fileName)
    {
        $maxSizeBytes = MediaHub::getMaxFileSizeInBytes();

        if ($maxSizeBytes && $fileSize > $maxSizeBytes) {
            throw new FileTooLargeException("File size {$fileSize} bytes exceeds the maximum allowed of {$maxSizeBytes} ({$fileName}).");
        }
    }

    protected function validateMimeType(string $mimeType, string $fileName)
    {
        $allowedMimeTypes = MediaHub::getAllowedMimeTypes();

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new MimeTypeNotAllowedException("Mime type {$mimeType} is not allowed ({$fileName}).");
        }
    }
}
