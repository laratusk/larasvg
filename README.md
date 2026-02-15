<p align="center">
    <img src="art/banner.png" alt="LaraSVG Banner">
</p>

<p align="center">
    <a href="https://packagist.org/packages/laratusk/larasvg"><img src="https://img.shields.io/packagist/v/laratusk/larasvg.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/laratusk/larasvg"><img src="https://img.shields.io/packagist/php-v/laratusk/larasvg.svg?style=flat-square" alt="PHP Version"></a>
    <a href="https://packagist.org/packages/laratusk/larasvg"><img src="https://img.shields.io/badge/Laravel-10.x--12.x-red?style=flat-square" alt="Laravel Version"></a>
    <a href="https://github.com/laratusk/larasvg/actions"><img src="https://img.shields.io/github/actions/workflow/status/laratusk/larasvg/ci.yml?branch=main&style=flat-square&label=tests" alt="Tests"></a>
    <a href="https://packagist.org/packages/laratusk/larasvg"><img src="https://img.shields.io/packagist/l/laratusk/larasvg.svg?style=flat-square" alt="License"></a>
</p>

A modern Laravel package for SVG conversion with multiple provider support. Convert SVG files to PNG, PDF, EPS, and more using a fluent API and Laravel's Process facade.

## Battle-Tested

> Backed by over 80 million SVG-to-format conversions in production across three years, this codebase is truly battle-tested. Releasing it as a package was the logical evolution.

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- At least one converter installed:
  - [Resvg](https://github.com/linebender/resvg) — recommended for PNG
  - [Inkscape](https://inkscape.org/) 1.0+ — for PDF, EPS, PS, EMF, WMF
  - [rsvg-convert](https://wiki.gnome.org/Projects/LibRsvg) — lightweight, PNG/PDF/PS/EPS/SVG
  - [CairoSVG](https://cairosvg.org/) — Python-based, PNG/PDF/PS/SVG

## Quick Install

```bash
composer require laratusk/larasvg
php artisan larasvg:setup
```

## Documentation

Visit the **[full documentation](https://larasvg.laratusk.org)** for detailed guides, usage examples, API reference, and more.

## Features

- **Multi-provider architecture** — Switch between Resvg, Inkscape, rsvg-convert, and CairoSVG with a single method call
- **Resvg (default)** — Lightning-fast SVG to PNG conversion
- **Inkscape** — Full-featured SVG conversion to PNG, PDF, PS, EPS, EMF, WMF
- **rsvg-convert** — Lightweight librsvg tool for PNG, PDF, PS, EPS, and SVG output
- **CairoSVG** — Python-based converter using the Cairo 2D library; PNG, PDF, PS, SVG
- **Custom drivers** — Register your own converter via config or `SvgConverter::extend()` — no package code changes needed
- **Fluent API** — Chainable methods for dimensions, background, format, and provider-specific options
- **Laravel Filesystem** — Read from and write to any Laravel disk (S3, local, etc.)
- **Stdout output** — Pipe conversion output directly to stdout for streaming responses
- **Facade** — Clean `SvgConverter::` static API with IDE autocompletion
- **Testable** — Built on Laravel's Process facade with full `Process::fake()` support
- **Setup command** — Interactive `php artisan larasvg:setup` to detect and install providers

## Supported Formats

| Format | Resvg | Inkscape | rsvg-convert | CairoSVG |
|--------|-------|----------|--------------|----------|
| PNG    | Yes   | Yes      | Yes          | Yes      |
| PDF    | —     | Yes      | Yes          | Yes      |
| SVG    | —     | Yes      | Yes          | Yes      |
| PS     | —     | Yes      | Yes          | Yes      |
| EPS    | —     | Yes      | Yes          | —        |
| EMF    | —     | Yes      | —            | —        |
| WMF    | —     | Yes      | —            | —        |

## Quick Start

```php
use Laratusk\Larasvg\Facades\SvgConverter;

// Default provider (Resvg)
SvgConverter::open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->toFile(storage_path('app/output.png'));

// Switch provider per call
SvgConverter::using('inkscape')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->toFile(storage_path('app/output.pdf'));

SvgConverter::using('cairosvg')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('pdf')
    ->setScale(2.0)
    ->toFile(storage_path('app/output.pdf'));
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE](LICENSE) for details.
