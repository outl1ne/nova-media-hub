<?php

namespace Outl1ne\NovaMediaHub\Models;

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer;

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
        return Storage::disk($this->disk)->url(FileNamer::encode("{$this->path}{$this->file_name}"));
    }

    public function getThumbnailUrlAttribute()
    {
        $thumbnailConversionName = MediaHub::getThumbnailConversionName();
        if (!$thumbnailConversionName) return null;

        $thumbnailName = $this->conversions[$thumbnailConversionName] ?? null;
        if (!$thumbnailName) return null;

        return Storage::disk($this->conversions_disk)->url(FileNamer::encode($this->conversionsPath . $thumbnailName));
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
