<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\RsvgConvertConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RsvgConvertConversionTest extends TestCase
{
    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSvg = $this->createTempSvg();
        $this->app['config']->set('svg-converter.providers.rsvg-convert.binary', 'rsvg-convert');
        $this->app['config']->set('svg-converter.providers.rsvg-convert.timeout', 60);
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
    public function it_switches_to_rsvg_convert_provider_via_facade(): void
    {
        Process::fake();

        SvgConverter::using('rsvg-convert')
            ->open($this->testSvg)
            ->setFormat('png')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, 'rsvg-convert'));
    }

    // -------------------------------------------------------------------------
    // SVG → PNG
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_png(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $result = $converter->setFormat('png')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.png';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, 'rsvg-convert'));
    }

    #[Test]
    public function it_converts_with_dimensions(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setDimensions(800, 600, 150)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--width=800')
            && str_contains((string) $process->command, '--height=600')
            && str_contains((string) $process->command, '--dpi-x=150')
            && str_contains((string) $process->command, '--dpi-y=150'));
    }

    #[Test]
    public function it_converts_with_background_color(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setBackground('#ffffff')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--background-color='#ffffff'"));
    }

    #[Test]
    public function it_converts_with_background_and_opacity(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setBackground('#ffffff')
            ->setBackgroundOpacity(0.5)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--background-color='rgba(255,255,255,0.5)'"));
    }

    // -------------------------------------------------------------------------
    // SVG → PDF
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_pdf(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $result = $converter->setFormat('pdf')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.pdf';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--format='pdf'"));
    }

    #[Test]
    public function it_converts_to_pdf_with_page_dimensions(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('pdf')
            ->setPageWidth('210mm')
            ->setPageHeight('297mm')
            ->keepAspectRatio()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--page-width='210mm'")
            && str_contains((string) $process->command, "--page-height='297mm'")
            && str_contains((string) $process->command, '--keep-aspect-ratio'));
    }

    // -------------------------------------------------------------------------
    // SVG → PS / EPS / SVG
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_ps(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setFormat('ps')->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--format='ps'"));
    }

    #[Test]
    public function it_converts_svg_to_eps(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setFormat('eps')->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--format='eps'"));
    }

    #[Test]
    public function it_converts_svg_to_svg(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setFormat('svg')->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--format='svg'"));
    }

    // -------------------------------------------------------------------------
    // Zoom Options
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_zoom(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setZoom(2.0)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--zoom=2'));
    }

    #[Test]
    public function it_converts_with_x_and_y_zoom(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setXZoom(2.0)
            ->setYZoom(1.5)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--x-zoom=2')
            && str_contains((string) $process->command, '--y-zoom=1.5'));
    }

    // -------------------------------------------------------------------------
    // Stylesheet & Unlimited
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_stylesheet(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setStylesheet('/path/to/style.css')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--stylesheet='/path/to/style.css'"));
    }

    #[Test]
    public function it_converts_with_unlimited_flag(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->unlimited()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--unlimited'));
    }

    // -------------------------------------------------------------------------
    // Keep Image Data
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_keep_image_data(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('pdf')
            ->keepImageData(true)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--keep-image-data'));
    }

    #[Test]
    public function it_converts_with_no_keep_image_data(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('pdf')
            ->keepImageData(false)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--no-keep-image-data'));
    }

    // -------------------------------------------------------------------------
    // Base URI & Margins
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_base_uri(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('png')
            ->setBaseUri('file:///path/to/assets/')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--base-uri='file:///path/to/assets/'"));
    }

    #[Test]
    public function it_converts_with_margins(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter
            ->setFormat('pdf')
            ->setTopMargin('10mm')
            ->setLeftMargin('15mm')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--top='10mm'")
            && str_contains((string) $process->command, "--left='15mm'"));
    }

    // -------------------------------------------------------------------------
    // File Output Methods
    // -------------------------------------------------------------------------

    #[Test]
    public function it_uses_to_file_method(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/rsvg_convert_test_output.png';

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
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

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $output = $converter->toStdout('png');

        $this->assertStringContainsString('binary-png-data', $output);

        // No -o flag should be present for stdout
        Process::assertRan(fn ($process): bool => ! str_contains((string) $process->command, ' -o '));
    }

    #[Test]
    public function it_infers_format_from_output_extension(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/rsvg_test_output.pdf';

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->toFile($outputPath);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--format='pdf'"));
    }

    // -------------------------------------------------------------------------
    // Timeout
    // -------------------------------------------------------------------------

    #[Test]
    public function it_respects_custom_timeout(): void
    {
        Process::fake();

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
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

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setFormat('png')->convert();
    }

    #[Test]
    public function it_throws_for_missing_format_when_no_extension(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('No export format specified');

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->convert('output-without-extension');
    }

    // -------------------------------------------------------------------------
    // Command Structure
    // -------------------------------------------------------------------------

    #[Test]
    public function it_places_input_file_after_options(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/rsvg_test.png';

        $converter = new RsvgConvertConverter($this->testSvg, '/usr/bin/rsvg-convert', 60);
        $converter->setFormat('png')->setWidth(800)->toFile($outputPath);

        Process::assertRan(function ($process): bool {
            $cmd = (string) $process->command;
            $widthPos = strpos($cmd, '--width=800');
            $inputPos = strpos($cmd, escapeshellarg($this->testSvg));
            $outputPos = strpos($cmd, '-o ');

            return $widthPos !== false
                && $inputPos !== false
                && $outputPos !== false
                && $widthPos < $inputPos
                && $inputPos < $outputPos;
        });
    }
}
