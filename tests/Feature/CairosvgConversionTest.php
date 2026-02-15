<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\CairosvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CairosvgConversionTest extends TestCase
{
    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSvg = $this->createTempSvg();
        $this->app['config']->set('svg-converter.providers.cairosvg.binary', 'cairosvg');
        $this->app['config']->set('svg-converter.providers.cairosvg.timeout', 60);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testSvg)) {
            @unlink($this->testSvg);
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Provider Switching
    // -------------------------------------------------------------------------

    #[Test]
    public function it_switches_to_cairosvg_provider_via_facade(): void
    {
        Process::fake();

        SvgConverter::using('cairosvg')
            ->open($this->testSvg)
            ->setFormat('png')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, 'cairosvg'));
    }

    // -------------------------------------------------------------------------
    // SVG → PNG
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_png(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $result = $converter->setFormat('png')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.png';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, 'cairosvg'));
    }

    #[Test]
    public function it_converts_with_output_dimensions(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setDimensions(800, 600, 150)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--output-width 800')
            && str_contains((string) $process->command, '--output-height 600')
            && str_contains((string) $process->command, '-d 150'));
    }

    // -------------------------------------------------------------------------
    // SVG → PDF
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_pdf(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $result = $converter->setFormat('pdf')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.pdf';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "-f 'pdf'"));
    }

    // -------------------------------------------------------------------------
    // SVG → PS / SVG
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_ps(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->setFormat('ps')->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "-f 'ps'"));
    }

    #[Test]
    public function it_converts_svg_to_svg(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->setFormat('svg')->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "-f 'svg'"));
    }

    // -------------------------------------------------------------------------
    // Scale
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_scale(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setScale(2.0)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-s 2'));
    }

    // -------------------------------------------------------------------------
    // Container Dimensions
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_container_dimensions(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setContainerDimensions(1920, 1080)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-W 1920')
            && str_contains((string) $process->command, '-H 1080'));
    }

    #[Test]
    public function it_converts_with_container_width_and_height_separately(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setContainerWidth(1280)
            ->setContainerHeight(720)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-W 1280')
            && str_contains((string) $process->command, '-H 720'));
    }

    // -------------------------------------------------------------------------
    // Unsafe flag
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_unsafe_flag(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->unsafe()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-u'));
    }

    // -------------------------------------------------------------------------
    // DPI
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_dpi(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setDpi(300)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-d 300'));
    }

    // -------------------------------------------------------------------------
    // Output Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_uses_to_file_method(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/cairosvg_test_output.png';

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $result = $converter->toFile($outputPath);

        $this->assertEquals($outputPath, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '-o '.escapeshellarg($outputPath)));
    }

    #[Test]
    public function it_outputs_to_stdout(): void
    {
        Process::fake([
            '*' => Process::result(output: 'binary-png-data', exitCode: 0),
        ]);

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $output = $converter->toStdout('png');

        $this->assertStringContainsString('binary-png-data', $output);

        // No -o flag should be present for stdout
        Process::assertRan(fn ($process): bool => ! str_contains((string) $process->command, ' -o '));
    }

    #[Test]
    public function it_infers_format_from_output_extension(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/cairosvg_test_output.pdf';

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->toFile($outputPath);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "-f 'pdf'"));
    }

    // -------------------------------------------------------------------------
    // Timeout
    // -------------------------------------------------------------------------

    #[Test]
    public function it_respects_custom_timeout(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->timeout(300)
            ->convert();

        Process::assertRan(fn ($process): bool => $process->timeout === 300);
    }

    // -------------------------------------------------------------------------
    // Error Handling
    // -------------------------------------------------------------------------

    #[Test]
    public function it_throws_svg_converter_exception_on_failure(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'conversion failed', exitCode: 1),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('conversion failed');

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->setFormat('png')->convert();
    }

    #[Test]
    public function it_throws_for_missing_format_when_no_extension(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('No export format specified');

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->convert('output-without-extension');
    }

    // -------------------------------------------------------------------------
    // Command Structure
    // -------------------------------------------------------------------------

    #[Test]
    public function it_places_input_file_after_binary_and_before_options(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/cairosvg_test.png';

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter->setFormat('png')->setWidth(800)->toFile($outputPath);

        Process::assertRan(function ($process): bool {
            $cmd = (string) $process->command;
            $binaryPos = strpos($cmd, "'/usr/local/bin/cairosvg'");
            $inputPos = strpos($cmd, escapeshellarg($this->testSvg));
            $widthPos = strpos($cmd, '--output-width 800');
            $outputPos = strpos($cmd, '-o ');

            return $binaryPos !== false
                && $inputPos !== false
                && $widthPos !== false
                && $outputPos !== false
                && $binaryPos < $inputPos
                && $inputPos < $widthPos
                && $inputPos < $outputPos;
        });
    }

    // -------------------------------------------------------------------------
    // setOutputWidth / setOutputHeight explicit aliases
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_explicit_output_width_and_height(): void
    {
        Process::fake();

        $converter = new CairosvgConverter($this->testSvg, '/usr/local/bin/cairosvg', 60);
        $converter
            ->setFormat('png')
            ->setOutputWidth(512)
            ->setOutputHeight(512)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--output-width 512')
            && str_contains((string) $process->command, '--output-height 512'));
    }
}
