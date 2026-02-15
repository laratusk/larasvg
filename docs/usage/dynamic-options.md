# Dynamic Options

Beyond the built-in methods, you can pass arbitrary CLI options and flags to the underlying converter binary.

## `withOption()`

Add a CLI option with a value:

```php
$converter->withOption('custom-flag', 'value');
// Produces: --custom-flag value (Resvg) or --custom-flag='value' (Inkscape)
```

## `withFlag()`

Add a CLI flag (no value):

```php
$converter->withFlag('some-flag');
// Produces: --some-flag
```

## `withOptions()`

Add multiple options at once. Numeric keys are treated as flags:

```php
$converter->withOptions([
    'option-a' => 'value',   // --option-a value
    'flag-b',                 // --flag-b
]);
```

## `timeout()`

Override the process timeout for this conversion:

```php
$converter->timeout(120); // 120 seconds
```

The default timeout is configured per provider in `config/svg-converter.php`.

## Example: Custom Resvg Options

```php
SvgConverter::open(resource_path('svg/logo.svg'))
    ->setFormat('png')
    ->setDimensions(1024, 1024)
    ->withOption('languages', 'en')
    ->withOption('shape-rendering', 'crispEdges')
    ->timeout(30)
    ->toFile(storage_path('app/logo.png'));
```

## Example: Custom Inkscape Options

```php
SvgConverter::using('inkscape')
    ->open(resource_path('svg/design.svg'))
    ->setFormat('pdf')
    ->withOption('export-pdf-version', '1.5')
    ->withFlag('export-text-to-path')
    ->timeout(300)
    ->toFile(storage_path('app/design.pdf'));
```

::: tip
While `withOption()` and `withFlag()` work for any CLI option, prefer using the dedicated provider-specific methods when available (e.g., `setZoom()`, `exportTextToPath()`) for better IDE autocompletion and type safety.
:::
