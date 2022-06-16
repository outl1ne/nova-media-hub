<?php

return [
    // Table name
    'table_name' => 'media_hub',

    // Base URL path
    'base_path' => 'media-hub',

    'model' => \Outl1ne\NovaMediaHub\Models\Media::class,

    'file_namer' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer::class,

    'disk' => 'public',
    'conversion_disk' => 'public',

    'max_file_size_in_kb' => 3000,
];
