<?php

declare(strict_types=1);

return [

    /*
     * The disk on which to store added files and derived images.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * The maximum file size of an item in bytes.
     * Adding a file that is larger than this will result in an exception.
     */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 5242880), // 5MB default

    /*
     * This queue will be used to perform image conversions.
     * Leave empty to perform conversions synchronously.
     */
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS', true),

    'queue_name' => env('QUEUE_NAME', 'default'),

    /*
     * By default all conversions are named 'conversions'.
     * You can customize the name of the folder.
     */
    'conversions_disk' => env('CONVERSIONS_DISK', 'public'),

    'conversion_file_namer' => \Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    /*
     * The class that contains the strategy for determining a media file's path.
     */
    'path_generator' => \Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    /*
     * The path where to store temporary files while performing image conversions.
     */
    'temporary_directory_path' => null,

    /*
     * The class that contains the strategy for generating the filename of a media file.
     */
    'file_namer' => \Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    /*
     * The class that contains the strategy for generating the filename of a conversion.
     */
    'conversion_file_namer' => \Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    /*
     * The engine to use for image conversions.
     * Currently supported: "gd", "imagick"
     */
    'image_driver' => env('IMAGE_DRIVER', 'imagick'),

    /*
     * Imagemagick (the ImageMagick library used by the imagick PHP extension)
     * has been deprecated. We recommend using ImageMagick 7 or ImageMagick 6.
     */
    'imagemagick_path' => env('IMAGEMAGICK_PATH', '/usr/bin/convert'),

    /*
     * Enable the generation of responsive images.
     * `media-library-pro` must be installed when using this feature.
     */
    'enable_responsive_images' => true,

    /*
     * This will queue the generation of responsive images.
     * In order for this to work queue_conversions_by_default must be set to true.
     */
    'queue_responsive_images' => true,

    /*
     * This value determines how many breakpoints are added to a responsive image.
     * The images will be evenly spaced between 20px and the max width of the image.
     */
    'responsive_images_count' => 8,

    /*
     * Conversions defined in the 'Conversions' class.
     */
    'conversions' => [
        'thumb' => [
            'format' => 'webp',
            'manipulations' => [
                'quality' => 80,
            ],
            'width' => 300,
            'height' => 300,
            'mode' => 'crop',
        ],
        'medium' => [
            'format' => 'webp',
            'manipulations' => [
                'quality' => 85,
            ],
            'width' => 600,
            'height' => 600,
            'mode' => 'fit',
        ],
        'large' => [
            'format' => 'webp',
            'manipulations' => [
                'quality' => 90,
            ],
            'width' => 1200,
            'height' => 1200,
            'mode' => 'fit',
        ],
        'responsive' => [
            'format' => 'webp',
            'manipulations' => [
                'quality' => 85,
            ],
            'responsive' => true,
        ],
    ],

    /*
     * By default the URL of a media file will not change when the file is updated.
     * Passing `true` here will append a ?v=xx query string to a media files URL when its updated.
     */
    'version_urls' => true,

    /*
     * If set to true, only media files that have been explicitly registered as allowed
     * for download will be downloadable.
     */
    'media_download_validation' => false,

    /*
     * This function is called before writing the media path to the database.
     */
    'sanitize_filename_callback' => null,

    /*
     * This function is called to determine the public URL of the media.
     */
    'url_generator' => \Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

];
