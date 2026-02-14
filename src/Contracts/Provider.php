<?php

namespace Laratusk\SvgConverter\Contracts;

use Illuminate\Contracts\Process\ProcessResult;

interface Provider
{
    /**
     * Get the list of supported output formats.
     *
     * @return array<string>
     */
    public function supportedFormats(): array;

    /**
     * Get the provider version string.
     */
    public function version(): string;

    /**
     * Set the export format.
     */
    public function setFormat(string $format): static;

    /**
     * Set the export width in pixels.
     */
    public function setWidth(int $width): static;

    /**
     * Set the export height in pixels.
     */
    public function setHeight(int $height): static;

    /**
     * Set the export DPI.
     */
    public function setDpi(?int $dpi): static;

    /**
     * Set width, height, and DPI in one call.
     */
    public function setDimensions(int $width, int $height, ?int $dpi = null): static;

    /**
     * Set the export background color.
     */
    public function setBackground(string $color): static;

    /**
     * Set the background opacity.
     */
    public function setBackgroundOpacity(float $value): static;

    /**
     * Convert and save to the given export path.
     */
    public function convert(?string $exportName = null): string;

    /**
     * Convert and save to a local file path.
     */
    public function toFile(string $outputPath): string;

    /**
     * Convert and write the output to a file on the given disk.
     */
    public function toDisk(string $disk, string $path, ?string $format = null): string;

    /**
     * Convert and return the raw output (stdout).
     */
    public function toStdout(?string $format = 'png'): string;

    /**
     * Run the command and return the raw ProcessResult.
     */
    public function raw(): ProcessResult;

    /**
     * Build the full CLI command string.
     */
    public function buildCommand(): string;

    /**
     * Set the process timeout in seconds.
     */
    public function timeout(int $seconds): static;

    /**
     * Add an arbitrary CLI option with a value.
     */
    public function withOption(string $option, mixed $value): static;

    /**
     * Add an arbitrary CLI flag (no value).
     */
    public function withFlag(string $flag): static;

    /**
     * Add multiple options at once.
     *
     * @param array<string|int, mixed> $options
     */
    public function withOptions(array $options): static;

    /**
     * Clean up temporary files.
     */
    public function cleanup(): void;

    /**
     * Register a temp file for cleanup.
     */
    public function addTempFile(string $path): void;

    /**
     * Create a temp file and register it for cleanup.
     */
    public function createTempFile(string $name): string;
}
