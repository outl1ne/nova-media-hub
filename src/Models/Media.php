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
        return Storage::disk($this->disk)->url("{$this->path}{$this->file_name}");
    }

    public function getThumbnailUrlAttribute()
    {
        $thumbnailConversionName = MediaHub::getThumbnailConversionName();
        if (!$thumbnailConversionName) return null;

        $thumbnailName = $this->conversions[$thumbnailConversionName] ?? null;
        if (!$thumbnailName) return null;

        return Storage::disk($this->conversions_disk)->url($this->conversionsPath . $thumbnailName);
    }
}
