<?php

use Spatie\Image\Manipulations;

return [
    // Table name
    'table_name' => 'media_hub',

    // Base URL path in Nova
    'base_path' => 'media-hub',

    // Classes configuration
    'model' => \Outl1ne\NovaMediaHub\Models\Media::class,
    'file_namer' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer::class,

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
            'thumbnail' => [
                'width' => 150,
                'height' => 150,
                'fit' => Manipulations::FIT_MAX,
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
    ],

    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m85', // set maximum quality to 85%
            '--force', // ensure that progressive generation is always done also if a little bigger
            '--strip-all', // this strips out all text information such as comments and EXIF data
            '--all-progressive', // this will make sure the resulting image is a progressive one
        ],

        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force', // required parameter for this package
        ],

        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2', // this set the optimization level to two (multiple IDAT compression trials)
            '-quiet', // required parameter for this package
        ],

        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupIDs', // disabling because it is known to cause troubles
        ],

        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b', // required parameter for this package
            '-O3', // this produces the slowest but best results
        ],

        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6', // for the slowest compression method in order to get the best compression.
            '-pass 10', // for maximizing the amount of analysis pass.
            '-mt', // multithreading for some speed improvements.
            '-q 90', //quality factor that brings the least noticeable changes.
        ],
    ],
];
