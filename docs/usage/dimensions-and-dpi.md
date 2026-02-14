# Dimensions & DPI

## Setting Dimensions

### All at Once

```php
$converter->setDimensions(1024, 768, 150); // width, height, dpi
```

### Individually

```php
$converter->setWidth(800);
$converter->setHeight(600);
$converter->setDpi(300);
```

## Chaining

All methods return the converter instance, so you can chain them:

```php
SvgConverter::open('logo.svg')
    ->setWidth(1024)
    ->setHeight(1024)
    ->setDpi(96)
    ->setFormat('png')
    ->toFile('logo.png');
```

## DPI

DPI (dots per inch) controls the resolution of raster output. The default depends on the provider:

- **Resvg** — 96 DPI (SVG standard)
- **Inkscape** — 96 DPI

For print output, use 300 DPI:

```php
$converter->setDpi(300);
```

## Notes

- Width and height are in pixels
- If only width is set, the aspect ratio is preserved (provider-dependent)
- DPI is optional — pass `null` to skip setting it:

```php
$converter->setDimensions(1024, 768, null); // no DPI override
```
