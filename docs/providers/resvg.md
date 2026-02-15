# Resvg Provider

[Resvg](https://github.com/linebender/resvg) is a fast, lightweight SVG to PNG converter. It is the default provider in LaraSVG.

## Supported Formats

| Format | Supported |
|--------|-----------|
| PNG    | Yes       |

## Basic Usage

```php
use Laratusk\Larasvg\Facades\SvgConverter;

SvgConverter::open(resource_path('svg/logo.svg'))
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->toFile(storage_path('app/logo.png'));
```

## Resvg-Specific Methods

### `setZoom()`

Set the zoom factor:

```php
$converter->setZoom(2.0);
```

### `setShapeRendering()`

Control shape rendering mode:

```php
$converter->setShapeRendering('crispEdges');
```

Options: `optimizeSpeed`, `crispEdges`, `geometricPrecision`

### `setTextRendering()`

Control text rendering:

```php
$converter->setTextRendering('optimizeLegibility');
```

### `setImageRendering()`

Control image rendering:

```php
$converter->setImageRendering('optimizeQuality');
```

### `setDefaultFontFamily()`

Set the default font family:

```php
$converter->setDefaultFontFamily('Arial');
```

### `setDefaultFontSize()`

Set the default font size in pixels:

```php
$converter->setDefaultFontSize(16);
```

### `useFontFile()`

Use a specific font file:

```php
$converter->useFontFile(resource_path('fonts/font.ttf'));
```

### `useFontsDir()`

Load fonts from a directory:

```php
$converter->useFontsDir(resource_path('fonts'));
```

### `skipSystemFonts()`

Skip loading system fonts (faster startup):

```php
$converter->skipSystemFonts();
```

### `setResourcesDir()`

Set the directory for resolving relative image paths in SVG files:

```php
$converter->setResourcesDir(resource_path('svg'));
```

## Full Example

```php
SvgConverter::open(resource_path('svg/design.svg'))
    ->setFormat('png')
    ->setDimensions(2048, 2048, 300)
    ->setBackground('#ffffff')
    ->setZoom(2.0)
    ->setShapeRendering('geometricPrecision')
    ->setDefaultFontFamily('Inter')
    ->useFontsDir(resource_path('fonts'))
    ->skipSystemFonts()
    ->toFile(storage_path('app/design-hq.png'));
```
