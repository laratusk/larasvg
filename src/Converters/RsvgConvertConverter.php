<?php

namespace Laratusk\Larasvg\Converters;

use Laratusk\Larasvg\Exceptions\SvgConverterException;

class RsvgConvertConverter extends AbstractConverter
{
    /**
     * Supported export formats for rsvg-convert.
     */
    public const SUPPORTED_FORMATS = ['png', 'pdf', 'ps', 'eps', 'svg'];

    /**
     * Background color stored for combining with opacity.
     */
    protected ?string $backgroundColor = null;

    /**
     * Background opacity for combining with color.
     */
    protected ?float $backgroundOpacity = null;

    // -------------------------------------------------------------------------
    // Command Building
    // -------------------------------------------------------------------------

    public function buildCommand(): string
    {
        $parts = [escapeshellarg($this->binary)];

        foreach ($this->options as $option => $value) {
            $parts[] = match (true) {
                $value === null => "--{$option}",
                is_bool($value) => "--{$option}=".($value ? 'true' : 'false'),
                is_numeric($value) => "--{$option}={$value}",
                is_string($value) => "--{$option}=".escapeshellarg($value),
                is_scalar($value) => "--{$option}=".escapeshellarg($value),
                default => "--{$option}",
            };
        }

        $parts[] = escapeshellarg($this->inputPath);

        if ($this->outputPath !== null) {
            $parts[] = '-o '.escapeshellarg($this->outputPath);
        }

        return implode(' ', $parts);
    }

    // -------------------------------------------------------------------------
    // Background (overridden — rsvg-convert has no --background-opacity flag)
    // -------------------------------------------------------------------------

    /**
     * Set the background color.
     * Accepts HEX (#ff007f), RGB (rgb(255,0,128)), or RGBA (rgba(255,0,128,0.5)).
     */
    public function setBackground(string $color): static
    {
        if (! $this->isValidColor($color)) {
            throw new SvgConverterException('Supported color formats are HEX (#ff007f), RGB (rgb(255,0,128)), and RGBA (rgba(255,0,128,0.5)).');
        }

        $this->backgroundColor = $color;

        return $this->applyBackgroundColor();
    }

