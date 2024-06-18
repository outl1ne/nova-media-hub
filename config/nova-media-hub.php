<?php

use Spatie\Image\Enums\Fit;

return [
    // Table name
    'table_name' => 'media_hub',

    // Base URL path in Nova
    'base_path' => 'media-hub',

    // Classes configuration
    'model' => \Outl1ne\NovaMediaHub\Models\Media::class,
    'file_namer' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer::class,
    'file_validator' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileValidator::class,
    'media_manipulator' =>  \Outl1ne\NovaMediaHub\MediaHandler\Support\MediaManipulator::class,

    // This default PathMaker puts files in a /prefix/<mediaid>/* structure
    'path_maker' => \Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker::class,

    // If you want files to be in a /prefix/year/month/<mediaid>/* folder structure, use DatePathMaker instead
    // 'path_maker' => \Outl1ne\NovaMediaHub\MediaHandler\Support\DatePathMaker::class,

    // Disk configurations
    'disk_name' => 'public',
    'conversions_disk_name' => 'public',

    // Path configuration
    'path_prefix' => 'media',

    // Locales (for translatable alt texts and titles)
    // Set to null if you don't want translatability
    // Example value: ['et' => 'Estonian', 'en' => 'English']
    'locales' => null,

    // File size upper limit
    'max_uploaded_file_size_in_kb' => 15000,

    // Allowed mime types list
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        'image/webp',
        'video/mp4',
        'text/csv',
        'application/pdf',
    ],

    // Job queue configuration
    'original_image_manipulations_job_queue' => null,
    'image_conversions_job_queue' => null,

    // Default collections that will always be displayed (even when empty)
    'collections' => [
        'default',
    ],

    // Defines whether user can create collections in the "Upload media" modal
    // If set to false, the user can only use the collections defined in the config
    'user_can_create_collections' => true,

    // Thumbnail conversion name
    // If you want Nova to use thumbnails instead of full size files
    // when dispalying media, define the name of the conversion here
    'thumbnail_conversion_name' => null,

    // Image manipulation driver
    // If null, it will try to read config('image.driver')
    // Final fallback is 'gd'
    // Options: null, 'imagick', 'gd'
    'image_driver' => null,

    // -- Conversions
    // 'collection_name' => ['conversion_name' => [options]]
    // Use '*' as wildcard for all collections
    'image_conversions' => [
        '*' => [
            // Disable oiriginal image optimizations on a per-collection basis
            // Only accepts 'false' as an argument to disable original image manipulations
            // 'original' => false,

            'thumbnail' => [
                // Image format, null for same as original
                // Other options: jpg, pjpg, png, gif, webp, avif, tiff
                'format' => null,
                'width' => 150,
                'height' => 150,
                'fit' => Fit::Max,
            ],
        ],
    ],

    'original_image_manipulations' => [
        // Set to false if you don't want the original image to be optimized
        // The mime type must still be in the optimizable_mime_types array
        // If set to false, will also disable resizing in max_dimensions
        'optimize' => true,

        // Maximum number of pixels in height or width, will be scaled down to this number
        // Set to null if you don't want the original image to be resized
        'max_dimensions' => 2000,
    ],



    // ------------------------------
    // -- Image optimizations
    // ------------------------------

    // NB! Must have a matching image_optimizer configured and binary for it to work
    'optimizable_mime_types' => [
        'image/jpeg',
        // 'image/png',
        // 'image/gif',
        // 'image/tiff',
    ],
];
