<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Outl1ne\NovaMediaHub\Exceptions\FileTooLargeException;
use Outl1ne\NovaMediaHub\MediaHub;

class FileValidator
{
    public function validateFile(string $localFilePath, string $fileName, string $extension, string $mimeType, int $fileSize): bool
    {
        $maxSizeBytes = MediaHub::getMaxFileSizeInBytes();

        if ($maxSizeBytes && $fileSize > $maxSizeBytes) {
            throw new FileTooLargeException($localFilePath);
        }

        return true;
    }
}
