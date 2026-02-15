<?php

namespace Laratusk\Larasvg\Converters;

use Laratusk\Larasvg\Exceptions\SvgConverterException;

class CairosvgConverter extends AbstractConverter
{
    /**
     * Supported export formats for CairoSVG.
     */
    public const SUPPORTED_FORMATS = ['png', 'pdf', 'ps', 'svg'];

    // -------------------------------------------------------------------------
    // Command Building
    // -------------------------------------------------------------------------

    public function buildCommand(): string
    {
        $parts = [escapeshellarg($this->binary)];
        $parts[] = escapeshellarg($this->inputPath);

        foreach ($this->options as $option => $value) {
            $prefix = strlen($option) === 1 ? '-' : '--';
            $parts[] = match (true) {
                $value === null => "{$prefix}{$option}",
                is_bool($value) => "{$prefix}{$option} ".($value ? 'true' : 'false'),
                is_numeric($value) => "{$prefix}{$option} {$value}",
                is_string($value) => "{$prefix}{$option} ".escapeshellarg($value),
                is_scalar($value) => "{$prefix}{$option} ".escapeshellarg($value),
                default => "{$prefix}{$option}",
            };
        }

        if ($this->outputPath !== null) {
            $parts[] = '-o '.escapeshellarg($this->outputPath);
        }

        return implode(' ', $parts);
    }

    // -------------------------------------------------------------------------
    // Background (not supported by CairoSVG CLI)
    // -------------------------------------------------------------------------

    /**
     * Not supported by CairoSVG CLI.
     *
     * @throws SvgConverterException
     */
    public function setBackground(string $color): static
    {
        throw new SvgConverterException(
            'CairoSVG does not support background color via CLI. Use the Python API instead.',
        );
    }

    /**
     * Not supported by CairoSVG CLI.
     *
     * @throws SvgConverterException
     */
    public function setBackgroundOpacity(float $value): static
    {
        throw new SvgConverterException(
            'CairoSVG does not support background opacity via CLI. Use the Python API instead.',
        );
    }

    // -------------------------------------------------------------------------
    // CairoSVG-specific fluent methods
    // -------------------------------------------------------------------------

    /**
     * Set the output scaling factor.
     *
     * @param float $scale Scaling factor (e.g., 2.0 for 200%)
     */
    public function setScale(float $scale): static
    {
        return $this->withOption('s', $scale);
    }

    /**
     * Set the parent container width in pixels (for SVGs using percentage widths).
     *
     * @param int $width Container width in pixels
     */
    public function setContainerWidth(int $width): static
    {
        return $this->withOption('W', $width);
    }

    /**
     * Set the parent container height in pixels (for SVGs using percentage heights).
     *
     * @param int $height Container height in pixels
     */
    public function setContainerHeight(int $height): static
    {
        return $this->withOption('H', $height);
    }

    /**
     * Set both container dimensions at once.
     *
     * @param int $width  Container width in pixels
     * @param int $height Container height in pixels
     */
    public function setContainerDimensions(int $width, int $height): static
    {
        return $this->setContainerWidth($width)->setContainerHeight($height);
    }

    /**
     * Set the desired output width in pixels.
     * Alias for setWidth() with an explicit name.
     *
     * @param int $width Output width in pixels
     */
    public function setOutputWidth(int $width): static
    {
        return $this->withOption('output-width', $width);
    }

    /**
     * Set the desired output height in pixels.
     * Alias for setHeight() with an explicit name.
     *
     * @param int $height Output height in pixels
     */
    public function setOutputHeight(int $height): static
    {
        return $this->withOption('output-height', $height);
    }

    /**
     * Enable XML entity resolution and allow very large files.
     *
     * WARNING: This flag makes CairoSVG vulnerable to XXE attacks.
     * Only use on trusted input.
     */
    public function unsafe(): static
    {
        return $this->withFlag('u');
    }

    // -------------------------------------------------------------------------
    // Option name overrides for CairoSVG
    // -------------------------------------------------------------------------

    protected function widthOption(): string
    {
        return 'output-width';
    }

    protected function heightOption(): string
    {
        return 'output-height';
    }

    protected function dpiOption(): string
    {
        return 'd';
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    protected function providerName(): string
    {
        return 'CairoSVG';
    }

    protected function applyExportOptions(string $exportPath): void
    {
        if ($this->format !== null) {
            $this->withOption('f', $this->format);
        }

        if ($exportPath !== '-') {
            $this->outputPath = $exportPath;
        }
        // For stdout ('-'), no -o flag is needed; output goes to stdout by default.
    }
}
