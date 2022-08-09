<?php

namespace Outl1ne\NovaMediaHub\MediaHandler\Support;

use Finfo;
use Illuminate\Support\Str;

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

    public static function getBase64FileInfo($base64): ?array
    {
        $finfo = new Finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer(base64_decode($base64));
        if (!Str::startsWith($mimeType, 'image')) return null;
        $extension = static::getExtensionFromMimeType($mimeType);
        return [$mimeType, $extension];
    }

    public static function getExtensionFromMimeType(string $mimeType): ?string
    {
        if (!Str::startsWith($mimeType, 'image')) return null;
        return explode('/', $mimeType)[1] ?? null;
    }

    public static function getFileHash(string $path): string
    {
        if (!$path || !is_file($path)) return null;

        $fileStream = fopen($path, 'r');
        $fileHash = md5(fread($fileStream, 1000000));
        fclose($fileStream);

        return $fileHash;
    }

    public static function sanitizeFileName($fileName)
    {
        return str_replace(['#', '/', '\\', ' ', '?', '='], '-', $fileName);
    }

    // Returns [$fileName, $extension]
    public static function splitNameAndExtension(string $fileName): array
    {
        $name = pathinfo($fileName, PATHINFO_BASENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $name = mb_substr($name, 0, - (mb_strlen($extension) + 1));
        return [$name, $extension];
    }
}
