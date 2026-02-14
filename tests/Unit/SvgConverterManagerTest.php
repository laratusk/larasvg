<?php

namespace Laratusk\SvgConverter\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Laratusk\SvgConverter\Contracts\Provider;
use Laratusk\SvgConverter\Converters\InkscapeConverter;
use Laratusk\SvgConverter\Converters\ResvgConverter;
use Laratusk\SvgConverter\Exceptions\SvgConverterException;
use Laratusk\SvgConverter\SvgConverterManager;
use Laratusk\SvgConverter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SvgConverterManagerTest extends TestCase
{
    private SvgConverterManager $manager;

    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(SvgConverterManager::class);
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
    public function it_opens_a_local_file(): void
    {
        $converter = $this->manager->open($this->testSvg);

        $this->assertInstanceOf(Provider::class, $converter);
        $this->assertEquals($this->testSvg, $converter->inputPath);
    }

    #[Test]
    public function it_throws_when_opening_nonexistent_file(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('does not exist');

        $this->manager->open('/nonexistent/file.svg');
    }

    #[Test]
    public function it_defaults_to_resvg_provider(): void
    {
        $converter = $this->manager->open($this->testSvg);

        $this->assertInstanceOf(ResvgConverter::class, $converter);
    }

    #[Test]
    public function it_switches_to_inkscape_provider(): void
    {
        $converter = $this->manager->using('inkscape')->open($this->testSvg);

        $this->assertInstanceOf(InkscapeConverter::class, $converter);
    }

    #[Test]
    public function it_applies_config_values_to_resvg_instances(): void
    {
        $converter = $this->manager->open($this->testSvg);

        $this->assertEquals('/usr/local/bin/resvg', $converter->binary);
        $this->assertEquals(60, $converter->timeout);
    }

    #[Test]
    public function it_applies_config_values_to_inkscape_instances(): void
    {
        $converter = $this->manager->using('inkscape')->open($this->testSvg);

        $this->assertEquals('/usr/local/bin/inkscape', $converter->binary);
        $this->assertEquals(60, $converter->timeout);
    }

    #[Test]
    public function it_reads_config_values(): void
    {
        $this->assertEquals('/usr/local/bin/resvg', $this->manager->getBinary('resvg'));
        $this->assertEquals('/usr/local/bin/inkscape', $this->manager->getBinary('inkscape'));
        $this->assertEquals(60, $this->manager->getTimeout('resvg'));
        $this->assertEquals(60, $this->manager->getTimeout('inkscape'));
        $this->assertEquals('local', $this->manager->getDefaultDisk());
    }

    #[Test]
    public function it_opens_from_disk(): void
    {
        Storage::fake('test-disk');
        Storage::disk('test-disk')->put('test.svg', '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>');

        $converter = $this->manager->openFromDisk('test-disk', 'test.svg');

        $this->assertInstanceOf(Provider::class, $converter);
        $this->assertFileExists($converter->inputPath);
        $this->assertStringContainsString('svgconverter_disk_', $converter->inputPath);
    }

    #[Test]
    public function it_throws_when_disk_file_not_found(): void
    {
        Storage::fake('test-disk');

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('does not exist on disk');

        $this->manager->openFromDisk('test-disk', 'nonexistent.svg');
    }

    #[Test]
    public function it_opens_from_content(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle r="50"/></svg>';

        $converter = $this->manager->openFromContent($svg);

        $this->assertInstanceOf(Provider::class, $converter);
        $this->assertFileExists($converter->inputPath);
        $this->assertStringEndsWith('.svg', $converter->inputPath);
        $this->assertEquals($svg, file_get_contents($converter->inputPath));
    }

    #[Test]
    public function it_opens_from_content_with_custom_extension(): void
    {
        $content = '%PDF-1.4 fake content';

        $converter = $this->manager->using('inkscape')->openFromContent($content, 'pdf');

        $this->assertStringEndsWith('.pdf', $converter->inputPath);
    }

    #[Test]
    public function it_throws_for_unknown_provider(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unknown SVG converter provider');

        $this->manager->using('unknown')->open($this->testSvg);
    }

    #[Test]
    public function it_resets_provider_after_use(): void
    {
        $converter1 = $this->manager->using('inkscape')->open($this->testSvg);
        $this->assertInstanceOf(InkscapeConverter::class, $converter1);

        // Next call should use default (resvg)
        $converter2 = $this->manager->open($this->testSvg);
        $this->assertInstanceOf(ResvgConverter::class, $converter2);
    }

    #[Test]
    public function it_opens_from_disk_with_inkscape_provider(): void
    {
        Storage::fake('test-disk');
        Storage::disk('test-disk')->put('test.svg', '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>');

        $converter = $this->manager->using('inkscape')->openFromDisk('test-disk', 'test.svg');

        $this->assertInstanceOf(InkscapeConverter::class, $converter);
    }
}
