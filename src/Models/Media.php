<?php

namespace Outl1ne\NovaMediaHub\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{

    use HasUuids;

    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'conversions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'optimized_at' => 'datetime',
    ];

    protected $appends = [
        'url',
        'thumbnail_url'
    ];

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
        return $this->createUrl($this->disk, $this->path . $this->file_name);
    }

    public function createUrl(string $disk_name, string $file_path): string
    {

        // check if disk has temporary url method
        $disk = Storage::disk($disk_name);

        if (method_exists($disk, 'temporaryUrl')) {
                
            try {
                return $disk->temporaryUrl($file_path, now()->addMinutes((int) config('nova-media-hub.temporary_url_expiration', 60)));
            } catch (\RuntimeException $e) {
                return $disk->url($file_path);
            }
        }
        
        return $disk->url($file_path);
        
    }

    public function getThumbnailUrlAttribute()
    {
        $forConversion = MediaHub::getThumbnailConversionName();

        if (!$forConversion) {
            return $this->url;
        }

        $conversionName = $this->conversions[$forConversion] ?? null;

        if (empty($conversionName)) {
            return null;
        }

        return $this->createUrl($this->conversions_disk, $this->conversionsPath . $conversionName);
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
