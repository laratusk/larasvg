<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SVG Converter Provider
    |--------------------------------------------------------------------------
    |
    | The default provider to use for SVG conversions. Supported: "resvg",
    | "inkscape", "rsvg-convert", "cairosvg".
    | Resvg is recommended for PNG-only conversions due to its speed and simplicity.
    | Inkscape supports more formats (PDF, EPS, PS, EMF, WMF).
    | Rsvg-convert supports PNG, PDF, PS, EPS, SVG — lightweight and widely available.
    | CairoSVG supports PNG, PDF, PS, SVG — Python-based, uses the Cairo 2D library.
    |
    */

    'default' => env('SVG_CONVERTER_DRIVER', 'resvg'),

    /*
    |--------------------------------------------------------------------------
    | Driver Map
    |--------------------------------------------------------------------------
    |
    | Maps provider names to their converter class. Adding an entry here is
    | all that is required to register a new driver — no code changes needed.
    | Each class must extend AbstractConverter or implement the Provider contract.
    |
    | Third-party packages can register custom drivers by publishing a config
    | merge or calling SvgConverter::extend('name', MyConverter::class) in a
    | service provider.
    |
    */

    'drivers' => [
        'resvg' => \Laratusk\Larasvg\Converters\ResvgConverter::class,
        'inkscape' => \Laratusk\Larasvg\Converters\InkscapeConverter::class,
        'rsvg-convert' => \Laratusk\Larasvg\Converters\RsvgConvertConverter::class,
        'cairosvg' => \Laratusk\Larasvg\Converters\CairosvgConverter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the binary path and timeout for each provider.
    |
    */

    'providers' => [

        'resvg' => [
            'binary' => env('RESVG_PATH', 'resvg'),
            'timeout' => env('RESVG_TIMEOUT', 60),
        ],

        'inkscape' => [
            'binary' => env('INKSCAPE_PATH', 'inkscape'),
            'timeout' => env('INKSCAPE_TIMEOUT', 60),
        ],

        'rsvg-convert' => [
            'binary' => env('RSVG_CONVERT_PATH', 'rsvg-convert'),
            'timeout' => env('RSVG_CONVERT_TIMEOUT', 60),
        ],

        'cairosvg' => [
            'binary' => env('CAIROSVG_PATH', 'cairosvg'),
            'timeout' => env('CAIROSVG_TIMEOUT', 60),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The default filesystem disk to use when reading/writing files via
    | the openFromDisk() and toDisk() methods. This should match one
    | of the disks defined in your config/filesystems.php.
    |
    */

    'default_disk' => env('SVG_CONVERTER_DISK', 'local'),

];
