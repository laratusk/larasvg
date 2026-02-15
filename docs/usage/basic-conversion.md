# Basic Conversion

## Opening Files

### From a Local Path

The `open()` method accepts an **absolute file path**. Use Laravel's path helpers to build the correct path:

```php
use Laratusk\Larasvg\Facades\SvgConverter;

$converter = SvgConverter::open(resource_path('svg/file.svg'));

// Other Laravel path helpers you can use:
// storage_path('app/file.svg')
// base_path('resources/svg/file.svg')
// public_path('images/file.svg')
```

### From a Laravel Disk

```php
$converter = SvgConverter::openFromDisk('s3', 'designs/logo.svg');
```

### From Raw SVG Content

```php
$svgString = '<svg xmlns="http://www.w3.org/2000/svg">...</svg>';
$converter = SvgConverter::openFromContent($svgString);
```

## Setting the Format

```php
$converter->setFormat('png');
```

Supported formats depend on the provider:

| Format | Resvg | Inkscape |
|--------|-------|----------|
| PNG    | Yes   | Yes      |
| PDF    | —     | Yes      |
| SVG    | —     | Yes      |
| PS     | —     | Yes      |
| EPS    | —     | Yes      |
| EMF    | —     | Yes      |
| WMF    | —     | Yes      |

If you call `setFormat()` with an unsupported format, a `SvgConverterException` is thrown.

## Converting

```php
// Auto-generate output path (saves next to input file)
$path = SvgConverter::open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->convert();
// => resources/svg/file.png

// Specify output filename
$path = SvgConverter::open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->convert('output.png');
// => resources/svg/output.png
```

## Switching Providers

```php
// Use Inkscape for this call
SvgConverter::using('inkscape')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->convert();
```

The `using()` method only applies to the next operation — subsequent calls revert to the default provider.
