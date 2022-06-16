<?php

return [
    // Table name
    'table_name' => 'media_hub',

    // Base URL path
    'base_path' => 'media-hub',

    'model' => \Outl1ne\NovaMediaHub\Models\Media::class,

    'file_namer' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer::class,
    'path_maker' => \Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker::class,

    'disk_name' => 'public',
    'conversions_disk_name' => 'public',

    'max_file_size_in_kb' => 3000,
];
