# Quick Start

## Basic Conversion with Resvg (default)

```php
use Laratusk\Larasvg\Facades\SvgConverter;

// SVG to PNG
SvgConverter::open('/path/to/file.svg')
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->toFile('/path/to/output.png');
```

## Using Inkscape

```php
// Switch provider for this call
SvgConverter::using('inkscape')
    ->open('/path/to/file.svg')
    ->setFormat('pdf')
    ->toFile('/path/to/output.pdf');
```

## Auto-Generate Output Path

When you don't specify an output path, the converted file is saved next to the input file:

```php
// Saves to /path/to/file.png
$outputPath = SvgConverter::open('/path/to/file.svg')
    ->setFormat('png')
    ->convert();
```

## Save to Laravel Disk

```php
SvgConverter::open('/path/to/file.svg')
    ->toDisk('s3', 'exports/image.png');
```

## Stream to HTTP Response

```php
Route::get('/convert', function () {
    $binary = SvgConverter::open(storage_path('app/logo.svg'))
        ->setDimensions(512, 512)
        ->toStdout('png');

    return response($binary)
        ->header('Content-Type', 'image/png');
});
```

## Check Provider Version

```php
$version = SvgConverter::version();            // Default provider
$version = SvgConverter::version('inkscape');   // Specific provider
```
