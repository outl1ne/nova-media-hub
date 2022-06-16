<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Finfo;

class FileHelpers
{
    const UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    public static function getHumanReadableSize(int $sizeInBytes): string
    {
        if ($sizeInBytes == 0) return '0 ' . static::UNITS[1];

        for ($i = 0; $sizeInBytes > 1024; $i++) {
            $sizeInBytes /= 1024;
        }

        return round($sizeInBytes, 2) . ' ' . static::UNITS[$i];
    }

    public static function getMimeType(string $path): string
    {
        $finfo = new Finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($path);
    }
}
