# Testing

LaraSVG is built on Laravel's Process facade, making it fully testable with `Process::fake()`.

## Faking Process Calls

```php
use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Facades\SvgConverter;

Process::fake();

SvgConverter::open($svgPath)
    ->setFormat('png')
    ->convert();

Process::assertRan(function ($process) {
    return str_contains($process->command, 'resvg');
});
```

## Asserting Command Arguments

```php
Process::fake();

SvgConverter::open($svgPath)
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->convert();

Process::assertRan(function ($process) {
    return str_contains($process->command, '--width 512')
        && str_contains($process->command, '--height 512');
});
```

## Faking Disk Operations

When testing disk read/write, combine `Process::fake()` with `Storage::fake()`:

```php
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

Process::fake();
Storage::fake('s3');

// Upload a test SVG
Storage::disk('s3')->put('input.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

$converter = SvgConverter::openFromDisk('s3', 'input.svg');
$converter->toDisk('s3', 'output.png');

Storage::disk('s3')->assertExists('output.png');
```

## Faking Specific Results

```php
Process::fake([
    'resvg*' => Process::result(
        output: '',
        errorOutput: '',
        exitCode: 0,
    ),
]);
```

## Faking Failures

```php
Process::fake([
    'resvg*' => Process::result(
        output: '',
        errorOutput: 'resvg: error: file not found',
        exitCode: 1,
    ),
]);
```

## Running the Test Suite

```bash
# All tests
vendor/bin/phpunit

# Unit tests only
vendor/bin/phpunit --testsuite Unit

# Feature tests only
vendor/bin/phpunit --testsuite Feature

# Integration tests (requires resvg/inkscape installed)
vendor/bin/phpunit --testsuite Integration
```
