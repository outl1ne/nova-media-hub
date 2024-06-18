<?php

namespace Outl1ne\NovaMediaHub\Models;

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $casts = [
        'data' => 'array',
        'conversions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'optimized_at' => 'datetime',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(MediaHub::getTableName());
    }

    public function getPathAttribute()
    {
        $pathMaker = MediaHub::getPathMaker();
        return $pathMaker->getPath($this);
    }

    public function getConversionsPathAttribute()
    {
        $pathMaker = MediaHub::getPathMaker();
        return $pathMaker->getConversionsPath($this);
    }

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path . $this->file_name);
    }

    public function getUrl(?string $forConversion = null)
    {
        if (!$forConversion) return $this->url;

        $conversionName = $this->conversions[$forConversion] ?? null;
        if (empty($conversionName)) return null;

        return Storage::disk($this->conversions_disk)->url($this->conversionsPath . $conversionName);
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->getUrl(MediaHub::getThumbnailConversionName());
    }

    public function getExtension()
    {
        $extension = (new \Symfony\Component\Mime\MimeTypes)->getExtensions($this->mime_type)[0];
        if (!$extension) $extension = str($this->file_name)->afterLast('.')->toString();

        // Fix issue with Imagick not recognizing "tif" as a valid format
        if ($extension === 'tif') $extension = 'tiff';

        return $extension;
    }

    public function formatForNova()
    {
        return [
            'id' => $this->id,
            'collection_name' => $this->collection_name,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnailUrl,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'file_name' => $this->file_name,
            'data' => $this->data,
            'conversions' => $this->conversions,
        ];
    }
}
