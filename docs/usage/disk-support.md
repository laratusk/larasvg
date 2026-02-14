# Disk Support

LaraSVG integrates with Laravel's filesystem to read from and write to any configured disk.

## Reading from a Disk

```php
use Laratusk\Larasvg\Facades\SvgConverter;

// Open from S3
$converter = SvgConverter::openFromDisk('s3', 'designs/logo.svg');

// Open from local disk
$converter = SvgConverter::openFromDisk('local', 'svgs/icon.svg');
```

The file is downloaded to a temporary location and automatically cleaned up after conversion.

## Writing to a Disk

```php
// Convert and save to S3
$path = SvgConverter::open('/local/path/logo.svg')
    ->toDisk('s3', 'exports/logo.png');

// With explicit format
$path = SvgConverter::open('/local/path/logo.svg')
    ->toDisk('s3', 'exports/logo', 'png');
```

## Full S3 Pipeline Example

Read from S3, convert, and write back to S3:

```php
$converter = SvgConverter::openFromDisk('s3', 'uploads/design.svg');

$converter
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->toDisk('s3', 'thumbnails/design.png');
```

## Default Disk

The default disk is configured in `config/svg-converter.php`:

```php
'default_disk' => env('SVG_CONVERTER_DISK', 'local'),
```

You can retrieve it programmatically:

```php
$disk = SvgConverter::getDefaultDisk(); // 'local'
```
