<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

class FileNamer
{
    public function formatFileName(string $fileName, string $extension)
    {
        return "$fileName.$extension";
    }

    public function formatConversionFileName(string $fileName, string $extension, $conversion)
    {
        return "$fileName.$extension";
    }
}
