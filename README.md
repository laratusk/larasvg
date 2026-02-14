# LaraSVG

A modern Laravel package for SVG conversion with multiple provider support. Convert SVG files to PNG, PDF, EPS, and more using a fluent API and Laravel's Process facade.

## Features

- **Multi-provider architecture** — Switch between Resvg and Inkscape with a single method call
- **Resvg (default)** — Lightning-fast SVG to PNG conversion
- **Inkscape** — Full-featured SVG conversion to PNG, PDF, PS, EPS, EMF, WMF
- **Fluent API** — Chainable methods for dimensions, background, format, and provider-specific options
- **Laravel Filesystem** — Read from and write to any Laravel disk (S3, local, etc.)
- **Stdout output** — Pipe conversion output directly to stdout for streaming
- **Testable** — Built on Laravel's Process facade with full `Process::fake()` support
- **Facade** — Clean `SvgConverter::` static API with IDE autocompletion
- **Setup command** — Interactive `php artisan larasvg:setup` to install providers

## Requirements

- PHP 8.4+
- Laravel 12+
- At least one converter installed:
  - [Resvg](https://github.com/niclasvaneyk/resvg-cli) (recommended for PNG)
  - [Inkscape](https://inkscape.org/) 1.0+ (for PDF, EPS, PS, and other formats)

## Supported Formats

| Format | Resvg | Inkscape |
|--------|-------|----------|
| PNG    | Yes   | Yes      |
| PDF    | —     | Yes      |
| SVG    | —     | Yes      |
| PS     | —     | Yes      |
| EPS    | —     | Yes      |
| EMF    | —     | Yes      |
| WMF    | —     | Yes      |

## Installation

```bash
composer require laratusk/larasvg
```

Publish the config file:

```bash
php artisan vendor:publish --tag=larasvg-config
```

### Setup Providers

Run the interactive setup command to detect and install conversion providers:

```bash
php artisan larasvg:setup
```

The command will:
1. Detect your operating system (macOS, Ubuntu, Fedora, Arch, Alpine, etc.)
2. Check which providers are already installed and show their versions
3. Prompt you to select a provider to install
4. Install the selected provider using the appropriate package manager
5. Suggest the `.env` configuration to use

```
  LaraSVG — Provider Setup

  System: macos (macos)

  ● Inkscape ··· Inkscape 1.4.3 (0d15f75, 2025-12-25) /opt/homebrew/bin/inkscape
  ○ Resvg ····· not installed

  Which provider would you like to install?
  › Inkscape — already installed (Inkscape 1.4.3)        (disabled)
    Resvg — PNG — fast, lightweight
    Skip — I'll install manually later
```

Already-installed providers are shown but cannot be selected. You can also install providers manually using the bundled shell script:

```bash
# Show status
./vendor/laratusk/larasvg/bin/install.sh

# Install a specific provider
./vendor/laratusk/larasvg/bin/install.sh resvg
./vendor/laratusk/larasvg/bin/install.sh inkscape
```

## Configuration

```php
// config/svg-converter.php
return [
    'default' => env('SVG_CONVERTER_DRIVER', 'resvg'),
    'providers' => [
        'resvg' => [
            'binary' => env('RESVG_PATH', 'resvg'),
            'timeout' => env('RESVG_TIMEOUT', 60),
        ],
        'inkscape' => [
            'binary' => env('INKSCAPE_PATH', 'inkscape'),
            'timeout' => env('INKSCAPE_TIMEOUT', 60),
        ],
    ],
    'default_disk' => env('SVG_CONVERTER_DISK', 'local'),
];
```

## Quick Start

### Basic Conversion (Resvg — default)

```php
use Laratusk\SvgConverter\Facades\SvgConverter;

// SVG to PNG
SvgConverter::open('/path/to/file.svg')
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->toFile('/path/to/output.png');
```

### Using Inkscape

```php
// Switch provider for this call
SvgConverter::using('inkscape')->open('/path/to/file.svg')
    ->setFormat('pdf')
    ->toFile('/path/to/output.pdf');
```

## Usage

### Dimensions & DPI

```php
$converter->setDimensions(1024, 768, 150);  // width, height, dpi
$converter->setWidth(800);
$converter->setHeight(600);
$converter->setDpi(300);
```

### Background Color & Opacity

```php
$converter->setBackground('#ffffff');       // HEX color
$converter->setBackground('rgb(255,0,0)'); // RGB color
$converter->setBackgroundOpacity(0.5);     // 0.0 to 1.0
```

### Output Methods

```php
// Auto-generate output filename
$path = $converter->setFormat('png')->convert();

// Explicit filename
$path = $converter->convert('output.png');

// Save to specific path
$path = $converter->toFile('/absolute/path/output.png');

// Save to Laravel filesystem disk
$path = $converter->toDisk('s3', 'exports/image.png');

// Output to stdout (binary data)
$binary = $converter->toStdout('png');

// Raw ProcessResult (no exception on failure)
$result = $converter->raw();
```

### Reading from Disks

```php
// Open from S3, local, or any configured disk
$converter = SvgConverter::openFromDisk('s3', 'designs/logo.svg');

// Open from raw SVG content
$converter = SvgConverter::openFromContent($svgString);
```

### Dynamic Options

```php
$converter->withOption('custom-flag', 'value');
$converter->withFlag('some-flag');
$converter->withOptions([
    'option-a' => 'value',
    'flag-b',
]);
$converter->timeout(120);
```

### Version Info

```php
$version = SvgConverter::version();            // Default provider version
$version = SvgConverter::version('inkscape');   // Specific provider version
```

## Provider-Specific Options

### Resvg Options

```php
$converter = SvgConverter::open('file.svg');

$converter->setZoom(2.0);                           // Zoom factor
$converter->setShapeRendering('crispEdges');         // optimizeSpeed, crispEdges, geometricPrecision
$converter->setTextRendering('optimizeLegibility');  // Text rendering hint
$converter->setImageRendering('optimizeQuality');    // Image rendering hint
$converter->setDefaultFontFamily('Arial');           // Default font
$converter->setDefaultFontSize(16);                  // Default font size
$converter->useFontFile('/path/to/font.ttf');        // Use specific font file
$converter->useFontsDir('/path/to/fonts');           // Fonts directory
$converter->skipSystemFonts();                       // Skip system fonts
$converter->setResourcesDir('/path/to/resources');   // Resources directory
```

### Inkscape Options

```php
$converter = SvgConverter::using('inkscape')->open('file.svg');

// Export area
$converter->exportAreaPage();
$converter->exportAreaDrawing();
$converter->exportArea(0, 0, 100, 100);
$converter->exportAreaSnap();

// Export modifiers
$converter->exportTextToPath();
$converter->exportPlainSvg();
$converter->exportOverwrite();
$converter->exportMargin(10);
$converter->exportLatex();
$converter->exportIgnoreFilters();
$converter->vacuumDefs();

// Format-specific
$converter->exportPdfVersion('1.5');
$converter->exportPsLevel(2);
$converter->exportPngColorMode('RGBA_8');
$converter->exportPngCompression(9);
$converter->exportPngAntialias(3);

// Pages & objects
$converter->setPage(2);
$converter->firstPage();
$converter->exportId('objectId', idOnly: true);

// Query dimensions
$dimensions = $converter->query();
// Returns: ['x' => '0', 'y' => '0', 'width' => '200', 'height' => '200']
```

## Error Handling

All conversion errors throw `SvgConverterException`:

```php
use Laratusk\SvgConverter\Exceptions\SvgConverterException;

try {
    SvgConverter::open('file.svg')->setFormat('png')->convert();
} catch (SvgConverterException $e) {
    $e->getMessage();     // Error message (includes provider name)
    $e->exitCode;         // Process exit code
    $e->output;           // Stdout content
    $e->errorOutput;      // Stderr content
    $e->getSummary();     // Formatted debug summary
}
```

## Testing

The package integrates with Laravel's `Process::fake()`:

```php
use Illuminate\Support\Facades\Process;

Process::fake();

SvgConverter::open($svgPath)->setFormat('png')->convert();

Process::assertRan(function ($process) {
    return str_contains($process->command, '--width 512');
});
```

### Running the Test Suite

```bash
# All tests (unit + feature, skips integration if binaries not installed)
vendor/bin/phpunit

# Unit tests only
vendor/bin/phpunit --testsuite Unit

# Feature tests only
vendor/bin/phpunit --testsuite Feature

# Integration tests (requires resvg/inkscape installed)
vendor/bin/phpunit --testsuite Integration
```

## API Reference

### Common Methods (All Providers)

| Method | Description |
|--------|-------------|
| `setFormat(string)` | Set export format |
| `setWidth(int)` | Set export width |
| `setHeight(int)` | Set export height |
| `setDpi(?int)` | Set export DPI |
| `setDimensions(int, int, ?int)` | Set width, height, DPI |
| `setBackground(string)` | Set background color (HEX/RGB) |
| `setBackgroundOpacity(float)` | Set background opacity (0.0-1.0) |
| `convert(?string)` | Convert and return output path |
| `toFile(string)` | Convert to local file |
| `toDisk(string, string, ?string)` | Convert to Laravel disk |
| `toStdout(?string)` | Convert to stdout |
| `raw()` | Get raw ProcessResult |
| `buildCommand()` | Get the CLI command string |
| `timeout(int)` | Set process timeout |
| `withOption(string, mixed)` | Add CLI option |
| `withFlag(string)` | Add CLI flag |
| `withOptions(array)` | Add multiple options |
| `cleanup()` | Clean temp files |

### Manager Methods

| Method | Description |
|--------|-------------|
| `open(string)` | Open local file |
| `openFromDisk(string, string)` | Open from Laravel disk |
| `openFromContent(string, string)` | Open from string content |
| `using(string)` | Switch provider |
| `version(?string)` | Get provider version |
| `getBinary(?string)` | Get binary path |
| `getTimeout(?string)` | Get timeout |
| `getDefaultDisk()` | Get default disk |

### Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan larasvg:setup` | Interactive provider installer |

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE](LICENSE) for details.
