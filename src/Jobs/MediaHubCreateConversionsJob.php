<?php

namespace Outl1ne\NovaMediaHub\Jobs;

use Illuminate\Bus\Queueable;
use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Queue\SerializesModels;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class MediaHubCreateConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    protected $mediaId = null;

    public function __construct(Media $media)
    {
        $this->mediaId = $media->id;
        $this->onQueue(MediaHub::getImageConversionsJobQueue());
    }

    public function handle()
    {
        $media = Media::find($this->mediaId);
        if (!$media) return;

        $conversions = $this->getConversionForMedia($media);

        // TODO
    }

    protected function getConversionForMedia(Media $media)
    {
        $allConversions = MediaHub::getConversions();

        $appliesToAllConversions = $allConversions['*'] ?? [];
        $appliesToCollectionConv = $allConversions[$media->collection_name] ?? [];

        return array_merge(
            $appliesToAllConversions,
            $appliesToCollectionConv,
        );
    }
}
