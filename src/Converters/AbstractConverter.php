<?php

namespace Laratusk\Larasvg\Converters;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

abstract class AbstractConverter implements Provider
{
    /**
     * CLI options as key => value pairs.
     * Keys without values (flags) use null.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * The export format (e.g. png, pdf, svg).
     */
    protected ?string $format = null;

    /**
     * The export filename / path.
     */
    protected ?string $exportFilename = null;

    /**
     * Whether the format was inferred from the export name.
     */
    protected bool $formatFromExport = false;

    /**
     * Temporary files to clean up.
     *
     * @var array<int, string>
     */
    protected array $tempFiles = [];

    /**
     * Whether cleanup has already been performed.
     */
    protected bool $cleaned = false;

    public function __construct(
        public readonly string $inputPath,
        public readonly string $binary,
        protected int $timeout = 60,
    ) {}

    public function __destruct()
    {
        $this->cleanup();
    }

    /**
     * Get the provider name for error messages.
     */
    abstract protected function providerName(): string;

    /**
     * Apply export options to the CLI command (provider-specific).
     */
    abstract protected function applyExportOptions(string $exportPath): void;

    /**
     * Get the CLI options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the process timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    // -------------------------------------------------------------------------
    // Format & Dimensions
    // -------------------------------------------------------------------------

    public function setFormat(string $format): static
    {
        $format = strtolower($format);

        if (! in_array($format, $this->supportedFormats(), true)) {
            throw new SvgConverterException(
                "Unsupported export format: {$format}. Supported by {$this->providerName()}: ".implode(', ', $this->supportedFormats()),
            );
        }

        $this->format = $format;

        return $this;
    }

    public function setDimensions(int $width = 1024, int $height = 1024, ?int $dpi = 96): static
    {
        return $this->setWidth($width)->setHeight($height)->setDpi($dpi);
    }

    public function setWidth(int $width): static
    {
        return $this->withOption($this->widthOption(), $width);
    }

    public function setHeight(int $height): static
    {
        return $this->withOption($this->heightOption(), $height);
    }

    public function setDpi(?int $dpi): static
    {
        if ($dpi !== null) {
            return $this->withOption($this->dpiOption(), $dpi);
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Background
    // -------------------------------------------------------------------------

    public function setBackground(string $color): static
    {
        if (! $this->isValidColor($color)) {
            throw new SvgConverterException('Supported color formats are HEX (#ff007f) and RGB (rgb(255,0,128)).');
        }

        return $this->withOption($this->backgroundOption(), $color);
    }

    public function setBackgroundOpacity(float $value): static
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new SvgConverterException('Background opacity must be between 0.0 and 1.0.');
        }

        return $this->withOption($this->backgroundOpacityOption(), $value);
    }

    // -------------------------------------------------------------------------
    // Dynamic Options
    // -------------------------------------------------------------------------

    public function withOption(string $option, mixed $value): static
    {
        $this->options[$option] = $value;

        return $this;
    }

    public function withFlag(string $flag): static
    {
        $this->options[$flag] = null;

        return $this;
    }

    /**
     * @param array<string|int, mixed> $options
     */
    public function withOptions(array $options): static
    {
        foreach ($options as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->withFlag($value);
            } elseif (is_string($key)) {
                $this->withOption($key, $value);
            }
        }

        return $this;
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Execution: Convert
    // -------------------------------------------------------------------------

    public function convert(?string $exportName = null): string
    {
        $this->prepareExportFormat($exportName);
        $exportPath = $this->prepareExportPath($exportName);
        $this->applyExportOptions($exportPath);

        $result = $this->execute();

        if ($exportPath === '-') {
            return $result->output();
        }

        return $exportPath;
    }

