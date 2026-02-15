<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\CairosvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CairosvgConverterTest extends TestCase
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
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);

        $formats = $converter->supportedFormats();

        $this->assertContains('png', $formats);
        $this->assertContains('pdf', $formats);
        $this->assertContains('ps', $formats);
        $this->assertContains('svg', $formats);
        $this->assertCount(4, $formats);
    }

    #[Test]
    public function it_has_typed_constant(): void
    {
        $this->assertIsArray(CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertContains('png', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertContains('pdf', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertContains('ps', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertContains('svg', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('eps', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('emf', CairosvgConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('wmf', CairosvgConverter::SUPPORTED_FORMATS);
    }

    // -------------------------------------------------------------------------
    // Format Validation
    // -------------------------------------------------------------------------

    #[Test]
    public function it_accepts_png_format(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('png');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_pdf_format(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('pdf');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_ps_format(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('ps');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_accepts_svg_format(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('svg');

        $this->assertEmpty($converter->getOptions());
    }

    #[Test]
    public function it_throws_for_unsupported_format_eps(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: eps');

        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('eps');
    }

    #[Test]
    public function it_throws_for_unsupported_format_emf(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: emf');

        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('emf');
    }

    #[Test]
    public function it_throws_for_unsupported_format_wmf(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: wmf');

        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('wmf');
    }

    // -------------------------------------------------------------------------
    // Dimensions & DPI
    // -------------------------------------------------------------------------

    #[Test]
    public function it_maps_set_width_to_output_width_option(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setWidth(800);

        $this->assertEquals(800, $converter->getOptions()['output-width']);
    }

    #[Test]
    public function it_maps_set_height_to_output_height_option(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setHeight(600);

        $this->assertEquals(600, $converter->getOptions()['output-height']);
    }

    #[Test]
    public function it_maps_set_dpi_to_d_option(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setDpi(150);

        $this->assertEquals(150, $converter->getOptions()['d']);
    }

    #[Test]
    public function it_ignores_null_dpi(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setDpi(null);

        $this->assertArrayNotHasKey('d', $converter->getOptions());
    }

    #[Test]
    public function it_sets_dimensions_via_set_dimensions(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setDimensions(1024, 768, 96);

        $this->assertEquals(1024, $converter->getOptions()['output-width']);
        $this->assertEquals(768, $converter->getOptions()['output-height']);
        $this->assertEquals(96, $converter->getOptions()['d']);
    }

    // -------------------------------------------------------------------------
    // Background (unsupported)
    // -------------------------------------------------------------------------

    #[Test]
    public function it_throws_when_set_background_is_called(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('CairoSVG does not support background color via CLI');

        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setBackground('#ffffff');
    }

    #[Test]
    public function it_throws_when_set_background_opacity_is_called(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('CairoSVG does not support background opacity via CLI');

        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setBackgroundOpacity(0.5);
    }

    // -------------------------------------------------------------------------
    // CairoSVG-Specific Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_sets_scale(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setScale(2.0);

        $this->assertEquals(2.0, $converter->getOptions()['s']);
    }

    #[Test]
    public function it_sets_container_width(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setContainerWidth(1920);

        $this->assertEquals(1920, $converter->getOptions()['W']);
    }

    #[Test]
    public function it_sets_container_height(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setContainerHeight(1080);

        $this->assertEquals(1080, $converter->getOptions()['H']);
    }

    #[Test]
    public function it_sets_container_dimensions(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setContainerDimensions(1920, 1080);

        $this->assertEquals(1920, $converter->getOptions()['W']);
        $this->assertEquals(1080, $converter->getOptions()['H']);
    }

    #[Test]
    public function it_sets_output_width(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setOutputWidth(800);

        $this->assertEquals(800, $converter->getOptions()['output-width']);
    }

    #[Test]
    public function it_sets_output_height(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setOutputHeight(600);

        $this->assertEquals(600, $converter->getOptions()['output-height']);
    }

    #[Test]
    public function it_sets_unsafe_flag(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->unsafe();

        $this->assertArrayHasKey('u', $converter->getOptions());
        $this->assertNull($converter->getOptions()['u']);
    }

    // -------------------------------------------------------------------------
    // Command Building
    // -------------------------------------------------------------------------

    #[Test]
    public function it_builds_basic_command_with_input_first(): void
    {
        $converter = new CairosvgConverter($this->testSvg, '/usr/bin/cairosvg', 60);

        $command = $converter->buildCommand();

        $this->assertStringContainsString("'/usr/bin/cairosvg'", $command);
        $this->assertStringContainsString(escapeshellarg($this->testSvg), $command);

        // Input must appear before any output flag
        $binaryPos = strpos($command, "'/usr/bin/cairosvg'");
        $inputPos = strpos($command, escapeshellarg($this->testSvg));
        $this->assertGreaterThan($binaryPos, $inputPos);
    }

    #[Test]
    public function it_builds_command_with_format_flag(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setFormat('png');

        // Simulate applyExportOptions
        $reflection = new \ReflectionProperty($converter, 'format');
        $reflection->setValue($converter, 'png');
        $converter->withOption('f', 'png');

        $command = $converter->buildCommand();

        $this->assertStringContainsString("-f 'png'", $command);
    }

    #[Test]
    public function it_builds_command_with_output_width(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setWidth(800);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--output-width 800', $command);
    }

    #[Test]
    public function it_builds_command_with_output_height(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setHeight(600);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--output-height 600', $command);
    }

    #[Test]
    public function it_builds_command_with_dpi(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setDpi(150);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('-d 150', $command);
    }

    #[Test]
    public function it_builds_command_with_scale(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setScale(2.5);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('-s 2.5', $command);
    }

    #[Test]
    public function it_builds_command_with_container_dimensions(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setContainerDimensions(1920, 1080);

        $command = $converter->buildCommand();

        $this->assertStringContainsString('-W 1920', $command);
        $this->assertStringContainsString('-H 1080', $command);
    }

    #[Test]
    public function it_builds_command_with_unsafe_flag(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->unsafe();

        $command = $converter->buildCommand();

        $this->assertStringContainsString('-u', $command);
    }

    #[Test]
    public function it_builds_command_with_output_file(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);

        $reflection = new \ReflectionProperty($converter, 'outputPath');
        $reflection->setValue($converter, '/tmp/output.png');

        $command = $converter->buildCommand();

        $this->assertStringContainsString("-o '/tmp/output.png'", $command);
    }

    #[Test]
    public function it_builds_command_without_output_for_stdout(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);

        $command = $converter->buildCommand();

        $this->assertStringNotContainsString(' -o ', $command);
    }

    #[Test]
    public function it_places_input_before_options_and_output(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);
        $converter->setWidth(800);

        $reflection = new \ReflectionProperty($converter, 'outputPath');
        $reflection->setValue($converter, '/tmp/output.png');

        $command = $converter->buildCommand();

        $inputPos = strpos($command, escapeshellarg($this->testSvg));
        $widthPos = strpos($command, '--output-width 800');
        $outputPos = strpos($command, '-o ');

        $this->assertLessThan($widthPos, $inputPos);
        $this->assertGreaterThan($inputPos, $outputPos);
    }

    // -------------------------------------------------------------------------
    // Version
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_version_string(): void
    {
        Process::fake([
            '*' => Process::result(output: 'CairoSVG 2.7.1', exitCode: 0),
        ]);

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $version = $converter->version();

        $this->assertEquals('CairoSVG 2.7.1', $version);
    }

    #[Test]
    public function it_throws_when_version_fails(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'command not found', exitCode: 127),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('command not found');

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->version();
    }

    // -------------------------------------------------------------------------
    // Fluent Chaining
    // -------------------------------------------------------------------------

    #[Test]
    public function it_supports_fluent_chaining(): void
    {
        $converter = new CairosvgConverter($this->testSvg, 'cairosvg', 60);

        $result = $converter
            ->setFormat('pdf')
            ->setWidth(1024)
            ->setHeight(768)
            ->setDpi(150)
            ->setScale(2.0)
            ->setContainerDimensions(1920, 1080)
            ->unsafe()
            ->timeout(120);

        $this->assertInstanceOf(CairosvgConverter::class, $result);
        $this->assertEquals(1024, $result->getOptions()['output-width']);
        $this->assertEquals(768, $result->getOptions()['output-height']);
        $this->assertEquals(150, $result->getOptions()['d']);
        $this->assertEquals(2.0, $result->getOptions()['s']);
        $this->assertEquals(1920, $result->getOptions()['W']);
        $this->assertEquals(1080, $result->getOptions()['H']);
        $this->assertArrayHasKey('u', $result->getOptions());
        $this->assertEquals(120, $result->getTimeout());
    }
}
