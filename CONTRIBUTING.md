# Contributing to LaraSVG

Thank you for considering contributing to LaraSVG! This guide will help you get started.

## Development Setup

### Prerequisites

- PHP 8.4+
- Composer
- (Optional) [Resvg](https://github.com/niclasvaneyk/resvg-cli) — for running integration tests
- (Optional) [Inkscape](https://inkscape.org/) 1.0+ — for running integration tests

### Installation

```bash
git clone https://github.com/laratusk/larasvg.git
cd larasvg
composer install
```

## Quality Tools

This project uses four quality tools that must all pass before code can be merged. You can run them individually or all at once:

```bash
# Run the full quality pipeline (lint, rector, static analysis, tests)
composer quality
```

### Pint (Code Style)

We use [Laravel Pint](https://laravel.com/docs/pint) with the Laravel preset for consistent code style.

```bash
# Check for style issues
composer lint:test

# Auto-fix style issues
composer lint
```

### Rector (Automated Refactoring)

[Rector](https://getrector.com/) enforces modern PHP patterns: strict types, early returns, dead code removal, and PHP 8.4 features.

```bash
# Preview changes (dry run)
composer rector:test

# Apply changes
composer rector
```

### PHPStan (Static Analysis)

[Larastan](https://github.com/larastan/larastan) (PHPStan for Laravel) runs at **level 6**.

```bash
composer analyse
```

All source code in `src/` must pass without errors. If you add new code, make sure to include proper PHPDoc types for arrays and iterables.

### PHPUnit (Tests)

```bash
# Run all tests (unit + feature + integration)
composer test

# Run only unit tests
composer test:unit

# Run only feature tests
composer test:feature

# Run integration tests (requires resvg/inkscape binaries)
composer test:integration
```

## Test Structure

Tests are organized into three suites:

| Suite | Directory | Description |
|-------|-----------|-------------|
| **Unit** | `tests/Unit/` | Tests individual classes in isolation. No external processes. |
| **Feature** | `tests/Feature/` | Tests package features using `Process::fake()`. No binaries needed. |
| **Integration** | `tests/Integration/` | Runs real conversion commands. Requires resvg/inkscape installed. Automatically skipped if binaries are not found. |

### Writing Tests

- Extend `Laratusk\SvgConverter\Tests\TestCase` for all tests
- Use `Process::fake()` in unit and feature tests to avoid real process execution
- Use the `#[Test]` attribute instead of `test_` method prefixes
- Use `$this->createTempSvg()` helper from TestCase for temporary SVG files
- Clean up temp files in `tearDown()`

Example:

```php
<?php

namespace Laratusk\SvgConverter\Tests\Unit;

use Laratusk\SvgConverter\Tests\TestCase;
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

## Architecture Overview

```
src/
├── Contracts/
│   └── Provider.php              # Interface for all converters
├── Converters/
│   ├── AbstractConverter.php     # Shared converter logic
│   ├── InkscapeConverter.php     # Inkscape CLI implementation
│   └── ResvgConverter.php        # Resvg CLI implementation
├── Exceptions/
│   └── SvgConverterException.php # Custom exception with process details
├── Facades/
│   └── SvgConverter.php          # Laravel facade
├── Commands/
│   └── SetupCommand.php          # artisan larasvg:setup
├── SvgConverterManager.php       # Manager (provider selection, file opening)
└── SvgConverterServiceProvider.php
```

### Key Design Decisions

- **Manager pattern** — `SvgConverterManager` selects the provider and creates converter instances
- **Abstract base class** — `AbstractConverter` handles shared logic (formats, dimensions, background, temp files, execution)
- **Provider-specific subclasses** — Override CLI option names and add provider-specific methods
- **Laravel Process facade** — All CLI execution goes through `Process::run()` for testability with `Process::fake()`

### Adding a New Provider

1. Create a new converter class extending `AbstractConverter`
2. Implement `providerName()`, `supportedFormats()`, `version()`, `buildCommand()`, and `applyExportOptions()`
3. Override option name methods (`widthOption()`, etc.) if the CLI uses different flag names
4. Register the provider in `SvgConverterManager::createConverter()`
5. Add unit, feature, and integration tests

## Reporting Issues

When reporting bugs, please include:

- PHP version (`php -v`)
- Laravel version
- Provider and version (`resvg --version` or `inkscape --version`)
- Minimal reproduction steps
- Full error message and stack trace

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
