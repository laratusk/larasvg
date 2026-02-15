# Configuration

After publishing the config file, you'll find it at `config/svg-converter.php`.

## Full Configuration

```php
// config/svg-converter.php
return [
    'default' => env('SVG_CONVERTER_DRIVER', 'resvg'),

    'drivers' => [
        'resvg'        => \Laratusk\Larasvg\Converters\ResvgConverter::class,
        'inkscape'     => \Laratusk\Larasvg\Converters\InkscapeConverter::class,
        'rsvg-convert' => \Laratusk\Larasvg\Converters\RsvgConvertConverter::class,
        'cairosvg'     => \Laratusk\Larasvg\Converters\CairosvgConverter::class,
    ],

    'providers' => [
        'resvg' => [
            'binary'  => env('RESVG_PATH', 'resvg'),
            'timeout' => env('RESVG_TIMEOUT', 60),
        ],
        'inkscape' => [
            'binary'  => env('INKSCAPE_PATH', 'inkscape'),
            'timeout' => env('INKSCAPE_TIMEOUT', 60),
        ],
        'rsvg-convert' => [
            'binary'  => env('RSVG_CONVERT_PATH', 'rsvg-convert'),
            'timeout' => env('RSVG_CONVERT_TIMEOUT', 60),
        ],
        'cairosvg' => [
            'binary'  => env('CAIROSVG_PATH', 'cairosvg'),
            'timeout' => env('CAIROSVG_TIMEOUT', 60),
        ],
    ],

    'default_disk' => env('SVG_CONVERTER_DISK', 'local'),
];
```

## The `drivers` Map

The `drivers` array maps provider names to their converter class. This is the entry point for custom drivers — adding an entry here is all that is needed to register a new driver. No package code changes are required.

```php
'drivers' => [
    // Add your own driver:
    'my-converter' => \App\Svg\MyCustomConverter::class,
],
```

See [Custom Drivers](/advanced/custom-drivers) for a full guide.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `SVG_CONVERTER_DRIVER` | `resvg` | Default conversion provider |
| `RESVG_PATH` | `resvg` | Path to the Resvg binary |
| `RESVG_TIMEOUT` | `60` | Resvg process timeout (seconds) |
| `INKSCAPE_PATH` | `inkscape` | Path to the Inkscape binary |
| `INKSCAPE_TIMEOUT` | `60` | Inkscape process timeout (seconds) |
| `RSVG_CONVERT_PATH` | `rsvg-convert` | Path to the rsvg-convert binary |
| `RSVG_CONVERT_TIMEOUT` | `60` | rsvg-convert process timeout (seconds) |
| `CAIROSVG_PATH` | `cairosvg` | Path to the CairoSVG binary |
| `CAIROSVG_TIMEOUT` | `60` | CairoSVG process timeout (seconds) |
| `SVG_CONVERTER_DISK` | `local` | Default Laravel filesystem disk |

## Examples

### Use Inkscape as default

```ini
SVG_CONVERTER_DRIVER=inkscape
```

### Use rsvg-convert as default

```ini
SVG_CONVERTER_DRIVER=rsvg-convert
```

### Use CairoSVG as default

```ini
SVG_CONVERTER_DRIVER=cairosvg
```

### Custom binary paths

```ini
RESVG_PATH=/usr/local/bin/resvg
INKSCAPE_PATH=/opt/homebrew/bin/inkscape
RSVG_CONVERT_PATH=/usr/bin/rsvg-convert
CAIROSVG_PATH=/Users/azer/.local/bin/cairosvg
```

### Increase timeout for large files

```ini
RESVG_TIMEOUT=120
INKSCAPE_TIMEOUT=300
RSVG_CONVERT_TIMEOUT=120
CAIROSVG_TIMEOUT=120
```

### Use S3 as default disk

```ini
SVG_CONVERTER_DISK=s3
```
