# Error Handling

All conversion errors throw `SvgConverterException`, a subclass of `RuntimeException`.

## Catching Errors

```php
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;

try {
    SvgConverter::open(resource_path('svg/file.svg'))
        ->setFormat('png')
        ->convert();
} catch (SvgConverterException $e) {
    $e->getMessage();     // Error message (includes provider name)
    $e->exitCode;         // Process exit code
    $e->output;           // Stdout content
    $e->errorOutput;      // Stderr content
}
```

## Exception Properties

| Property | Type | Description |
|----------|------|-------------|
| `message` | `string` | Human-readable error message |
| `exitCode` | `int` | Process exit code (default: `1`) |
| `output` | `string` | Stdout content from the process |
| `errorOutput` | `string` | Stderr content from the process |

## Debug Summary

The `getSummary()` method returns a formatted string for debugging:

```php
try {
    $converter->convert();
} catch (SvgConverterException $e) {
    logger()->error('SVG conversion failed', [
        'summary' => $e->getSummary(),
    ]);
}
```

Output:

```
Exit code: 1
Stderr: resvg: error: file not found
```

## Common Errors

### File Not Found

```php
// Throws: "Input file does not exist: /var/www/html/resources/svg/missing.svg"
SvgConverter::open(resource_path('svg/missing.svg'));
```

### Unsupported Format

```php
// Throws: "Unsupported export format: pdf. Supported by Resvg: png"
SvgConverter::open(resource_path('svg/file.svg'))->setFormat('pdf');
```

### Invalid Color

```php
// Throws: "Supported color formats are HEX (#ff007f) and RGB (rgb(255,0,128))."
$converter->setBackground('not-a-color');
```

### Invalid Opacity

```php
// Throws: "Background opacity must be between 0.0 and 1.0."
$converter->setBackgroundOpacity(1.5);
```

## Using `raw()` for Manual Error Handling

If you prefer not to use exceptions, use `raw()` to get the `ProcessResult` directly:

```php
$result = $converter->raw();

if ($result->failed()) {
    // Handle failure manually
    echo $result->errorOutput();
}
```
