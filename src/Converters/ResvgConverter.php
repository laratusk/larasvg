<?php

namespace Laratusk\Larasvg\Converters;

class ResvgConverter extends AbstractConverter
{
    /**
     * Supported export formats for Resvg.
     */
    public const SUPPORTED_FORMATS = ['png'];

    // -------------------------------------------------------------------------
    // Command Building
    // -------------------------------------------------------------------------

    public function buildCommand(): string
    {
        $parts = [escapeshellarg($this->binary)];
        $postInputFlags = [];

        foreach ($this->options as $option => $value) {
            $prefix = strlen($option) === 1 ? '-' : '--';
            $rendered = match (true) {
                $value === null => "{$prefix}{$option}",
                is_bool($value) => "{$prefix}{$option}=".($value ? 'true' : 'false'),
                is_numeric($value) => "{$prefix}{$option} {$value}",
                is_string($value) => "{$prefix}{$option} ".escapeshellarg($value),
                is_scalar($value) => "{$prefix}{$option} ".escapeshellarg($value),
                default => "{$prefix}{$option}",
            };

            // Single-char flags (like -c for stdout) must come after input
            if (strlen($option) === 1 && $value === null) {
                $postInputFlags[] = $rendered;
            } else {
                $parts[] = $rendered;
            }
        }

        $parts[] = escapeshellarg($this->inputPath);

        // Append post-input flags (e.g. -c for stdout)
        foreach ($postInputFlags as $flag) {
            $parts[] = $flag;
        }

        if ($this->outputPath !== null && $this->outputPath !== '-') {
            $parts[] = escapeshellarg($this->outputPath);
        }

        return implode(' ', $parts);
    }

    /**
     * Convert and return the raw output (stdout).
     * Resvg uses `-c` flag to output to stdout.
     */
    public function toStdout(?string $format = 'png'): string
    {
        if ($format) {
            $this->setFormat($format);
        }

        $this->applyExportOptions('-');

        $result = $this->execute();

        return $result->output();
    }

    // -------------------------------------------------------------------------
    // Resvg-Specific Options
    // -------------------------------------------------------------------------

    /**
     * Set zoom factor.
     */
    public function setZoom(float $zoom): static
    {
        return $this->withOption('zoom', $zoom);
    }

    /**
     * Set shape rendering mode (optimizeSpeed, crispEdges, geometricPrecision).
     */
    public function setShapeRendering(string $mode): static
    {
        return $this->withOption('shape-rendering', $mode);
    }

    /**
     * Set text rendering mode.
     */
    public function setTextRendering(string $mode): static
    {
        return $this->withOption('text-rendering', $mode);
    }

    /**
     * Set image rendering mode.
     */
    public function setImageRendering(string $mode): static
    {
        return $this->withOption('image-rendering', $mode);
    }

    /**
     * Set default font family.
     */
    public function setDefaultFontFamily(string $family): static
    {
        return $this->withOption('font-family', $family);
    }

    /**
     * Set default font size.
     */
    public function setDefaultFontSize(int $size): static
    {
        return $this->withOption('font-size', $size);
    }

    /**
     * Use a specific font file.
     */
    public function useFontFile(string $path): static
    {
        return $this->withOption('use-font-file', $path);
    }

    /**
     * Use fonts from a directory.
     */
    public function useFontsDir(string $path): static
    {
        return $this->withOption('use-fonts-dir', $path);
    }

    /**
     * Skip system fonts.
     */
    public function skipSystemFonts(): static
    {
        return $this->withFlag('skip-system-fonts');
    }

    /**
     * Set the resources directory for relative image paths.
     */
    public function setResourcesDir(string $path): static
    {
        return $this->withOption('resources-dir', $path);
    }

    protected function providerName(): string
    {
        return 'Resvg';
    }

    // -------------------------------------------------------------------------
    // Option name overrides for Resvg
    // -------------------------------------------------------------------------

    protected function widthOption(): string
    {
        return 'width';
    }

    protected function heightOption(): string
    {
        return 'height';
    }

    protected function dpiOption(): string
    {
        return 'dpi';
    }

    protected function backgroundOption(): string
    {
        return 'background';
    }

    protected function backgroundOpacityOption(): string
    {
        return 'background-opacity';
    }

    protected function applyExportOptions(string $exportPath): void
    {
        if ($exportPath === '-') {
            $this->withFlag('c');
            $this->outputPath = null;
        } else {
            $this->outputPath = $exportPath;
        }
    }
}
