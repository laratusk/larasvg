# Output Methods

LaraSVG provides several ways to output the converted file.

## `convert()`

Auto-generates the output path based on the input filename, or use a custom name:

```php
// Auto-generate: input.svg → input.png (saved next to the input file)
$path = $converter->setFormat('png')->convert();

// Custom filename (relative to input directory)
$path = $converter->setFormat('png')->convert('output.png');
```

## `toFile()`

Save to a specific absolute path:

```php
$path = $converter->toFile(storage_path('app/output.png'));
```

The format is inferred from the file extension if not explicitly set.

## `toDisk()`

Save to any Laravel filesystem disk:

```php
// Save to S3
$path = $converter->toDisk('s3', 'exports/image.png');

// With explicit format
$path = $converter->toDisk('s3', 'exports/image', 'png');
```

See [Disk Support](/usage/disk-support) for more details.

## `toStdout()`

Get the raw binary output as a string:

```php
$binary = $converter->toStdout('png');
```

See [Stdout Streaming](/usage/stdout-streaming) for HTTP response examples.

## `raw()`

Get the raw `ProcessResult` without throwing on failure:

```php
$result = $converter->raw();

$result->successful();   // bool
$result->failed();       // bool
$result->output();       // stdout string
$result->errorOutput();  // stderr string
$result->exitCode();     // int
```

This is useful for debugging or when you want to handle errors manually.

## `buildCommand()`

Get the CLI command string without executing it:

```php
$command = $converter
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->buildCommand();

// e.g. 'resvg' --width 512 --height 512 '/path/to/file.svg' '/path/to/output.png'
```

This is useful for debugging or logging the exact command that would be run.
