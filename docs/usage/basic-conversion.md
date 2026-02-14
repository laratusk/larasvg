# Basic Conversion

## Opening Files

### From a Local Path

```php
use Laratusk\Larasvg\Facades\SvgConverter;

$converter = SvgConverter::open('/path/to/file.svg');
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
$path = SvgConverter::open('/path/to/file.svg')
    ->setFormat('png')
    ->convert();
// => /path/to/file.png

// Specify output filename
$path = SvgConverter::open('/path/to/file.svg')
    ->setFormat('png')
    ->convert('output.png');
// => /path/to/output.png
```

## Switching Providers

```php
// Use Inkscape for this call
SvgConverter::using('inkscape')
    ->open('/path/to/file.svg')
    ->setFormat('pdf')
    ->convert();
```

The `using()` method only applies to the next operation — subsequent calls revert to the default provider.
