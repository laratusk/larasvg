# CairoSVG Provider

[CairoSVG](https://cairosvg.org/) is a Python-based SVG converter that uses the Cairo 2D graphics library. It supports PNG, PDF, PS, and SVG output and is installable via `pip` or `pipx`.

## Supported Formats

| Format | Supported |
|--------|-----------|
| PNG    | Yes       |
| PDF    | Yes       |
| PS     | Yes       |
| SVG    | Yes       |
| EPS    | —         |
| EMF    | —         |
| WMF    | —         |

## Installation

::: code-group

```bash [macOS]
brew install cairo libffi
pipx install cairosvg
```

```bash [Ubuntu/Debian]
sudo apt install libcairo2-dev pkg-config python3-dev
pip3 install cairosvg
```

```bash [Fedora/RHEL]
sudo dnf install cairo-devel pkg-config python3-devel
pip3 install cairosvg
```

```bash [Arch]
sudo pacman -S python-cairosvg
```

```bash [Alpine]
apk add cairo-dev
pip3 install cairosvg
```

:::

Or use the interactive artisan command:

```bash
php artisan larasvg:setup
```

## Configuration

```php
// config/svg-converter.php
'cairosvg' => [
    'binary'  => env('CAIROSVG_PATH', 'cairosvg'),
    'timeout' => env('CAIROSVG_TIMEOUT', 60),
],
```

```ini
# .env
SVG_CONVERTER_DRIVER=cairosvg
CAIROSVG_PATH=/usr/local/bin/cairosvg
```

## Basic Usage

```php
use Laratusk\Larasvg\Facades\SvgConverter;

SvgConverter::using('cairosvg')
    ->open(resource_path('svg/logo.svg'))
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->toFile(storage_path('app/logo.png'));
```

## Output Dimensions

CairoSVG distinguishes between the **output dimensions** (the final pixel size of the rendered image) and the **container dimensions** (the virtual viewport used when the SVG uses percentage-based widths/heights).

### Output Dimensions

Set the final pixel dimensions of the rendered output:

```php
// Via standard API
$converter->setWidth(800)->setHeight(600);

// Via CairoSVG-specific aliases
$converter->setOutputWidth(800)->setOutputHeight(600);

// Shorthand
$converter->setDimensions(800, 600);
```

Maps to `--output-width` and `--output-height` flags.

### Container Dimensions

For SVGs that use percentage widths/heights, set the parent container size so percentages resolve correctly:

```php
$converter->setContainerWidth(1920)->setContainerHeight(1080);

// Or as one call
$converter->setContainerDimensions(1920, 1080);
```

Maps to the `-W` and `-H` flags.

## DPI

Set the pixel density ratio (default: 96):

```php
$converter->setDpi(300);
```

Maps to the `-d` flag.

## Scale

Apply a uniform scaling factor to the output:

```php
// 2× scale
$converter->setScale(2.0);

// 0.5× scale (half size)
$converter->setScale(0.5);
```

Maps to the `-s` flag.

## Unsafe Mode

By default, CairoSVG restricts XML entity resolution to protect against XXE attacks. Pass `unsafe()` to disable this restriction for trusted input:

```php
$converter->unsafe();
```

::: warning
Only use `unsafe()` on SVG files from trusted sources. It enables XML entity resolution, which can expose your application to XXE attacks if used on untrusted input.
:::

## Background Color

::: info Not supported via CLI
CairoSVG does not expose a background color option through its CLI. Calling `setBackground()` or `setBackgroundOpacity()` will throw a `SvgConverterException`.

If you need background color control, use the Python API directly, or apply a background rectangle inside the SVG itself.
:::

## Full Example

```php
SvgConverter::using('cairosvg')
    ->open(resource_path('svg/report.svg'))
    ->setFormat('pdf')
    ->setScale(2.0)
    ->setDpi(150)
    ->setContainerDimensions(1920, 1080)
    ->toFile(storage_path('app/report.pdf'));
```
