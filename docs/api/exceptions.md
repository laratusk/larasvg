# Exceptions

## SvgConverterException

`Laratusk\Larasvg\Exceptions\SvgConverterException`

Extends `RuntimeException`. Thrown by all LaraSVG operations on failure.

### Constructor

```php
public function __construct(
    string $message = '',
    public readonly string $output = '',
    public readonly string $errorOutput = '',
    public readonly int $exitCode = 1,
    ?Throwable $previous = null,
)
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$message` | `string` | Human-readable error message |
| `$output` | `string` | Stdout content from the failed process |
| `$errorOutput` | `string` | Stderr content from the failed process |
| `$exitCode` | `int` | Process exit code (default: `1`) |

### Static Methods

#### `fromProcess()`

Create an exception from a failed `ProcessResult`:

```php
public static function fromProcess(
    ProcessResult $result,
    string $command = '',
    string $provider = 'SVG converter'
): self
```

The message automatically includes the provider name and command for easier debugging.

### Instance Methods

#### `getSummary()`

Get a formatted debug summary:

```php
public function getSummary(): string
```

Returns a multi-line string with exit code, stderr, and stdout:

```
Exit code: 1
Stderr: resvg: error: file not found
Stdout: ...
```

### Usage

```php
use Laratusk\Larasvg\Exceptions\SvgConverterException;

try {
    $converter->convert();
} catch (SvgConverterException $e) {
    // Access properties
    echo $e->getMessage();
    echo $e->exitCode;
    echo $e->output;
    echo $e->errorOutput;

    // Debug summary
    echo $e->getSummary();

    // Standard exception methods
    echo $e->getCode();       // Same as exitCode
    echo $e->getPrevious();   // Previous exception, if any
}
```
