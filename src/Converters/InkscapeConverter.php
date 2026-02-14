<?php

namespace Laratusk\Larasvg\Converters;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

class InkscapeConverter extends AbstractConverter
{
    /**
     * Supported export formats for Inkscape.
     */
    public const array SUPPORTED_FORMATS = ['svg', 'png', 'ps', 'eps', 'pdf', 'emf', 'wmf'];

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

    /**
     * Get the list of available Inkscape actions.
     */
    public function actionList(): string
    {
        $result = Process::timeout(10)->run(escapeshellarg($this->binary).' --action-list');

        if ($result->failed()) {
            throw SvgConverterException::fromProcess($result, "{$this->binary} --action-list", $this->providerName());
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
                is_numeric($value) => "--{$option}={$value}",
                default => "--{$option}=".escapeshellarg((string) $value),
            };
        }

        $parts[] = escapeshellarg($this->inputPath);

        return implode(' ', $parts);
    }

    // -------------------------------------------------------------------------
    // Pages & Objects
    // -------------------------------------------------------------------------

    /**
     * Set the page(s) to export.
     */
    public function setPage(int|string $page): static
    {
        return $this->withOption('pages', $page);
    }

    /**
     * Export the first page only.
     */
    public function firstPage(): static
    {
        return $this->setPage(1);
    }

    /**
     * Set the export-id for exporting a specific object.
     */
    public function exportId(string $id, bool $idOnly = false): static
    {
        if ($idOnly) {
            $this->withFlag('export-id-only');
        }

        return $this->withOption('export-id', $id);
    }

    // -------------------------------------------------------------------------
    // Export Area
    // -------------------------------------------------------------------------

    /**
     * Export the page area.
     */
    public function exportAreaPage(): static
    {
        return $this->withFlag('export-area-page');
    }

    /**
     * Export the drawing area (bounding box of all objects).
     */
    public function exportAreaDrawing(): static
    {
        return $this->withFlag('export-area-drawing');
    }

    /**
     * Set a custom export area.
     */
    public function exportArea(float $x0, float $y0, float $x1, float $y1): static
    {
        return $this->withOption('export-area', "{$x0}:{$y0}:{$x1}:{$y1}");
    }

    /**
     * Snap the export area to integer px values.
     */
    public function exportAreaSnap(): static
    {
        return $this->withFlag('export-area-snap');
    }

    // -------------------------------------------------------------------------
    // Export Modifiers
    // -------------------------------------------------------------------------

    /**
     * Convert text objects to paths on export.
     */
    public function exportTextToPath(): static
    {
        return $this->withFlag('export-text-to-path');
    }

    /**
     * Export as plain SVG (no Inkscape namespaces).
     */
    public function exportPlainSvg(): static
    {
        return $this->withFlag('export-plain-svg');
    }

    /**
     * Overwrite the input file.
     */
    public function exportOverwrite(): static
    {
        return $this->withFlag('export-overwrite');
    }

    /**
     * Set the PDF version for export.
     */
    public function exportPdfVersion(string $version = '1.4'): static
    {
        return $this->withOption('export-pdf-version', $version);
    }

    /**
     * Set the PostScript level for PS/EPS export.
     */
    public function exportPsLevel(int $level = 3): static
    {
        return $this->withOption('export-ps-level', $level);
    }

    /**
     * Set the PNG color mode.
     */
    public function exportPngColorMode(string $mode): static
    {
        return $this->withOption('export-png-color-mode', $mode);
    }

    /**
     * Set the PNG compression level (0-9).
     */
    public function exportPngCompression(int $level): static
    {
        return $this->withOption('export-png-compression', $level);
    }

    /**
     * Set the PNG antialiasing level (0-3).
     */
    public function exportPngAntialias(int $level): static
    {
        return $this->withOption('export-png-antialias', $level);
    }

    /**
     * Set a margin around the exported area.
     */
    public function exportMargin(float|int $margin): static
    {
        return $this->withOption('export-margin', $margin);
    }

    /**
     * Export LaTeX companion file.
     */
    public function exportLatex(): static
    {
        return $this->withFlag('export-latex');
    }

    /**
     * Ignore filters and export as vectors.
     */
    public function exportIgnoreFilters(): static
    {
        return $this->withFlag('export-ignore-filters');
    }

    /**
     * Remove unused defs from the SVG.
     */
    public function vacuumDefs(): static
    {
        return $this->withFlag('vacuum-defs');
    }

    // -------------------------------------------------------------------------
    // Query
    // -------------------------------------------------------------------------

    /**
     * Query object dimensions. Returns an associative array with x, y, width, height.
     *
     * @return array<string, string>
     */
    public function query(?string $objectId = null): array
    {
        $opts = [];

        if ($objectId) {
            $opts['query-id'] = $objectId;
        }

        $results = [];

        foreach (['query-x', 'query-y', 'query-width', 'query-height'] as $query) {
            $clone = clone $this;
            $clone->withOptions($opts);
            $clone->withFlag($query);

            $result = $clone->execute();
            $key = str_replace('query-', '', $query);
            $results[$key] = trim($result->output());
        }

        return $results;
    }

    protected function providerName(): string
    {
        return 'Inkscape';
    }

    protected function applyExportOptions(string $exportPath): void
    {
        if ($this->format) {
            $this->withOption('export-type', $this->format);
        }

        $this->withOption('export-filename', $exportPath);
    }
}
