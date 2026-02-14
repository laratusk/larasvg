<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InkscapeConverterTest extends TestCase
{
    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSvg = $this->createTempSvg();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (file_exists($this->testSvg)) {
            @unlink($this->testSvg);
        }
        parent::tearDown();
    }

    #[Test]
    public function it_returns_supported_formats(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);

        $formats = $converter->supportedFormats();

        $this->assertContains('svg', $formats);
        $this->assertContains('png', $formats);
        $this->assertContains('pdf', $formats);
        $this->assertContains('ps', $formats);
        $this->assertContains('eps', $formats);
        $this->assertContains('emf', $formats);
        $this->assertContains('wmf', $formats);
    }

    #[Test]
    public function it_sets_format_correctly(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setFormat('png');

        $this->assertEmpty($converter->options);
    }

    #[Test]
    public function it_throws_for_unsupported_format(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format');

        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setFormat('bmp');
    }

    #[Test]
    public function it_sets_dimensions_with_inkscape_option_names(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setDimensions(800, 600, 150);

        $this->assertEquals(800, $converter->options['export-width']);
        $this->assertEquals(600, $converter->options['export-height']);
        $this->assertEquals(150, $converter->options['export-dpi']);
    }

    #[Test]
    public function it_sets_background_with_inkscape_option_names(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setBackground('#ffffff');
        $converter->setBackgroundOpacity(0.5);

        $this->assertEquals('#ffffff', $converter->options['export-background']);
        $this->assertEquals(0.5, $converter->options['export-background-opacity']);
    }

    #[Test]
    public function it_sets_page(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setPage(2);

        $this->assertEquals(2, $converter->options['pages']);
    }

    #[Test]
    public function it_sets_first_page(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->firstPage();

        $this->assertEquals(1, $converter->options['pages']);
    }

    #[Test]
    public function it_sets_export_id(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportId('rect123');

        $this->assertEquals('rect123', $converter->options['export-id']);
        $this->assertArrayNotHasKey('export-id-only', $converter->options);
    }

    #[Test]
    public function it_sets_export_id_with_id_only(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportId('rect123', idOnly: true);

        $this->assertEquals('rect123', $converter->options['export-id']);
        $this->assertNull($converter->options['export-id-only']);
    }

    #[Test]
    public function it_sets_export_area_flags(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportAreaPage();
        $this->assertArrayHasKey('export-area-page', $converter->options);

        $converter2 = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter2->exportAreaDrawing();
        $this->assertArrayHasKey('export-area-drawing', $converter2->options);
    }

    #[Test]
    public function it_sets_custom_export_area(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportArea(0, 0, 100, 100);

        $this->assertEquals('0:0:100:100', $converter->options['export-area']);
    }

    #[Test]
    public function it_sets_export_area_snap(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportAreaSnap();

        $this->assertArrayHasKey('export-area-snap', $converter->options);
    }

    #[Test]
    public function it_sets_export_modifiers(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);

        $converter->exportTextToPath();
        $this->assertArrayHasKey('export-text-to-path', $converter->options);

        $converter->exportPlainSvg();
        $this->assertArrayHasKey('export-plain-svg', $converter->options);

        $converter->exportOverwrite();
        $this->assertArrayHasKey('export-overwrite', $converter->options);

        $converter->vacuumDefs();
        $this->assertArrayHasKey('vacuum-defs', $converter->options);
    }

    #[Test]
    public function it_sets_pdf_version(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportPdfVersion('1.5');

        $this->assertEquals('1.5', $converter->options['export-pdf-version']);
    }

    #[Test]
    public function it_sets_ps_level(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportPsLevel(2);

        $this->assertEquals(2, $converter->options['export-ps-level']);
    }

    #[Test]
    public function it_sets_png_options(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);

        $converter->exportPngColorMode('RGBA_8');
        $this->assertEquals('RGBA_8', $converter->options['export-png-color-mode']);

        $converter->exportPngCompression(9);
        $this->assertEquals(9, $converter->options['export-png-compression']);

        $converter->exportPngAntialias(3);
        $this->assertEquals(3, $converter->options['export-png-antialias']);
    }

    #[Test]
    public function it_sets_export_margin(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportMargin(10);

        $this->assertEquals(10, $converter->options['export-margin']);
    }

    #[Test]
    public function it_sets_export_latex(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportLatex();

        $this->assertArrayHasKey('export-latex', $converter->options);
    }

    #[Test]
    public function it_sets_export_ignore_filters(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->exportIgnoreFilters();

        $this->assertArrayHasKey('export-ignore-filters', $converter->options);
    }

    #[Test]
    public function it_builds_command_with_flags(): void
    {
        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->withFlag('export-area-page');
        $converter->withFlag('export-text-to-path');

        $command = $converter->buildCommand();

        $this->assertStringContainsString("'/usr/bin/inkscape'", $command);
        $this->assertStringContainsString('--export-area-page', $command);
        $this->assertStringContainsString('--export-text-to-path', $command);
        $this->assertStringContainsString(escapeshellarg($this->testSvg), $command);
    }

    #[Test]
    public function it_builds_command_with_keyed_options(): void
    {
        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->withOption('export-width', 800);
        $converter->withOption('export-height', 600);
        $converter->withOption('export-type', 'png');

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--export-width=800', $command);
        $this->assertStringContainsString('--export-height=600', $command);
        $this->assertStringContainsString("--export-type='png'", $command);
    }

    #[Test]
    public function it_builds_command_with_mixed_options(): void
    {
        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->withOption('export-width', 500);
        $converter->withFlag('export-area-drawing');
        $converter->withOption('export-type', 'pdf');
        $converter->withOption('export-filename', '/tmp/output.pdf');

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--export-width=500', $command);
        $this->assertStringContainsString('--export-area-drawing', $command);
        $this->assertStringContainsString("--export-type='pdf'", $command);
        $this->assertStringContainsString("--export-filename='/tmp/output.pdf'", $command);
    }

    #[Test]
    public function it_escapes_binary_and_input_path(): void
    {
        $svgWithSpaces = sys_get_temp_dir().'/test file with spaces.svg';
        file_put_contents($svgWithSpaces, '<svg></svg>');

        try {
            $converter = new InkscapeConverter($svgWithSpaces, '/opt/my inkscape/inkscape', 60);
            $command = $converter->buildCommand();

            $this->assertStringContainsString("'/opt/my inkscape/inkscape'", $command);
            $this->assertStringContainsString("'".$svgWithSpaces."'", $command);
        } finally {
            @unlink($svgWithSpaces);
        }
    }

    #[Test]
    public function it_handles_boolean_option_values(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->withOption('export-png-use-dithering', true);

        $command = $converter->buildCommand();
        $this->assertStringContainsString('--export-png-use-dithering=true', $command);
    }

    #[Test]
    public function it_has_typed_constant(): void
    {
        $this->assertIsArray(InkscapeConverter::SUPPORTED_FORMATS);
        $this->assertContains('png', InkscapeConverter::SUPPORTED_FORMATS);
        $this->assertContains('pdf', InkscapeConverter::SUPPORTED_FORMATS);
        $this->assertContains('svg', InkscapeConverter::SUPPORTED_FORMATS);
    }
}
