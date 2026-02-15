# Rsvg-convert Provider

[rsvg-convert](https://wiki.gnome.org/Projects/LibRsvg) is a lightweight command-line tool from the [librsvg](https://gitlab.gnome.org/GNOME/librsvg) project. It is widely available on Linux distributions as a standard package and supports PNG, PDF, PS, EPS, and SVG output.

## Supported Formats

| Format | Supported |
|--------|-----------|
| PNG    | Yes       |
| PDF    | Yes       |
| PS     | Yes       |
| EPS    | Yes       |
| SVG    | Yes       |

## Basic Usage

```php
use Laratusk\Larasvg\Facades\SvgConverter;

SvgConverter::using('rsvg-convert')
    ->open(resource_path('svg/logo.svg'))
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->toFile(storage_path('app/logo.png'));
```

## Zoom

Scale the output with a zoom factor instead of explicit dimensions:

```php
// 2× zoom
$converter->setZoom(2.0);

// Independent horizontal and vertical zoom
$converter->setXZoom(2.0)->setYZoom(1.5);
```

## Aspect Ratio

Preserve the aspect ratio when both width and height are set:

```php
$converter->setWidth(800)->setHeight(600)->keepAspectRatio();
```

## Background Color

`rsvg-convert` accepts HEX, RGB, and RGBA colors directly:

```php
// Solid background
$converter->setBackground('#ffffff');
$converter->setBackground('rgb(255, 255, 255)');

// Transparent background via RGBA
$converter->setBackground('rgba(255, 255, 255, 0.5)');
```

::: info Background Opacity
`rsvg-convert` has no separate `--background-opacity` flag. Calling `setBackgroundOpacity()` combines the opacity with the color automatically, converting it to an `rgba()` value:

```php
$converter->setBackground('#ffffff')->setBackgroundOpacity(0.5);
// Sends: --background-color='rgba(255,255,255,0.5)'
```
:::

## Page Dimensions (PDF / PS)

Set physical page size for vector output:

```php
$converter
    ->setFormat('pdf')
    ->setPageWidth('210mm')   // A4 width
    ->setPageHeight('297mm')  // A4 height
    ->keepAspectRatio()
    ->toFile(storage_path('app/output.pdf'));
```

CSS length units are accepted: `px`, `pt`, `in`, `cm`, `mm`.

## Page Margins

Offset the rendered content within the page:

```php
$converter->setTopMargin('10mm')->setLeftMargin('15mm');
```

## Stylesheet

Apply an external CSS stylesheet to the SVG before rendering:

```php
$converter->setStylesheet(resource_path('css/svg-theme.css'));
```

## Unlimited Mode

Disable the SVG parser guard limits for large or complex SVGs:

```php
$converter->unlimited();
```

## Image Data (PDF / PS)

Control whether embedded images in the SVG are kept compressed or expanded:

```php
// Keep compressed (default for PDF/PS)
$converter->keepImageData(true);

// Embed as uncompressed RGB
$converter->keepImageData(false);
```

## Base URI

Set the base URI for resolving relative resource references inside the SVG:

```php
$converter->setBaseUri('file://'.resource_path('svg/'));
```

## Full Example

```php
SvgConverter::using('rsvg-convert')
    ->open(resource_path('svg/report.svg'))
    ->setFormat('pdf')
    ->setPageWidth('210mm')
    ->setPageHeight('297mm')
    ->keepAspectRatio()
    ->setBackground('#ffffff')
    ->setStylesheet(resource_path('css/print.css'))
    ->setBaseUri('file://'.resource_path('svg/'))
    ->toFile(storage_path('app/report.pdf'));
```