    /**
     * Set the background opacity (0.0–1.0).
     *
     * Note: rsvg-convert has no --background-opacity flag. The opacity is
     * combined with the background color into an RGBA value automatically.
     */
    public function setBackgroundOpacity(float $value): static
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new SvgConverterException('Background opacity must be between 0.0 and 1.0.');
        }

        $this->backgroundOpacity = $value;

        return $this->applyBackgroundColor();
    }

    // -------------------------------------------------------------------------
    // DPI (overridden — rsvg-convert uses --dpi-x and --dpi-y separately)
    // -------------------------------------------------------------------------

    /**
     * Set the DPI (applies to both X and Y axes).
     */
    public function setDpi(?int $dpi): static
    {
        if ($dpi !== null) {
            $this->withOption('dpi-x', $dpi);
            $this->withOption('dpi-y', $dpi);
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // rsvg-convert-specific fluent methods
    // -------------------------------------------------------------------------

    /**
     * Set the zoom factor (e.g. 2.5 = 250%).
     *
     * @param float $zoom Zoom factor (e.g., 2.0)
     */
    public function setZoom(float $zoom): static
    {
        return $this->withOption('zoom', $zoom);
    }

    /**
     * Set the horizontal zoom factor.
     *
     * @param float $zoom Horizontal zoom factor
     */
    public function setXZoom(float $zoom): static
    {
        return $this->withOption('x-zoom', $zoom);
    }

    /**
     * Set the vertical zoom factor.
     *
     * @param float $zoom Vertical zoom factor
     */
    public function setYZoom(float $zoom): static
    {
        return $this->withOption('y-zoom', $zoom);
    }

    /**
     * Preserve the aspect ratio when scaling.
     */
    public function keepAspectRatio(bool $keep = true): static
    {
        if ($keep) {
            $this->withFlag('keep-aspect-ratio');
        } else {
            unset($this->options['keep-aspect-ratio']);
        }

        return $this;
    }

    /**
     * Apply an external CSS stylesheet to the SVG.
     *
     * @param string $path Path to the CSS stylesheet
     */
    public function setStylesheet(string $path): static
    {
        return $this->withOption('stylesheet', $path);
    }

    /**
     * Disable SVG parser guards (for large or complex SVGs).
     */
    public function unlimited(bool $unlimited = true): static
    {
        if ($unlimited) {
            $this->withFlag('unlimited');
        } else {
            unset($this->options['unlimited']);
        }

        return $this;
    }

    /**
     * Set the page width for PDF/PS output (e.g., '8.5in', '210mm').
     *
     * @param string $width CSS length value (must be paired with setPageHeight)
     */
    public function setPageWidth(string $width): static
    {
        return $this->withOption('page-width', $width);
    }

    /**
     * Set the page height for PDF/PS output (e.g., '11in', '297mm').
     *
     * @param string $height CSS length value (must be paired with setPageWidth)
     */
    public function setPageHeight(string $height): static
    {
        return $this->withOption('page-height', $height);
    }

    /**
     * Set the top margin/offset for page output.
     *
     * @param string $margin CSS length value
     */
    public function setTopMargin(string $margin): static
    {
        return $this->withOption('top', $margin);
    }

    /**
     * Set the left margin/offset for page output.
     *
     * @param string $margin CSS length value
     */
    public function setLeftMargin(string $margin): static
    {
        return $this->withOption('left', $margin);
    }

    /**
     * Control whether compressed image data is kept in PDF/PS output.
     *
     * Passing true adds --keep-image-data (default for PDF/PS).
     * Passing false adds --no-keep-image-data (embed uncompressed RGB).
     */
    public function keepImageData(bool $keep = true): static
    {
        if ($keep) {
            unset($this->options['no-keep-image-data']);
            $this->withFlag('keep-image-data');
        } else {
            unset($this->options['keep-image-data']);
            $this->withFlag('no-keep-image-data');
        }

        return $this;
    }

    /**
     * Set the base URI for resolving relative references in the SVG.
     *
     * @param string $uri Base URI (e.g., 'file:///path/to/assets/')
     */
    public function setBaseUri(string $uri): static
    {
        return $this->withOption('base-uri', $uri);
    }

    // -------------------------------------------------------------------------
    // Option name overrides for rsvg-convert
    // -------------------------------------------------------------------------

    protected function widthOption(): string
    {
        return 'width';
    }

    protected function heightOption(): string
    {
        return 'height';
    }

    protected function backgroundOption(): string
    {
        return 'background-color';
    }

    protected function backgroundOpacityOption(): string
    {
        return 'background-color';
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    protected function providerName(): string
    {
        return 'rsvg-convert';
    }

    protected function applyExportOptions(string $exportPath): void
    {
        if ($this->format !== null) {
            $this->withOption('format', $this->format);
        }

        if ($exportPath !== '-') {
            $this->outputPath = $exportPath;
        }
        // For stdout ('-'), no -o flag is needed; output goes to stdout by default.
    }

    /**
     * Validate a color string — accepts HEX, RGB, and RGBA.
     */
    protected function isValidColor(string $color): bool
    {
        if ($this->isValidHexColor($color)) {
            return true;
        }

        if ($this->isValidRgbColor($color)) {
            return true;
        }

        return $this->isValidRgbaColor($color);
    }

    /**
     * Check whether a string is a valid RGBA color.
     */
    protected function isValidRgbaColor(string $color): bool
    {
        $color = strtolower(str_replace(' ', '', $color));

        return (bool) preg_match('/^rgba\(\d{1,3},\d{1,3},\d{1,3},(0(\.\d+)?|1(\.0+)?)\)$/', $color);
    }

    /**
     * Apply the background color (combined with opacity if set) to the options.
     */
    private function applyBackgroundColor(): static
    {
        if ($this->backgroundColor === null) {
            return $this;
        }

        if ($this->backgroundOpacity !== null) {
            $color = $this->combineColorWithOpacity($this->backgroundColor, $this->backgroundOpacity);
        } else {
            $color = $this->backgroundColor;
        }

        $this->options['background-color'] = $color;

        return $this;
    }

    /**
     * Combine a color with an opacity value into an RGBA string.
     *
     * @param string $color   HEX or RGB color
     * @param float  $opacity Opacity between 0.0 and 1.0
     */
    private function combineColorWithOpacity(string $color, float $opacity): string
    {
        if (str_starts_with($color, '#')) {
            $hex = ltrim($color, '#');

            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }

            $r = (int) hexdec(substr($hex, 0, 2));
            $g = (int) hexdec(substr($hex, 2, 2));
            $b = (int) hexdec(substr($hex, 4, 2));

            return "rgba({$r},{$g},{$b},{$opacity})";
        }

        // rgb(r,g,b) → rgba(r,g,b,opacity)
        $inner = substr($color, 4, -1); // strip 'rgb(' and ')'

        return "rgba({$inner},{$opacity})";
    }
}
