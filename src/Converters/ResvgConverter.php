<?php

namespace Laratusk\Larasvg\Converters;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

class ResvgConverter extends AbstractConverter
{
    /**
     * Supported export formats for Resvg.
     */
    public const array SUPPORTED_FORMATS = ['png'];

    /**
     * The output path for the positional argument.
     */
    protected ?string $outputPath = null;

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
                is_numeric($value) => "--{$option} {$value}",
                default => "--{$option} ".escapeshellarg((string) $value),
            };
        }

        $parts[] = escapeshellarg($this->inputPath);

        if ($this->outputPath !== null && $this->outputPath !== '-') {
            $parts[] = escapeshellarg($this->outputPath);
        }

        return implode(' ', $parts);
    }

    /**
     * Convert and return the raw output (stdout).
     * Resvg uses `-c` flag to output to stdout.
     */
    #[\Override]
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

    #[\Override]
    protected function widthOption(): string
    {
        return 'width';
    }

    #[\Override]
    protected function heightOption(): string
    {
        return 'height';
    }

    #[\Override]
    protected function dpiOption(): string
    {
        return 'dpi';
    }

    #[\Override]
    protected function backgroundOption(): string
    {
        return 'background';
    }

    #[\Override]
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
