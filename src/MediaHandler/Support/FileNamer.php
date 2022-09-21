<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

class FileNamer
{
    public function formatFileName(string $fileName, string $extension)
    {
        return "{$fileName}.{$extension}";
    }

    public function formatConversionFileName(string $fileName, string $extension, $conversion)
    {
        return "{$fileName}.{$conversion}.{$extension}";
    }

    public static function encode(string $fileName): string
    {
        // For backwards compatibility.
        // Encodes fileName while avoiding double encoding.
        return urlencode(urldecode($fileName));
    }
}
