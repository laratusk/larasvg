<?php

namespace Laratusk\SvgConverter\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\SvgConverter\Converters\ResvgConverter;
use Laratusk\SvgConverter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResvgConversionTest extends TestCase
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
    public function it_converts_svg_to_png(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $result = $converter->setFormat('png')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.png';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "'resvg'") || str_contains((string) $process->command, 'resvg'));
    }

    #[Test]
    public function it_converts_with_dimensions(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setDimensions(800, 600, 150)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--width 800')
            && str_contains((string) $process->command, '--height 600')
            && str_contains((string) $process->command, '--dpi 150'));
    }

    #[Test]
    public function it_converts_with_background(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setBackground('#ffffff')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--background'));
    }

    #[Test]
    public function it_converts_with_zoom(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setZoom(2.0)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--zoom 2'));
    }

    #[Test]
    public function it_converts_with_shape_rendering(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setShapeRendering('crispEdges')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--shape-rendering 'crispEdges'"));
    }

    #[Test]
    public function it_converts_with_font_options(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setDefaultFontFamily('Arial')
            ->setDefaultFontSize(16)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--font-family 'Arial'")
            && str_contains((string) $process->command, '--font-size 16'));
    }

    #[Test]
    public function it_converts_with_skip_system_fonts(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->skipSystemFonts()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--skip-system-fonts'));
    }

    #[Test]
    public function it_converts_with_custom_timeout(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->timeout(300)
            ->convert();

        Process::assertRan(fn ($process): bool => $process->timeout === 300);
    }

    #[Test]
    public function it_uses_to_file_method(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/resvg_test_output.png';

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $result = $converter->toFile($outputPath);

        $this->assertEquals($outputPath, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, escapeshellarg($outputPath)));
    }

    #[Test]
    public function it_converts_with_resources_dir(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter
            ->setFormat('png')
            ->setResourcesDir('/path/to/resources')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--resources-dir '/path/to/resources'"));
    }

    #[Test]
    public function it_build_command_with_output_as_positional_arg(): void
    {
        Process::fake();

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $outputPath = sys_get_temp_dir().'/test_output.png';
        $converter->toFile($outputPath);

        Process::assertRan(function ($process) use ($outputPath): bool {
            $inputEscaped = escapeshellarg($this->testSvg);
            $outputEscaped = escapeshellarg($outputPath);

            // Input and output should both be at the end as positional args
            return str_contains((string) $process->command, $inputEscaped)
                && str_contains((string) $process->command, $outputEscaped);
        });
    }
}
