# Contributing

Thank you for considering contributing to LaraSVG! This guide will help you get started.

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- (Optional) [Resvg](https://github.com/linebender/resvg) — for running integration tests
- (Optional) [Inkscape](https://inkscape.org/) 1.0+ — for running integration tests
- (Optional) [rsvg-convert](https://wiki.gnome.org/Projects/LibRsvg) — for running integration tests

### Installation

```bash
git clone https://github.com/laratusk/larasvg.git
cd larasvg
composer install
```

## Quality Tools

This project uses four quality tools that must all pass before code can be merged. Run them all at once:

```bash
composer quality
```

### Pint (Code Style)

[Laravel Pint](https://laravel.com/docs/pint) with the Laravel preset:

```bash
# Check for style issues
composer lint:test

# Auto-fix style issues
composer lint
```

### Rector (Automated Refactoring)

[Rector](https://getrector.com/) enforces modern PHP patterns:

```bash
# Preview changes (dry run)
composer rector:test

# Apply changes
composer rector
```

### PHPStan (Static Analysis)

[Larastan](https://github.com/larastan/larastan) runs at **level max**:

```bash
composer analyse
```

### PHPUnit (Tests)

```bash
# Run all tests
composer test

# Unit tests only
composer test:unit

# Feature tests only
composer test:feature

# Integration tests (requires binaries)
composer test:integration
```

## Test Structure

| Suite | Directory | Description |
|-------|-----------|-------------|
| **Unit** | `tests/Unit/` | Tests individual classes in isolation. No external processes. |
| **Feature** | `tests/Feature/` | Tests package features using `Process::fake()`. No binaries needed. |
| **Integration** | `tests/Integration/` | Runs real conversion commands. Requires resvg/inkscape installed. |

### Writing Tests

- Extend `Laratusk\Larasvg\Tests\TestCase` for all tests
- Use `Process::fake()` in unit and feature tests
- Use the `#[Test]` attribute instead of `test_` method prefixes
- Use `$this->createTempSvg()` helper from TestCase for temporary SVG files
- Clean up temp files in `tearDown()`

```php
<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MyNewTest extends TestCase
{
    #[Test]
    public function it_does_something(): void
    {
        // Arrange, Act, Assert
    }
}
```

## Architecture Overview

```
src/
├── Contracts/
│   └── Provider.php                  # Interface for all converters
├── Converters/
│   ├── AbstractConverter.php         # Shared converter logic
│   ├── InkscapeConverter.php         # Inkscape CLI implementation
│   ├── ResvgConverter.php            # Resvg CLI implementation
│   └── RsvgConvertConverter.php      # rsvg-convert CLI implementation
├── Exceptions/
│   └── SvgConverterException.php     # Custom exception with process details
├── Facades/
│   └── SvgConverter.php              # Laravel facade
├── Commands/
│   └── SetupCommand.php              # artisan larasvg:setup
├── SvgConverterManager.php           # Manager (provider selection, file opening)
└── SvgConverterServiceProvider.php
```

### Key Design Decisions

- **Manager pattern** — `SvgConverterManager` selects the provider and creates converter instances
- **Abstract base class** — `AbstractConverter` handles shared logic (formats, dimensions, background, temp files, execution)
- **Provider-specific subclasses** — Override CLI option names and add provider-specific methods
- **Laravel Process facade** — All CLI execution goes through `Process::run()` for testability

### Adding a New Provider

1. Create a new converter class extending `AbstractConverter`
2. Implement `providerName()`, `supportedFormats()`, `version()`, `buildCommand()`, and `applyExportOptions()`
3. Override option name methods (`widthOption()`, etc.) if the CLI uses different flag names
4. Register the provider in `SvgConverterManager::createConverter()`
5. Add unit, feature, and integration tests

## Manual Testing Script

The repo includes `try.php` — a local scratch script for quickly testing any provider against the real binary without running the full test suite:

```bash
php try.php rsvg-convert
php try.php resvg
php try.php inkscape
```

Running without arguments prints the list of available providers. The script prints the version, supported formats, and the output path and file size for each format the provider supports.

::: info
`try.php` is tracked in git for convenience but is **not bundled in Composer releases** (`archive.exclude` in `composer.json`). It is purely a development tool and has no impact on package consumers.
:::

## Pull Request Workflow

1. **Fork** the repository and create a feature branch from `main`
2. **Write code** following the existing patterns and conventions
3. **Run the quality pipeline** before committing:
   ```bash
   composer quality
   ```
4. **Commit** with a clear, descriptive message
5. **Push** your branch and open a pull request

### PR Checklist

- [ ] All quality checks pass (`composer quality`)
- [ ] New features include tests
- [ ] PHPDoc types are added for new public methods
- [ ] No breaking changes to the public API (or clearly documented if intentional)

## Reporting Issues

When reporting bugs, please include:

- PHP version (`php -v`)
- Laravel version
- Provider and version (`resvg --version` or `inkscape --version`)
- Minimal reproduction steps
- Full error message and stack trace

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](https://github.com/laratusk/larasvg/blob/main/LICENSE).
