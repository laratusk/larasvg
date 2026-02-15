# Quick Start

## Basic Conversion with Resvg (default)

```php
use Laratusk\Larasvg\Facades\SvgConverter;

// SVG to PNG
SvgConverter::open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->toFile(storage_path('app/output.png'));
```

::: tip
The `open()` method accepts an **absolute file path**. Use Laravel's path helpers such as `resource_path()`, `storage_path()`, `base_path()`, or `public_path()` to build the correct path. The same applies to `toFile()`.
:::

## Using Inkscape

```php
// Switch provider for this call
SvgConverter::using('inkscape')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->toFile(storage_path('app/output.pdf'));
```

## Using rsvg-convert

```php
SvgConverter::using('rsvg-convert')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->setPageWidth('210mm')
    ->setPageHeight('297mm')
    ->keepAspectRatio()
    ->toFile(storage_path('app/output.pdf'));
```

## Using CairoSVG

```php
SvgConverter::using('cairosvg')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->setScale(2.0)
    ->toFile(storage_path('app/output.pdf'));
```

## Auto-Generate Output Path

When you don't specify an output path, the converted file is saved next to the input file:

```php
$outputPath = SvgConverter::open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->convert();
// => resources/svg/file.png
```

## Save to Laravel Disk

```php
SvgConverter::open(resource_path('svg/file.svg'))
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
$version = SvgConverter::version();                  // Default provider
$version = SvgConverter::version('inkscape');         // Specific provider
$version = SvgConverter::version('rsvg-convert');     // Specific provider
```
