<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResvgConverterTest extends TestCase
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
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        $formats = $converter->supportedFormats();

        $this->assertContains('png', $formats);
        $this->assertCount(1, $formats);
    }

    #[Test]
    public function it_throws_for_unsupported_format(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: pdf');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setFormat('pdf');
    }

    #[Test]
    public function it_accepts_png_format(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setFormat('png');

        $this->assertEmpty($converter->options);
    }

    #[Test]
    public function it_uses_resvg_option_names(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDimensions(800, 600, 150);

        $this->assertEquals(800, $converter->options['width']);
        $this->assertEquals(600, $converter->options['height']);
        $this->assertEquals(150, $converter->options['dpi']);
    }

    #[Test]
    public function it_uses_resvg_background_option_names(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackground('#ffffff');
        $converter->setBackgroundOpacity(0.5);

        $this->assertEquals('#ffffff', $converter->options['background']);
        $this->assertEquals(0.5, $converter->options['background-opacity']);
    }

    #[Test]
    public function it_sets_zoom(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setZoom(2.0);

        $this->assertEquals(2.0, $converter->options['zoom']);
    }

    #[Test]
    public function it_sets_shape_rendering(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setShapeRendering('crispEdges');

        $this->assertEquals('crispEdges', $converter->options['shape-rendering']);
    }

    #[Test]
    public function it_sets_text_rendering(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setTextRendering('optimizeLegibility');

        $this->assertEquals('optimizeLegibility', $converter->options['text-rendering']);
    }

    #[Test]
    public function it_sets_image_rendering(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setImageRendering('optimizeQuality');

        $this->assertEquals('optimizeQuality', $converter->options['image-rendering']);
    }

    #[Test]
    public function it_sets_default_font_family(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDefaultFontFamily('Arial');

        $this->assertEquals('Arial', $converter->options['font-family']);
    }

    #[Test]
    public function it_sets_default_font_size(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDefaultFontSize(16);

        $this->assertEquals(16, $converter->options['font-size']);
    }

    #[Test]
    public function it_sets_font_file(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->useFontFile('/path/to/font.ttf');

        $this->assertEquals('/path/to/font.ttf', $converter->options['use-font-file']);
    }

    #[Test]
    public function it_sets_fonts_dir(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->useFontsDir('/path/to/fonts');

        $this->assertEquals('/path/to/fonts', $converter->options['use-fonts-dir']);
    }

    #[Test]
    public function it_skips_system_fonts(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->skipSystemFonts();

        $this->assertArrayHasKey('skip-system-fonts', $converter->options);
        $this->assertNull($converter->options['skip-system-fonts']);
    }

    #[Test]
    public function it_sets_resources_dir(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setResourcesDir('/path/to/resources');

        $this->assertEquals('/path/to/resources', $converter->options['resources-dir']);
    }

    #[Test]
    public function it_builds_command_with_positional_output(): void
    {
        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setFormat('png');
        $converter->setWidth(800);

        // Simulate what applyExportOptions does for file output
        // We need to use reflection or test via the full flow
        $command = $converter->buildCommand();

        $this->assertStringContainsString("'/usr/bin/resvg'", $command);
        $this->assertStringContainsString('--width 800', $command);
        $this->assertStringContainsString(escapeshellarg($this->testSvg), $command);
    }

    #[Test]
    public function it_builds_command_with_space_separated_values(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->withOption('width', 500);
        $converter->withOption('height', 300);
        $converter->withOption('background', '#ffffff');

        $command = $converter->buildCommand();

        $this->assertStringContainsString('--width 500', $command);
        $this->assertStringContainsString('--height 300', $command);
        $this->assertStringContainsString("--background '#ffffff'", $command);
    }

    #[Test]
    public function it_has_typed_constant(): void
    {
        $this->assertIsArray(ResvgConverter::SUPPORTED_FORMATS);
        $this->assertContains('png', ResvgConverter::SUPPORTED_FORMATS);
        $this->assertNotContains('pdf', ResvgConverter::SUPPORTED_FORMATS);
    }

    #[Test]
    public function it_supports_fluent_chaining(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        $result = $converter
            ->setFormat('png')
            ->setWidth(1024)
            ->setHeight(768)
            ->setDpi(150)
            ->setZoom(2.0)
            ->setShapeRendering('crispEdges')
            ->setDefaultFontFamily('Arial')
            ->timeout(120);

        $this->assertInstanceOf(ResvgConverter::class, $result);
        $this->assertEquals(1024, $result->options['width']);
        $this->assertEquals(768, $result->options['height']);
        $this->assertEquals(150, $result->options['dpi']);
        $this->assertEquals(2.0, $result->options['zoom']);
        $this->assertEquals('crispEdges', $result->options['shape-rendering']);
        $this->assertEquals('Arial', $result->options['font-family']);
    }

    #[Test]
    public function it_returns_version_string(): void
    {
        Process::fake([
            '*' => Process::result(output: 'resvg 0.44.0', exitCode: 0),
        ]);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $version = $converter->version();

        $this->assertEquals('resvg 0.44.0', $version);
    }

    #[Test]
    public function it_throws_when_version_fails(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'command not found', exitCode: 127),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('command not found');

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->version();
    }

    #[Test]
    public function it_builds_command_with_boolean_false(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->withOption('some-option', false);

        $command = $converter->buildCommand();
        $this->assertStringContainsString('--some-option=false', $command);
    }

    #[Test]
    public function it_builds_command_with_single_char_flag(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->withFlag('c'); // Single char flag (like -c for stdout)

        $command = $converter->buildCommand();
        $this->assertStringContainsString('-c', $command);
        // Ensure flag is AFTER input path (Resvg requirement for -c)
        $inputPos = strpos($command, $this->testSvg);
        $flagPos = strpos($command, '-c');
        $this->assertGreaterThan($inputPos, $flagPos);
    }

    #[Test]
    public function it_builds_command_with_output_path(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        // Use reflection to set outputPath protected property
        $reflection = new \ReflectionProperty($converter, 'outputPath');
        $reflection->setValue($converter, '/tmp/output.png');

        $command = $converter->buildCommand();
        $this->assertStringContainsString("'/tmp/output.png'", $command);
    }

    #[Test]
    public function it_builds_command_with_default_non_scalar_value(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        // Use withOption public method instead of reflection
        $converter->withOption('array-option', ['not', 'scalar']);

        $command = $converter->buildCommand();
        $this->assertStringContainsString('--array-option', $command);
    }
}
