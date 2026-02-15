# Facade

`Laratusk\Larasvg\Facades\SvgConverter`

The facade provides static access to the `SvgConverterManager` instance.

## Methods

### `open(string $path): Provider`

Open a local SVG file for conversion. The path must be an absolute file path — use Laravel's path helpers such as `resource_path()`, `storage_path()`, `base_path()`, or `public_path()`.

```php
$converter = SvgConverter::open(resource_path('svg/file.svg'));
```

**Throws:** `SvgConverterException` if the file does not exist.

---

### `openFromDisk(string $disk, string $path): Provider`

Open a file from a Laravel filesystem disk. The file is downloaded to a temporary location.

```php
$converter = SvgConverter::openFromDisk('s3', 'designs/logo.svg');
```

**Throws:** `SvgConverterException` if the file does not exist on the disk.

---

### `openFromContent(string $content, string $extension = 'svg'): Provider`

Open from raw SVG content. The content is written to a temporary file.

```php
$converter = SvgConverter::openFromContent($svgString);
```

---

### `using(string $provider): SvgConverterManager`

Switch the provider for the next operation. Resets after the operation completes.

```php
$converter = SvgConverter::using('inkscape')->open(resource_path('svg/file.svg'));
```

---

### `version(?string $provider = null): string`

Get the version string of the default or specified provider.

```php
$version = SvgConverter::version();            // Default provider
$version = SvgConverter::version('inkscape');   // Specific provider
```

---

### `actionList(): string`

Get the list of available Inkscape actions. Only available when using the Inkscape provider.

```php
$actions = SvgConverter::using('inkscape')->actionList();
```

---

### `getBinary(?string $provider = null): string`

Get the configured binary path for the default or specified provider.

```php
$binary = SvgConverter::getBinary();            // Default provider
$binary = SvgConverter::getBinary('inkscape');   // Specific provider
```

---

### `getTimeout(?string $provider = null): int`

Get the configured timeout for the default or specified provider.

```php
$timeout = SvgConverter::getTimeout(); // 60
```

---

### `getDefaultDisk(): string`

Get the configured default filesystem disk.

```php
$disk = SvgConverter::getDefaultDisk(); // 'local'
```
