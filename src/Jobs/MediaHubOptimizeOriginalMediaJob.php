<?php

namespace Outl1ne\NovaMediaHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Outl1ne\NovaMediaHub\Models\Media;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Outl1ne\NovaMediaHub\MediaHandler\Support\MediaOptimizer;

class MediaHubOptimizeOriginalMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    protected $mediaId = null;

    public function __construct(Media $media)
    {
        $this->mediaId = $media->id;
    }

    public function handle()
    {
        $media = Media::find($this->mediaId);
        if (!$media) return;

        MediaOptimizer::optimizeOriginalFile($media);
    }
}
