# Introduction

LaraSVG is a modern Laravel package for SVG conversion with multiple provider support. Convert SVG files to PNG, PDF, EPS, and more using a fluent API and Laravel's Process facade.

## Features

- **Multi-provider architecture** — Switch between Resvg, Inkscape, rsvg-convert, and CairoSVG with a single method call
- **Resvg (default)** — Lightning-fast SVG to PNG conversion
- **Inkscape** — Full-featured SVG conversion to PNG, PDF, PS, EPS, EMF, WMF
- **rsvg-convert** — Lightweight librsvg tool for PNG, PDF, PS, EPS, and SVG output
- **CairoSVG** — Python-based converter using the Cairo 2D library; PNG, PDF, PS, SVG
- **Custom drivers** — Register your own converter by adding a class to the config — no package code changes needed
- **Fluent API** — Chainable methods for dimensions, background, format, and provider-specific options
- **Laravel Filesystem** — Read from and write to any Laravel disk (S3, local, etc.)
- **Stdout output** — Pipe conversion output directly to stdout for streaming
- **Testable** — Built on Laravel's Process facade with full `Process::fake()` support
- **Facade** — Clean `SvgConverter::` static API with IDE autocompletion
- **Setup command** — Interactive `php artisan larasvg:setup` to install providers

## Code Quality

- **Test Coverage** — 98%+ line coverage with full feature and unit tests
- **PHPStan** — Compliant with Level 9 (Max) for robust type safety
- **Type Safety** — 100% typed properties and return types

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- At least one converter installed:
  - [Resvg](https://github.com/linebender/resvg) — recommended for PNG
  - [Inkscape](https://inkscape.org/) 1.0+ — for PDF, EPS, PS, EMF, WMF
  - [rsvg-convert](https://wiki.gnome.org/Projects/LibRsvg) — lightweight alternative for PNG, PDF, PS, EPS, SVG
  - [CairoSVG](https://cairosvg.org/) — Python-based, for PNG, PDF, PS, SVG

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
