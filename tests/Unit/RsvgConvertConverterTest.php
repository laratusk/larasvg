<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\RsvgConvertConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RsvgConvertConverterTest extends TestCase
{
    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSvg = $this->createTempSvg();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testSvg)) {
            @unlink($this->testSvg);
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Supported Formats
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_supported_formats(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);

        $formats = $converter->supportedFormats();

        $this->assertContains('png', $formats);
        $this->assertContains('pdf', $formats);
        $this->assertContains('ps', $formats);
        $this->assertContains('eps', $formats);
        $this->assertContains('svg', $formats);
        $this->assertCount(5, $formats);
    }

    #[Test]
    public function it_has_typed_constant(): void
    {
        $this->assertIsArray(RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertContains('png', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertContains('pdf', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertContains('ps', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertContains('eps', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertContains('svg', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('emf', RsvgConvertConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('wmf', RsvgConvertConverter::SUPPORTED_FORMATS);
    }

    // -------------------------------------------------------------------------
    // Format Validation
    // -------------------------------------------------------------------------

    #[Test]
    public function it_accepts_png_format(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('png');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_pdf_format(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('pdf');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_ps_format(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('ps');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_eps_format(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('eps');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_svg_format(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('svg');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_throws_for_unsupported_format_emf(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: emf');

        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('emf');
    }

    #[Test]
    public function it_throws_for_unsupported_format_wmf(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: wmf');

        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('wmf');
    }

    // -------------------------------------------------------------------------
    // Dimensions & DPI
    // -------------------------------------------------------------------------

    #[Test]
    public function it_uses_rsvg_convert_option_names_for_dimensions(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setDimensions(800, 600, 150);

        $this->assertEquals(800, $converter->getOptions()['width']);
        $this->assertEquals(600, $converter->getOptions()['height']);
        $this->assertEquals(150, $converter->getOptions()['dpi-x']);
        $this->assertEquals(150, $converter->getOptions()['dpi-y']);
    }

    #[Test]
    public function it_sets_width(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setWidth(1024);

        $this->assertEquals(1024, $converter->getOptions()['width']);
    }

    #[Test]
    public function it_sets_height(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setHeight(768);

        $this->assertEquals(768, $converter->getOptions()['height']);
    }

    #[Test]
    public function it_sets_dpi_for_both_axes(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setDpi(300);

        $this->assertEquals(300, $converter->getOptions()['dpi-x']);
        $this->assertEquals(300, $converter->getOptions()['dpi-y']);
    }

    #[Test]
    public function it_ignores_null_dpi(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setDpi(null);

        $this->assertArrayNotHasKey('dpi-x', $converter->getOptions());
        $this->assertArrayNotHasKey('dpi-y', $converter->getOptions());
    }

    // -------------------------------------------------------------------------
    // Background
    // -------------------------------------------------------------------------

    #[Test]
    public function it_sets_background_hex_color(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('#ffffff');

        $this->assertEquals('#ffffff', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_sets_background_rgb_color(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('rgb(255,0,128)');

        $this->assertEquals('rgb(255,0,128)', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_sets_background_rgba_color_directly(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('rgba(255,0,128,0.5)');

        $this->assertEquals('rgba(255,0,128,0.5)', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_throws_for_invalid_background_color(): void
    {
        $this->expectException(SvgConverterException::class);

        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('not-a-color');
    }

    #[Test]
    public function it_combines_hex_background_with_opacity(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('#ffffff');
        $converter->setBackgroundOpacity(0.5);

        $this->assertEquals('rgba(255,255,255,0.5)', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_combines_rgb_background_with_opacity(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('rgb(255,0,128)');
        $converter->setBackgroundOpacity(0.75);

        $this->assertEquals('rgba(255,0,128,0.75)', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_combines_short_hex_background_with_opacity(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('#fff');
        $converter->setBackgroundOpacity(0.5);

        $this->assertEquals('rgba(255,255,255,0.5)', $converter->getOptions()['background-color']);
    }

    #[Test]
    public function it_throws_for_background_opacity_out_of_range(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Background opacity must be between 0.0 and 1.0.');

        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackgroundOpacity(1.5);
    }

    #[Test]
    public function it_applies_opacity_even_if_set_before_color(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackgroundOpacity(0.5);
        $converter->setBackground('#000000');

        $this->assertEquals('rgba(0,0,0,0.5)', $converter->getOptions()['background-color']);
    }

    // -------------------------------------------------------------------------
    // Provider-Specific Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_sets_zoom(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setZoom(2.5);

        $this->assertEquals(2.5, $converter->getOptions()['zoom']);
    }

    #[Test]
    public function it_sets_x_zoom(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setXZoom(2.0);

        $this->assertEquals(2.0, $converter->getOptions()['x-zoom']);
    }

    #[Test]
    public function it_sets_y_zoom(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setYZoom(1.5);

        $this->assertEquals(1.5, $converter->getOptions()['y-zoom']);
    }

    #[Test]
    public function it_sets_keep_aspect_ratio(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepAspectRatio();

        $this->assertArrayHasKey('keep-aspect-ratio', $converter->getOptions());
        $this->assertNull($converter->getOptions()['keep-aspect-ratio']);
    }

    #[Test]
    public function it_removes_keep_aspect_ratio_when_false(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepAspectRatio();
        $converter->keepAspectRatio(false);

        $this->assertArrayNotHasKey('keep-aspect-ratio', $converter->getOptions());
    }

    #[Test]
    public function it_sets_stylesheet(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setStylesheet('/path/to/style.css');

        $this->assertEquals('/path/to/style.css', $converter->getOptions()['stylesheet']);
    }

    #[Test]
    public function it_sets_unlimited(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->unlimited();

        $this->assertArrayHasKey('unlimited', $converter->getOptions());
        $this->assertNull($converter->getOptions()['unlimited']);
    }

    #[Test]
    public function it_removes_unlimited_when_false(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->unlimited();
        $converter->unlimited(false);

        $this->assertArrayNotHasKey('unlimited', $converter->getOptions());
    }

    #[Test]
    public function it_sets_page_width(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setPageWidth('8.5in');

        $this->assertEquals('8.5in', $converter->getOptions()['page-width']);
    }

    #[Test]
    public function it_sets_page_height(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setPageHeight('11in');

        $this->assertEquals('11in', $converter->getOptions()['page-height']);
    }

    #[Test]
    public function it_sets_top_margin(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setTopMargin('10mm');

        $this->assertEquals('10mm', $converter->getOptions()['top']);
    }

    #[Test]
    public function it_sets_left_margin(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setLeftMargin('15mm');

        $this->assertEquals('15mm', $converter->getOptions()['left']);
    }

    #[Test]
    public function it_adds_keep_image_data_flag(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepImageData(true);

        $this->assertArrayHasKey('keep-image-data', $converter->getOptions());
        $this->assertNull($converter->getOptions()['keep-image-data']);
        $this->assertArrayNotHasKey('no-keep-image-data', $converter->getOptions());
    }

    #[Test]
    public function it_adds_no_keep_image_data_flag(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepImageData(false);

        $this->assertArrayHasKey('no-keep-image-data', $converter->getOptions());
        $this->assertNull($converter->getOptions()['no-keep-image-data']);
        $this->assertArrayNotHasKey('keep-image-data', $converter->getOptions());
    }

    #[Test]
    public function it_swaps_keep_image_data_flags_correctly(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepImageData(false);
        $converter->keepImageData(true);

        $this->assertArrayHasKey('keep-image-data', $converter->getOptions());
        $this->assertArrayNotHasKey('no-keep-image-data', $converter->getOptions());
    }

    #[Test]
    public function it_sets_base_uri(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBaseUri('file:///path/to/assets/');

        $this->assertEquals('file:///path/to/assets/', $converter->getOptions()['base-uri']);
    }

    // -------------------------------------------------------------------------
    // Command Building
    // -------------------------------------------------------------------------

    #[Test]
    public function it_builds_basic_command(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setWidth(800);

        $command = $converter->buildCommand();

        $this->assertStringContainsString("'/usr/bin/rsvg-convert'", $command);
        $this->assertStringContainsString('--width=800', $command);
        $this->assertStringContainsString(escapeshellarg($this->testSvg), $command);
    }

    #[Test]
    public function it_builds_command_with_format_and_output(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setFormat('png');
        $converter->setWidth(1024);
        $converter->setHeight(768);

        // Use reflection to set outputPath (normally set via applyExportOptions)
        $reflection = new \ReflectionProperty($converter, 'outputPath');
        $reflection->setValue($converter, '/tmp/output.png');

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--width=1024', $command);
        $this->assertStringContainsString('--height=768', $command);
        $this->assertStringContainsString(escapeshellarg($this->testSvg), $command);
        $this->assertStringContainsString("-o '/tmp/output.png'", $command);
    }

    #[Test]
    public function it_builds_command_with_null_flag(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->keepAspectRatio();

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--keep-aspect-ratio', $command);
    }

    #[Test]
    public function it_builds_command_with_string_value(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->setBackground('#ffffff');

        $command = $converter->buildCommand();

        $this->assertStringContainsString("--background-color='#ffffff'", $command);
    }

    #[Test]
    public function it_builds_command_with_boolean_false_value(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->withOption('some-flag', false);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--some-flag=false', $command);
    }

    #[Test]
    public function it_builds_command_with_non_scalar_value(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);
        $converter->withOption('array-option', ['not', 'scalar']);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--array-option', $command);
    }

    #[Test]
    public function it_builds_command_without_output_for_stdout(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);

        // No outputPath set means stdout mode
        $command = $converter->buildCommand();

        $this->assertStringNotContainsString(' -o ', $command);
    }

    #[Test]
    public function it_places_input_before_output_in_command(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);

        $reflection = new \ReflectionProperty($converter, 'outputPath');
        $reflection->setValue($converter, '/tmp/output.png');

        $command = $converter->buildCommand();

        $inputPos = strpos($command, escapeshellarg($this->testSvg));
        $outputPos = strpos($command, '-o ');

        $this->assertGreaterThan($inputPos, $outputPos);
    }

    // -------------------------------------------------------------------------
    // Version
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_version_string(): void
    {
        Process::fake([
            '*' => Process::result(output: 'rsvg-convert version 2.56.3', exitCode: 0),
        ]);

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $version = $converter->version();

        $this->assertEquals('rsvg-convert version 2.56.3', $version);
    }

    #[Test]
    public function it_throws_when_version_fails(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'command not found', exitCode: 127),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('command not found');

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->version();
    }

    // -------------------------------------------------------------------------
    // Fluent Chaining
    // -------------------------------------------------------------------------

    #[Test]
    public function it_supports_fluent_chaining(): void
    {
        $converter = new RsvgConvertConverter($this->testSvg, 'rsvg-convert', 60);

        $result = $converter
            ->setFormat('pdf')
            ->setWidth(1024)
            ->setHeight(768)
            ->setDpi(150)
            ->setZoom(2.0)
            ->keepAspectRatio()
            ->setBackground('#ffffff')
            ->setBackgroundOpacity(0.8)
            ->setPageWidth('8.5in')
            ->setPageHeight('11in')
            ->timeout(120);

        $this->assertInstanceOf(RsvgConvertConverter::class, $result);
        $this->assertEquals(1024, $result->getOptions()['width']);
        $this->assertEquals(768, $result->getOptions()['height']);
        $this->assertEquals(150, $result->getOptions()['dpi-x']);
        $this->assertEquals(150, $result->getOptions()['dpi-y']);
        $this->assertEquals(2.0, $result->getOptions()['zoom']);
        $this->assertArrayHasKey('keep-aspect-ratio', $result->getOptions());
        $this->assertEquals('rgba(255,255,255,0.8)', $result->getOptions()['background-color']);
        $this->assertEquals('8.5in', $result->getOptions()['page-width']);
        $this->assertEquals('11in', $result->getOptions()['page-height']);
    }
}
