# Custom Drivers

LaraSVG follows the **Open/Closed Principle** — you can add new conversion drivers without modifying any package source code. The manager resolves drivers from a config map, so registering a new driver is as simple as pointing a name to a class.

## How It Works

`SvgConverterManager::createConverter()` resolves the converter class in two steps:

1. **Programmatic registrations** — checked first (via `extend()`)
2. **Config map** — the `svg-converter.drivers` array in your config file

This means the manager itself never needs to change when new drivers are introduced.

## Option 1: Config Map (Recommended)

Add your driver to the `drivers` map in `config/svg-converter.php`:

```php
// config/svg-converter.php
return [
    'drivers' => [
        // Built-in drivers
        'resvg'       => \Laratusk\Larasvg\Converters\ResvgConverter::class,
        'inkscape'    => \Laratusk\Larasvg\Converters\InkscapeConverter::class,
        'rsvg-convert'=> \Laratusk\Larasvg\Converters\RsvgConvertConverter::class,
        'cairosvg'    => \Laratusk\Larasvg\Converters\CairosvgConverter::class,

        // Your custom driver
        'my-converter' => \App\Svg\MyCustomConverter::class,
    ],

    'providers' => [
        'my-converter' => [
            'binary'  => env('MY_CONVERTER_PATH', 'my-converter'),
            'timeout' => env('MY_CONVERTER_TIMEOUT', 60),
        ],
    ],
];
```

Then use it immediately:

```php
SvgConverter::using('my-converter')
    ->open(resource_path('svg/file.svg'))
    ->setFormat('png')
    ->toFile(storage_path('app/output.png'));
```

## Option 2: `extend()` in a Service Provider

Register drivers programmatically in `AppServiceProvider::boot()` (or any other service provider):

```php
use Laratusk\Larasvg\Facades\SvgConverter;

public function boot(): void
{
    SvgConverter::extend('my-converter', \App\Svg\MyCustomConverter::class);
}
```

Programmatic registrations **take precedence** over config-registered drivers with the same name, which makes them useful for testing or overriding built-in drivers.

## Building a Custom Driver

A custom driver must implement the `Laratusk\Larasvg\Contracts\Provider` interface. The easiest path is to extend `AbstractConverter`, which handles temp file management, disk support, error handling, and the fluent API for free.

### Minimal Example

```php
<?php

namespace App\Svg;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\AbstractConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

class MyCustomConverter extends AbstractConverter
{
    public const SUPPORTED_FORMATS = ['png'];

    public function supportedFormats(): array
    {
        return self::SUPPORTED_FORMATS;
    }

    public function version(): string
    {
        $result = Process::timeout(10)->run(escapeshellarg($this->binary).' --version');

        if ($result->failed()) {
            throw SvgConverterException::fromProcess($result, "{$this->binary} --version", $this->providerName());
        }

        return trim($result->output());
    }

    public function buildCommand(): string
    {
        $parts = [escapeshellarg($this->binary)];

        foreach ($this->options as $option => $value) {
            $parts[] = match (true) {
                $value === null    => "--{$option}",
                is_numeric($value) => "--{$option}={$value}",
                default            => "--{$option}=".escapeshellarg((string) $value),
            };
        }

        $parts[] = escapeshellarg($this->inputPath);

        return implode(' ', $parts);
    }

    protected function providerName(): string
    {
        return 'my-converter';
    }

    protected function applyExportOptions(string $exportPath): void
    {
        if ($this->format !== null) {
            $this->withOption('format', $this->format);
        }

        if ($exportPath !== '-') {
            $this->withOption('output', $exportPath);
        }
    }
}
```

### Implementing `HasActionList`

If your driver exposes an action list command (like Inkscape), implement the `HasActionList` contract so `SvgConverter::actionList()` can use it:

```php
use Laratusk\Larasvg\Contracts\HasActionList;

class MyCustomConverter extends AbstractConverter implements HasActionList
{
    public function actionList(): string
    {
        $result = Process::timeout(10)->run(
            escapeshellarg($this->binary).' --action-list'
        );

        return $result->output();
    }
}
```

## Testing Custom Drivers

Use `Process::fake()` and register your driver in the test:

```php
use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Facades\SvgConverter;

Process::fake([
    '*' => Process::result(output: '', exitCode: 0),
]);

SvgConverter::extend('my-converter', MyCustomConverter::class);

SvgConverter::using('my-converter')
    ->open($svgPath)
    ->setFormat('png')
    ->convert();

Process::assertRan(fn ($p) => str_contains($p->command, 'my-converter'));
```

## Distributing a Driver as a Package

If you are building a LaraSVG driver as a standalone Composer package:

1. Require `laratusk/larasvg` as a dependency
2. Extend `AbstractConverter` (or implement `Provider` directly)
3. Create a Laravel service provider that calls `SvgConverter::extend()`
4. Register the service provider in the package's `composer.json` under `extra.laravel.providers`

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\YourPackage\\YourDriverServiceProvider"
            ]
        }
    }
}
```

```php
// YourDriverServiceProvider.php
public function boot(): void
{
    SvgConverter::extend('your-driver', YourConverter::class);
}
```

Users of your package can then use it immediately after installing:

```php
SvgConverter::using('your-driver')->open($path)->setFormat('png')->convert();
```
