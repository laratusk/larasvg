<?php

namespace Laratusk\SvgConverter\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\SvgConverter\Converters\InkscapeConverter;
use Laratusk\SvgConverter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InkscapeConversionTest extends TestCase
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

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $result = $converter->setFormat('png')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.png';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-type=') && str_contains((string) $process->command, '--export-filename='));
    }

    #[Test]
    public function it_converts_svg_to_pdf(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $result = $converter->setFormat('pdf')->convert();

        $expectedOutput = dirname($this->testSvg).'/'.pathinfo($this->testSvg, PATHINFO_FILENAME).'.pdf';
        $this->assertEquals($expectedOutput, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "'pdf'") || str_contains((string) $process->command, 'pdf'));
    }

    #[Test]
    public function it_converts_with_custom_export_name(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $result = $converter->convert('custom_output.png');

        $this->assertStringEndsWith('custom_output.png', $result);
    }

    #[Test]
    public function it_converts_with_dimensions(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->setDimensions(800, 600, 150)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-width=800')
            && str_contains((string) $process->command, '--export-height=600')
            && str_contains((string) $process->command, '--export-dpi=150'));
    }

    #[Test]
    public function it_converts_with_background(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->setBackground('#ffffff')
            ->setBackgroundOpacity(1.0)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-background=')
            && str_contains((string) $process->command, '--export-background-opacity='));
    }

    #[Test]
    public function it_converts_with_text_to_path(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('eps')
            ->exportTextToPath()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-text-to-path'));
    }

    #[Test]
    public function it_converts_to_plain_svg(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('svg')
            ->exportPlainSvg()
            ->convert('output.svg');

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-plain-svg'));
    }

    #[Test]
    public function it_converts_with_export_area_drawing(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->exportAreaDrawing()
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-area-drawing'));
    }

    #[Test]
    public function it_converts_with_export_id(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->exportId('myObject', idOnly: true)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--export-id='myObject'")
            && str_contains((string) $process->command, '--export-id-only'));
    }

    #[Test]
    public function it_converts_with_pdf_version(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('pdf')
            ->exportPdfVersion('1.5')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-pdf-version=1.5'));
    }

    #[Test]
    public function it_converts_with_dynamic_options(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->withOption('export-png-color-mode', 'RGBA_16')
            ->withOption('export-png-compression', 9)
            ->withFlag('export-area-snap')
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--export-png-color-mode='RGBA_16'")
            && str_contains((string) $process->command, '--export-png-compression=9')
            && str_contains((string) $process->command, '--export-area-snap'));
    }

    #[Test]
    public function it_converts_with_custom_timeout(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('png')
            ->timeout(300)
            ->convert();

        Process::assertRan(fn ($process): bool => $process->timeout === 300);
    }

    #[Test]
    public function it_infers_format_from_export_name(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $result = $converter->convert('output.pdf');

        $this->assertStringEndsWith('.pdf', $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "'pdf'") || str_contains((string) $process->command, 'pdf'));
    }

    #[Test]
    public function it_uses_to_file_method(): void
    {
        Process::fake();

        $outputPath = sys_get_temp_dir().'/inkscape_test_output.png';

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $result = $converter->toFile($outputPath);

        $this->assertEquals($outputPath, $result);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, $outputPath));
    }

    #[Test]
    public function it_converts_with_export_margin(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('pdf')
            ->exportMargin(5)
            ->convert();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-margin=5'));
    }

    #[Test]
    public function it_converts_with_vacuum_defs(): void
    {
        Process::fake();

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter
            ->setFormat('svg')
            ->exportPlainSvg()
            ->vacuumDefs()
            ->convert('clean.svg');

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--vacuum-defs')
            && str_contains((string) $process->command, '--export-plain-svg'));
    }
}
