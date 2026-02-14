# Provider Interface

`Laratusk\Larasvg\Contracts\Provider`

The `Provider` interface defines the contract that all converter implementations must follow.

## Methods

### Format & Dimensions

```php
public function supportedFormats(): array;
public function setFormat(string $format): static;
public function setWidth(int $width): static;
public function setHeight(int $height): static;
public function setDpi(?int $dpi): static;
public function setDimensions(int $width, int $height, ?int $dpi = null): static;
```

### Background

```php
public function setBackground(string $color): static;
public function setBackgroundOpacity(float $value): static;
```

### Execution

```php
public function convert(?string $exportName = null): string;
public function toFile(string $outputPath): string;
public function toDisk(string $disk, string $path, ?string $format = null): string;
public function toStdout(?string $format = 'png'): string;
public function raw(): ProcessResult;
public function buildCommand(): string;
```

### Options & Configuration

```php
public function timeout(int $seconds): static;
public function withOption(string $option, mixed $value): static;
public function withFlag(string $flag): static;
public function withOptions(array $options): static;
```

### Version

```php
public function version(): string;
```

### Cleanup

```php
public function cleanup(): void;
public function addTempFile(string $path): void;
public function createTempFile(string $name): string;
```

## Full Interface

```php
<?php

namespace Laratusk\Larasvg\Contracts;

use Illuminate\Contracts\Process\ProcessResult;

interface Provider
{
    public function supportedFormats(): array;
    public function version(): string;
    public function setFormat(string $format): static;
    public function setWidth(int $width): static;
    public function setHeight(int $height): static;
    public function setDpi(?int $dpi): static;
    public function setDimensions(int $width, int $height, ?int $dpi = null): static;
    public function setBackground(string $color): static;
    public function setBackgroundOpacity(float $value): static;
    public function convert(?string $exportName = null): string;
    public function toFile(string $outputPath): string;
    public function toDisk(string $disk, string $path, ?string $format = null): string;
    public function toStdout(?string $format = 'png'): string;
    public function raw(): ProcessResult;
    public function buildCommand(): string;
    public function timeout(int $seconds): static;
    public function withOption(string $option, mixed $value): static;
    public function withFlag(string $flag): static;
    public function withOptions(array $options): static;
    public function cleanup(): void;
    public function addTempFile(string $path): void;
    public function createTempFile(string $name): string;
}
```
