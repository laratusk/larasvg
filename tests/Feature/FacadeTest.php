<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\SvgConverterManager;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FacadeTest extends TestCase
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
    public function it_resolves_the_facade(): void
    {
        $resolved = SvgConverter::getFacadeRoot();

        $this->assertInstanceOf(SvgConverterManager::class, $resolved);
    }

    #[Test]
    public function it_opens_file_via_facade(): void
    {
        $converter = SvgConverter::open($this->testSvg);

        $this->assertInstanceOf(Provider::class, $converter);
    }

    #[Test]
    public function it_defaults_to_resvg_via_facade(): void
    {
        $converter = SvgConverter::open($this->testSvg);

        $this->assertInstanceOf(ResvgConverter::class, $converter);
    }

    #[Test]
    public function it_switches_to_inkscape_via_facade(): void
    {
        $converter = SvgConverter::using('inkscape')->open($this->testSvg);

        $this->assertInstanceOf(InkscapeConverter::class, $converter);
    }

    #[Test]
    public function it_opens_from_content_via_facade(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>';

        $converter = SvgConverter::openFromContent($svg);

        $this->assertInstanceOf(Provider::class, $converter);
    }

    #[Test]
    public function it_gets_version_via_facade(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'resvg 0.44.0',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $version = SvgConverter::version();

        $this->assertEquals('resvg 0.44.0', $version);
    }

    #[Test]
    public function it_gets_inkscape_version_via_facade(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'Inkscape 1.4 (e7c3feb100, 2024-10-09)',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $version = SvgConverter::version('inkscape');

        $this->assertEquals('Inkscape 1.4 (e7c3feb100, 2024-10-09)', $version);
    }

    #[Test]
    public function it_gets_binary_via_facade(): void
    {
        $binary = SvgConverter::getBinary('resvg');

        $this->assertEquals('/usr/local/bin/resvg', $binary);
    }

    #[Test]
    public function it_gets_timeout_via_facade(): void
    {
        $timeout = SvgConverter::getTimeout('resvg');

        $this->assertEquals(60, $timeout);
    }

    #[Test]
    public function it_converts_via_facade_with_resvg(): void
    {
        Process::fake();

        $result = SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(512, 512)
            ->convert();

        $this->assertStringEndsWith('.png', $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--width 512')
            && str_contains((string) $process->command, '--height 512'));
    }

    #[Test]
    public function it_converts_via_facade_with_inkscape(): void
    {
        Process::fake();

        $result = SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(512, 512)
            ->convert();

        $this->assertStringEndsWith('.png', $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-width=512')
            && str_contains((string) $process->command, '--export-height=512'));
    }

    #[Test]
    public function it_is_a_singleton(): void
    {
        $first = SvgConverter::getFacadeRoot();
        $second = SvgConverter::getFacadeRoot();

        $this->assertSame($first, $second);
    }
}
