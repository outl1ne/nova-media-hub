<?php

namespace Outl1ne\NovaMediaHub\Jobs;

use Illuminate\Bus\Queueable;
use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Queue\SerializesModels;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Outl1ne\NovaMediaHub\MediaHandler\Support\MediaOptimizer;

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

        $conversions = MediaHub::getConversionForMedia($media);

        foreach ($conversions as $conversionName => $conversion) {
            MediaOptimizer::makeConversion($media, $conversionName, $conversion);
        }
    }
}
