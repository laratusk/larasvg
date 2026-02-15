# Stdout Streaming

LaraSVG can output conversion results directly to stdout, which is useful for streaming binary data in HTTP responses without writing temporary files.

## Basic Usage

```php
$binary = SvgConverter::open(resource_path('svg/logo.svg'))
    ->setDimensions(512, 512)
    ->toStdout('png');
```

The `toStdout()` method returns the raw binary data as a string.

## HTTP Response Streaming

Serve a converted image directly from a Laravel route:

```php
use Laratusk\Larasvg\Facades\SvgConverter;

Route::get('/logo.png', function () {
    $binary = SvgConverter::open(storage_path('app/logo.svg'))
        ->setDimensions(512, 512)
        ->toStdout('png');

    return response($binary)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=86400');
});
```

## Dynamic Conversion Endpoint

```php
Route::get('/convert/{format}', function (string $format) {
    $binary = SvgConverter::using('inkscape')
        ->open(storage_path('app/design.svg'))
        ->toStdout($format);

    $contentTypes = [
        'png' => 'image/png',
        'pdf' => 'application/pdf',
    ];

    return response($binary)
        ->header('Content-Type', $contentTypes[$format] ?? 'application/octet-stream');
});
```

## How It Works

- **Resvg** uses the `-c` flag to output to stdout
- **Inkscape** uses `--export-filename=-` to output to stdout

Both approaches are handled automatically — just call `toStdout()`.
