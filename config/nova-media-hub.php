<?php

return [
    // Table name
    'table_name' => 'media_hub',

    // Base URL path
    'base_path' => 'media-hub',

    // Class configurations
    'model' => \Outl1ne\NovaMediaHub\Models\Media::class,
    'file_namer' => \Outl1ne\NovaMediaHub\MediaHandler\Support\FileNamer::class,
    'path_maker' => \Outl1ne\NovaMediaHub\MediaHandler\Support\PathMaker::class,

    // Disk configurations
    'disk_name' => 'public',
    'conversions_disk_name' => 'public',

    // Misc configuration
    'path_prefix' => 'media',
    'max_file_size_in_kb' => 3000,

    'user_can_create_collections' => true,

    'collections' => [
        'default',
        'banners',
        'product_images',
    ],

    // ------------------------------
    // -- Conversion configurations
    // ------------------------------

    // TODO
    // Conversions
    // '*' for all collections
    // 'collection_name' => ['conversion_name' => [options]]
    'conversions' => [
        '*' => [
            'thumbnail' => [
                'width' => 150,
                'height' => 150,
                'crop' => true,
                'format' => 'jpeg',
            ]
        ],
    ],

    // TODO
    'original_image_manipulations' => [
        // False if you don't want the original image to be optimized
        // The mime type must still be in the optimizable_mime_types array
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
        'image/png',
        'image/jpeg',
        'image/gif',
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
