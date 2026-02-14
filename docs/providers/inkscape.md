# Inkscape Provider

[Inkscape](https://inkscape.org/) is a full-featured vector graphics editor with powerful command-line export capabilities. It supports a wide range of output formats.

## Supported Formats

| Format | Supported |
|--------|-----------|
| PNG    | Yes       |
| PDF    | Yes       |
| SVG    | Yes       |
| PS     | Yes       |
| EPS    | Yes       |
| EMF    | Yes       |
| WMF    | Yes       |

## Basic Usage

```php
use Laratusk\Larasvg\Facades\SvgConverter;

SvgConverter::using('inkscape')
    ->open('design.svg')
    ->setFormat('pdf')
    ->toFile('design.pdf');
```

## Export Area

### Page Area

Export the full page:

```php
$converter->exportAreaPage();
```

### Drawing Area

Export only the bounding box of all objects:

```php
$converter->exportAreaDrawing();
```

### Custom Area

Set a custom export area with coordinates:

```php
$converter->exportArea(0, 0, 100, 100); // x0, y0, x1, y1
```

### Snap to Pixels

Snap the export area to integer pixel values:

```php
$converter->exportAreaSnap();
```

## Export Modifiers

### Text to Path

Convert text objects to paths on export:

```php
$converter->exportTextToPath();
```

### Plain SVG

Export as plain SVG without Inkscape namespaces:

```php
$converter->exportPlainSvg();
```

### Overwrite Input

Overwrite the input file with the exported result:

```php
$converter->exportOverwrite();
```

### Export Margin

Add a margin around the exported area:

```php
$converter->exportMargin(10);
```

### LaTeX Companion

Export a LaTeX companion file alongside the output:

```php
$converter->exportLatex();
```

### Ignore Filters

Ignore SVG filters and export as vectors:

```php
$converter->exportIgnoreFilters();
```

### Vacuum Defs

Remove unused `<defs>` from the SVG:

```php
$converter->vacuumDefs();
```

## Format-Specific Options

### PDF Version

```php
$converter->exportPdfVersion('1.5'); // Default: '1.4'
```

### PostScript Level

```php
$converter->exportPsLevel(2); // Default: 3
```

### PNG Color Mode

```php
$converter->exportPngColorMode('RGBA_8');
```

### PNG Compression

```php
$converter->exportPngCompression(9); // 0-9
```

### PNG Antialiasing

```php
$converter->exportPngAntialias(3); // 0-3
```

## Pages & Objects

### Export Specific Page

```php
$converter->setPage(2);
```

### Export First Page

```php
$converter->firstPage();
```

### Export Specific Object

```php
$converter->exportId('objectId');

// Export only the object (not the full page)
$converter->exportId('objectId', idOnly: true);
```

## Query Dimensions

Query the dimensions of the SVG or a specific object:

```php
$dimensions = $converter->query();
// Returns: ['x' => '0', 'y' => '0', 'width' => '200', 'height' => '200']

// Query a specific object
$dimensions = $converter->query('my-rect');
```

## Full Example

```php
SvgConverter::using('inkscape')
    ->open('design.svg')
    ->setFormat('pdf')
    ->setDimensions(1024, 768, 300)
    ->exportAreaDrawing()
    ->exportTextToPath()
    ->exportPdfVersion('1.5')
    ->exportMargin(10)
    ->toFile('design-print.pdf');
```
