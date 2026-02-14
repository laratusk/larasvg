<?php

namespace Laratusk\SvgConverter\Tests\Unit;

use Illuminate\Support\Facades\Process;
use Laratusk\SvgConverter\Converters\InkscapeConverter;
use Laratusk\SvgConverter\Converters\ResvgConverter;
use Laratusk\SvgConverter\Exceptions\SvgConverterException;
use Laratusk\SvgConverter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AbstractConverterTest extends TestCase
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
    public function it_creates_instance_with_correct_defaults(): void
    {
        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);

        $this->assertEquals($this->testSvg, $converter->inputPath);
        $this->assertEquals('/usr/bin/resvg', $converter->binary);
        $this->assertEquals(60, $converter->timeout);
        $this->assertEmpty($converter->options);
    }

    #[Test]
    public function it_sets_dimensions(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDimensions(800, 600, 150);

        $this->assertEquals(800, $converter->options['width']);
        $this->assertEquals(600, $converter->options['height']);
        $this->assertEquals(150, $converter->options['dpi']);
    }

    #[Test]
    public function it_sets_width_only(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setWidth(1920);

        $this->assertEquals(1920, $converter->options['width']);
    }

    #[Test]
    public function it_sets_height_only(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setHeight(1080);

        $this->assertEquals(1080, $converter->options['height']);
    }

    #[Test]
    public function it_sets_dpi(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDpi(300);

        $this->assertEquals(300, $converter->options['dpi']);
    }

    #[Test]
    public function it_ignores_null_dpi(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setDpi(null);

        $this->assertArrayNotHasKey('dpi', $converter->options);
    }

    #[Test]
    public function it_sets_background_with_hex_color(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackground('#ff007f');

        $this->assertEquals('#ff007f', $converter->options['background']);
    }

    #[Test]
    public function it_sets_background_with_rgb_color(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackground('rgb(255,0,128)');

        $this->assertEquals('rgb(255,0,128)', $converter->options['background']);
    }

    #[Test]
    public function it_throws_for_invalid_color(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Supported color formats');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackground('not-a-color');
    }

    #[Test]
    public function it_sets_background_opacity(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackgroundOpacity(0.5);

        $this->assertEquals(0.5, $converter->options['background-opacity']);
    }

    #[Test]
    public function it_throws_for_invalid_opacity_too_high(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Background opacity');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackgroundOpacity(1.5);
    }

    #[Test]
    public function it_throws_for_invalid_opacity_negative(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Background opacity');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setBackgroundOpacity(-0.1);
    }

    #[Test]
    public function it_allows_dynamic_options(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        $converter->withOption('custom-option', 'custom-value');
        $converter->withFlag('custom-flag');

        $this->assertEquals('custom-value', $converter->options['custom-option']);
        $this->assertNull($converter->options['custom-flag']);
    }

    #[Test]
    public function it_allows_bulk_options(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        $converter->withOptions([
            'width' => 500,
            'height' => 300,
            'skip-system-fonts',
        ]);

        $this->assertEquals(500, $converter->options['width']);
        $this->assertEquals(300, $converter->options['height']);
        $this->assertNull($converter->options['skip-system-fonts']);
    }

    #[Test]
    public function it_changes_timeout(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->timeout(120);

        $this->assertEquals(120, $converter->timeout);
    }

    #[Test]
    public function it_supports_fluent_chaining(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        $result = $converter
            ->setFormat('png')
            ->setDimensions(1024, 1024, 96)
            ->setBackground('#ffffff')
            ->setBackgroundOpacity(1.0)
            ->timeout(120);

        $this->assertInstanceOf(ResvgConverter::class, $result);
        $this->assertEquals(1024, $result->options['width']);
        $this->assertEquals(1024, $result->options['height']);
        $this->assertEquals(96, $result->options['dpi']);
        $this->assertEquals('#ffffff', $result->options['background']);
        $this->assertEquals(1.0, $result->options['background-opacity']);
    }

    #[Test]
    public function it_creates_and_tracks_temp_files(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $tempPath = $converter->createTempFile('test.svg');

        $this->assertStringContainsString('svgconverter_', $tempPath);
        $this->assertStringEndsWith('test.svg', $tempPath);
    }

    #[Test]
    public function it_cleans_up_temp_files(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $tempPath = $converter->createTempFile('cleanup_test.txt');
        file_put_contents($tempPath, 'test');

        $this->assertFileExists($tempPath);

        $converter->cleanup();

        $this->assertFileDoesNotExist($tempPath);
    }

    #[Test]
    public function it_validates_hex_colors_with_and_without_hash(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);

        // With hash
        $converter->setBackground('#ff0000');
        $this->assertEquals('#ff0000', $converter->options['background']);

        // Short hex
        $converter2 = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter2->setBackground('#fff');
        $this->assertEquals('#fff', $converter2->options['background']);

        // Without hash
        $converter3 = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter3->setBackground('ff0000');
        $this->assertEquals('ff0000', $converter3->options['background']);
    }

    #[Test]
    public function it_throws_when_no_format_and_no_extension(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('No export format specified');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        Process::fake();
        $converter->convert('output_no_extension');
    }

    #[Test]
    public function it_has_public_readonly_properties(): void
    {
        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 120);

        $this->assertSame($this->testSvg, $converter->inputPath);
        $this->assertSame('/usr/bin/resvg', $converter->binary);
        $this->assertSame(120, $converter->timeout);
        $this->assertIsArray($converter->options);
    }

    #[Test]
    public function it_throws_for_unsupported_format_on_resvg(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: pdf');

        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setFormat('pdf');
    }

    #[Test]
    public function inkscape_accepts_all_supported_formats(): void
    {
        foreach (InkscapeConverter::SUPPORTED_FORMATS as $format) {
            $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
            $converter->setFormat($format);
            $this->assertTrue(true, "Format {$format} should be accepted");
        }
    }

    #[Test]
    public function inkscape_uses_export_width_option_name(): void
    {
        $converter = new InkscapeConverter($this->testSvg, 'inkscape', 60);
        $converter->setWidth(800);

        $this->assertEquals(800, $converter->options['export-width']);
    }

    #[Test]
    public function resvg_uses_width_option_name(): void
    {
        $converter = new ResvgConverter($this->testSvg, 'resvg', 60);
        $converter->setWidth(800);

        $this->assertEquals(800, $converter->options['width']);
    }
}