    public function toDisk(string $disk, string $path, ?string $format = null): string
    {
        if ($format) {
            $this->setFormat($format);
        }

        $extension = $this->format ?? pathinfo($path, PATHINFO_EXTENSION);
        $tempOutput = $this->createTempFile("svgconverter_output.{$extension}");

        if (! $this->format && $extension) {
            $this->setFormat($extension);
        }

        $this->applyExportOptions($tempOutput);
        $this->execute();

        if (! file_exists($tempOutput)) {
            throw new SvgConverterException("{$this->providerName()} did not produce the expected output file: {$tempOutput}");
        }

        $contents = file_get_contents($tempOutput);

        if ($contents === false) {
            throw new SvgConverterException("Failed to read converted file: {$tempOutput}");
        }

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    public function toFile(string $outputPath): string
    {
        $extension = pathinfo($outputPath, PATHINFO_EXTENSION);

        if (! $this->format && $extension) {
            $this->setFormat($extension);
        }

        $this->applyExportOptions($outputPath);
        $this->execute();

        return $outputPath;
    }

    public function toStdout(?string $format = 'png'): string
    {
        if ($format) {
            $this->setFormat($format);
        }

        $this->applyExportOptions('-');

        $result = $this->execute();

        return $result->output();
    }

    public function raw(): ProcessResult
    {
        return Process::timeout($this->timeout)->run($this->buildCommand());
    }

    // -------------------------------------------------------------------------
    // Temp File Management
    // -------------------------------------------------------------------------

    public function addTempFile(string $path): void
    {
        $this->tempFiles[] = $path;
    }

    public function createTempFile(string $name = 'svgconverter_temp'): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('svgconverter_').'_'.$name;
        $this->tempFiles[] = $path;

        return $path;
    }

    public function cleanup(): void
    {
        if ($this->cleaned) {
            return;
        }

        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        $this->tempFiles = [];
        $this->cleaned = false;
    }

    /**
     * Get the CLI option name for width.
     */
    protected function widthOption(): string
    {
        return 'export-width';
    }

    /**
     * Get the CLI option name for height.
     */
    protected function heightOption(): string
    {
        return 'export-height';
    }

    /**
     * Get the CLI option name for DPI.
     */
    protected function dpiOption(): string
    {
        return 'export-dpi';
    }

    /**
     * Get the CLI option name for background color.
     */
    protected function backgroundOption(): string
    {
        return 'export-background';
    }

    /**
     * Get the CLI option name for background opacity.
     */
    protected function backgroundOpacityOption(): string
    {
        return 'export-background-opacity';
    }

    // -------------------------------------------------------------------------
    // Internal Helpers
    // -------------------------------------------------------------------------

    /**
     * Execute the built command via Laravel Process.
     *
     * @throws SvgConverterException
     */
    protected function execute(): ProcessResult
    {
        $command = $this->buildCommand();

        $result = Process::timeout($this->timeout)->run($command);

        if ($result->failed()) {
            throw SvgConverterException::fromProcess($result, $command, $this->providerName());
        }

        return $result;
    }

    /**
     * Prepare the export format. If not set, infer from the export name.
     */
    protected function prepareExportFormat(?string $exportName): void
    {
        if ($this->format !== null) {
            return;
        }

        if ($exportName && $exportName !== '-') {
            $extension = pathinfo($exportName, PATHINFO_EXTENSION);

            if ($extension !== '' && $extension !== '0') {
                $this->setFormat($extension);
                $this->formatFromExport = true;

                return;
            }
        }

        throw new SvgConverterException('No export format specified. Use setFormat() or provide a filename with an extension.');
    }

    /**
     * Prepare the full export path from the given export name.
     */
    protected function prepareExportPath(?string $exportName): string
    {
        if ($exportName === '-') {
            return '-';
        }

        $inputDir = dirname($this->inputPath);

        if ($exportName === null) {
            $baseName = pathinfo($this->inputPath, PATHINFO_FILENAME);
            $exportName = $baseName.'.'.$this->format;
        }

        if (! $this->isAbsolutePath($exportName)) {
            $exportName = $inputDir.DIRECTORY_SEPARATOR.$exportName;
        }

        $outputDir = dirname($exportName);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        return $exportName;
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\\\\/', $path);
    }

    protected function isValidColor(string $color): bool
    {
        if ($this->isValidHexColor($color)) {
            return true;
        }

        return $this->isValidRgbColor($color);
    }

    protected function isValidHexColor(string $color): bool
    {
        return (bool) preg_match('/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $color);
    }

    protected function isValidRgbColor(string $color): bool
    {
        $color = strtolower(str_replace(' ', '', $color));

        return (bool) preg_match('/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/', $color);
    }
}
