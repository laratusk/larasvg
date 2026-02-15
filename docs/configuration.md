# Configuration

After publishing the config file, you'll find it at `config/svg-converter.php`.

## Full Configuration

```php
// config/svg-converter.php
return [
    'default' => env('SVG_CONVERTER_DRIVER', 'resvg'),

    'providers' => [
        'resvg' => [
            'binary' => env('RESVG_PATH', 'resvg'),
            'timeout' => env('RESVG_TIMEOUT', 60),
        ],
        'inkscape' => [
            'binary' => env('INKSCAPE_PATH', 'inkscape'),
            'timeout' => env('INKSCAPE_TIMEOUT', 60),
        ],
        'rsvg-convert' => [
            'binary' => env('RSVG_CONVERT_PATH', 'rsvg-convert'),
            'timeout' => env('RSVG_CONVERT_TIMEOUT', 60),
        ],
    ],

    'default_disk' => env('SVG_CONVERTER_DISK', 'local'),
];
```

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

### Custom binary paths

```ini
RESVG_PATH=/usr/local/bin/resvg
INKSCAPE_PATH=/opt/homebrew/bin/inkscape
RSVG_CONVERT_PATH=/usr/bin/rsvg-convert
```

### Increase timeout for large files

```ini
RESVG_TIMEOUT=120
INKSCAPE_TIMEOUT=300
RSVG_CONVERT_TIMEOUT=120
```

### Use S3 as default disk

```ini
SVG_CONVERTER_DISK=s3
```
