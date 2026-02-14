# Converters

## AbstractConverter

`Laratusk\Larasvg\Converters\AbstractConverter`

The abstract base class that provides shared logic for all converters.

### Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| `$inputPath` | `string` | `public readonly` | Path to the input SVG file |
| `$binary` | `string` | `public readonly` | Path to the converter binary |
| `$timeout` | `int` | `public private(set)` | Process timeout in seconds |
| `$options` | `array` | `public private(set)` | CLI options as key-value pairs |

### Abstract Methods

Subclasses must implement:

```php
abstract protected function providerName(): string;
abstract protected function applyExportOptions(string $exportPath): void;
abstract public function supportedFormats(): array;
abstract public function version(): string;
abstract public function buildCommand(): string;
```

### Option Name Methods

Subclasses can override these to match their CLI flag names:

| Method | Default | Resvg | Inkscape |
|--------|---------|-------|----------|
| `widthOption()` | `export-width` | `width` | `export-width` |
| `heightOption()` | `export-height` | `height` | `export-height` |
| `dpiOption()` | `export-dpi` | `dpi` | `export-dpi` |
| `backgroundOption()` | `export-background` | `background` | `export-background` |
| `backgroundOpacityOption()` | `export-background-opacity` | `background-opacity` | `export-background-opacity` |

---

## ResvgConverter

`Laratusk\Larasvg\Converters\ResvgConverter`

Implements SVG to PNG conversion using the Resvg CLI.

### Supported Formats

```php
public const array SUPPORTED_FORMATS = ['png'];
```

### Additional Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setZoom` | `(float $zoom): static` | Set zoom factor |
| `setShapeRendering` | `(string $mode): static` | Shape rendering mode |
| `setTextRendering` | `(string $mode): static` | Text rendering mode |
| `setImageRendering` | `(string $mode): static` | Image rendering mode |
| `setDefaultFontFamily` | `(string $family): static` | Default font family |
| `setDefaultFontSize` | `(int $size): static` | Default font size |
| `useFontFile` | `(string $path): static` | Use specific font file |
| `useFontsDir` | `(string $path): static` | Load fonts from directory |
| `skipSystemFonts` | `(): static` | Skip system fonts |
| `setResourcesDir` | `(string $path): static` | Resources directory |

---

## InkscapeConverter

`Laratusk\Larasvg\Converters\InkscapeConverter`

Implements SVG conversion using the Inkscape CLI. Supports multiple output formats.

### Supported Formats

```php
public const array SUPPORTED_FORMATS = ['svg', 'png', 'ps', 'eps', 'pdf', 'emf', 'wmf'];
```

### Additional Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setPage` | `(int\|string $page): static` | Set page(s) to export |
| `firstPage` | `(): static` | Export first page only |
| `exportId` | `(string $id, bool $idOnly = false): static` | Export specific object |
| `exportAreaPage` | `(): static` | Export page area |
| `exportAreaDrawing` | `(): static` | Export drawing area |
| `exportArea` | `(float $x0, float $y0, float $x1, float $y1): static` | Custom export area |
| `exportAreaSnap` | `(): static` | Snap area to pixels |
| `exportTextToPath` | `(): static` | Convert text to paths |
| `exportPlainSvg` | `(): static` | Export plain SVG |
| `exportOverwrite` | `(): static` | Overwrite input file |
| `exportPdfVersion` | `(string $version = '1.4'): static` | PDF version |
| `exportPsLevel` | `(int $level = 3): static` | PostScript level |
| `exportPngColorMode` | `(string $mode): static` | PNG color mode |
| `exportPngCompression` | `(int $level): static` | PNG compression (0-9) |
| `exportPngAntialias` | `(int $level): static` | PNG antialiasing (0-3) |
| `exportMargin` | `(float\|int $margin): static` | Export margin |
| `exportLatex` | `(): static` | LaTeX companion file |
| `exportIgnoreFilters` | `(): static` | Ignore SVG filters |
| `vacuumDefs` | `(): static` | Remove unused defs |
| `query` | `(?string $objectId = null): array` | Query dimensions |
| `actionList` | `(): string` | List available actions |
