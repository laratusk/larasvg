# SvgConverterManager

`Laratusk\Larasvg\SvgConverterManager`

The manager class is responsible for provider selection, file opening, and creating converter instances.

## Constructor

```php
public function __construct(Application $app)
```

Receives the Laravel application container.

## Methods

### `open(string $path): Provider`

Open a local file for processing.

```php
$converter = $manager->open('/path/to/file.svg');
```

- Verifies the file exists
- Creates a converter instance using the current provider
- **Throws:** `SvgConverterException` if the file does not exist

---

### `openFromDisk(string $disk, string $path): Provider`

Open a file from a Laravel filesystem disk.

```php
$converter = $manager->openFromDisk('s3', 'designs/logo.svg');
```

- Downloads the file to a temporary location
- Temporary files are cleaned up automatically
- **Throws:** `SvgConverterException` if the file does not exist on the disk

---

### `openFromContent(string $content, string $extension = 'svg'): Provider`

Open from raw file content (string).

```php
$converter = $manager->openFromContent($svgString);
```

- Writes content to a temporary file
- Temporary files are cleaned up automatically

---

### `using(string $provider): static`

Switch provider for the next operation.

```php
$manager->using('inkscape')->open('file.svg');
```

The provider resets to the default after the next `open()` / `openFromDisk()` / `openFromContent()` call.

---

### `version(?string $provider = null): string`

Get the version string of the given or default provider.

```php
$version = $manager->version();
$version = $manager->version('inkscape');
```

---

### `actionList(): string`

Get the list of available Inkscape actions. Only works with the Inkscape provider.

- **Throws:** `SvgConverterException` if the current provider is not Inkscape

---

### `getBinary(?string $provider = null): string`

Get the configured binary path for the given or default provider.

```php
$binary = $manager->getBinary();           // 'resvg'
$binary = $manager->getBinary('inkscape'); // 'inkscape'
```

---

### `getTimeout(?string $provider = null): int`

Get the configured timeout in seconds for the given or default provider.

```php
$timeout = $manager->getTimeout(); // 60
```

---

### `getDefaultDisk(): string`

Get the configured default filesystem disk name.

```php
$disk = $manager->getDefaultDisk(); // 'local'
```
