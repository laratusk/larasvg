<?php

namespace Laratusk\Larasvg\Tests\Unit;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Converters\CairosvgConverter;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\SvgConverterManager;
use Laratusk\Larasvg\Tests\TestCase;
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

        $expectedBinary = $this->app['config']->get('svg-converter.providers.resvg.binary');
        $this->assertEquals($expectedBinary, $converter->binary);
        $this->assertEquals(60, $converter->getTimeout());
    }

    #[Test]
    public function it_applies_config_values_to_inkscape_instances(): void
    {
        $converter = $this->manager->using('inkscape')->open($this->testSvg);

        $expectedBinary = $this->app['config']->get('svg-converter.providers.inkscape.binary');
        $this->assertEquals($expectedBinary, $converter->binary);
        $this->assertEquals(60, $converter->getTimeout());
    }

    #[Test]
    public function it_reads_config_values(): void
    {
        $expectedResvg = $this->app['config']->get('svg-converter.providers.resvg.binary');
        $expectedInkscape = $this->app['config']->get('svg-converter.providers.inkscape.binary');

        $this->assertEquals($expectedResvg, $this->manager->getBinary('resvg'));
        $this->assertEquals($expectedInkscape, $this->manager->getBinary('inkscape'));
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

    #[Test]
    public function it_returns_action_list_from_inkscape(): void
    {
        Process::fake([
            '*' => Process::result(output: 'action-list', exitCode: 0),
        ]);

        $result = $this->manager->using('inkscape')->actionList();
        $this->assertEquals('action-list', $result);
    }

    #[Test]
    public function it_throws_action_list_for_non_inkscape(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('actionList() is only available for the Inkscape provider.');

        // Default is resvg
        $this->manager->actionList();
    }

    // -------------------------------------------------------------------------
    // Config-Driven Driver Map
    // -------------------------------------------------------------------------

    #[Test]
    public function it_resolves_cairosvg_provider_from_config(): void
    {
        $converter = $this->manager->using('cairosvg')->open($this->testSvg);

        $this->assertInstanceOf(CairosvgConverter::class, $converter);
    }

    #[Test]
    public function it_resolves_all_built_in_drivers_from_config(): void
    {
        $this->assertInstanceOf(
            ResvgConverter::class,
            $this->manager->using('resvg')->open($this->testSvg),
        );
        $this->assertInstanceOf(
            InkscapeConverter::class,
            $this->manager->using('inkscape')->open($this->testSvg),
        );
        $this->assertInstanceOf(
            CairosvgConverter::class,
            $this->manager->using('cairosvg')->open($this->testSvg),
        );
    }

    #[Test]
    public function it_throws_for_driver_class_that_does_not_exist(): void
    {
        $this->app['config']->set('svg-converter.drivers.ghost-driver', 'App\\NonExistent\\Converter');

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Driver class [App\\NonExistent\\Converter] does not exist.');

        $this->manager->using('ghost-driver')->open($this->testSvg);
    }

    #[Test]
    public function it_throws_for_driver_class_that_does_not_implement_provider(): void
    {
        $this->app['config']->set('svg-converter.drivers.bad-driver', \stdClass::class);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('must implement');

        $this->manager->using('bad-driver')->open($this->testSvg);
    }

    // -------------------------------------------------------------------------
    // extend() — Programmatic Custom Driver Registration
    // -------------------------------------------------------------------------

    #[Test]
    public function it_registers_and_resolves_a_custom_driver_via_extend(): void
    {
        $this->manager->extend('custom-resvg', ResvgConverter::class);

        $converter = $this->manager->using('custom-resvg')->open($this->testSvg);

        $this->assertInstanceOf(ResvgConverter::class, $converter);
    }

    #[Test]
    public function it_custom_driver_takes_precedence_over_config(): void
    {
        // Register a custom mapping that overrides 'resvg' with InkscapeConverter
        $this->manager->extend('resvg', InkscapeConverter::class);

        $converter = $this->manager->open($this->testSvg); // default = resvg

        // Custom registration wins over config
        $this->assertInstanceOf(InkscapeConverter::class, $converter);
    }

    #[Test]
    public function it_returns_static_from_extend_for_fluent_chaining(): void
    {
        $result = $this->manager->extend('my-driver', ResvgConverter::class);

        $this->assertSame($this->manager, $result);
    }

    #[Test]
    public function it_resolves_custom_driver_with_correct_binary_and_timeout(): void
    {
        $this->app['config']->set('svg-converter.providers.my-tool.binary', '/usr/local/bin/my-tool');
        $this->app['config']->set('svg-converter.providers.my-tool.timeout', 120);

        $this->manager->extend('my-tool', ResvgConverter::class);
        $converter = $this->manager->using('my-tool')->open($this->testSvg);

        $this->assertEquals('/usr/local/bin/my-tool', $converter->binary);
        $this->assertEquals(120, $converter->getTimeout());
    }
}
