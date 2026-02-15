# Introduction

LaraSVG is a modern Laravel package for SVG conversion with multiple provider support. Convert SVG files to PNG, PDF, EPS, and more using a fluent API and Laravel's Process facade.

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

## Code Quality

- **Test Coverage** — 98.62% line coverage with full feature and unit tests
- **PHPStan** — Compliant with Level 9 (Max) for robust type safety
- **Type Safety** — 100% typed properties and return types

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- At least one converter installed:
  - [Resvg](https://github.com/linebender/resvg) (recommended for PNG)
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
