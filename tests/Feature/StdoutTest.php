<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StdoutTest extends TestCase
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

    // -------------------------------------------------------------------------
    // Inkscape stdout
    // -------------------------------------------------------------------------

    #[Test]
    public function it_outputs_to_stdout_as_png_via_inkscape(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'FAKE_PNG_BINARY_DATA',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $output = $converter->toStdout('png');

        $this->assertStringContainsString('FAKE_PNG_BINARY_DATA', $output);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, "--export-filename='-'")
            && str_contains((string) $process->command, "'png'"));
    }

    #[Test]
    public function it_outputs_to_stdout_as_pdf_via_inkscape(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'FAKE_PDF_DATA',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $output = $converter->toStdout('pdf');

        $this->assertStringContainsString('FAKE_PDF_DATA', $output);
    }

    #[Test]
    public function it_outputs_to_stdout_with_dimensions_via_inkscape(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'PNG_WITH_DIMS',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $output = $converter
            ->setDimensions(256, 256, 96)
            ->toStdout('png');

        $this->assertStringContainsString('PNG_WITH_DIMS', $output);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--export-width=256')
            && str_contains((string) $process->command, '--export-height=256')
            && str_contains((string) $process->command, "--export-filename='-'"));
    }

    // -------------------------------------------------------------------------
    // Resvg stdout
    // -------------------------------------------------------------------------

    #[Test]
    public function it_outputs_to_stdout_as_png_via_resvg(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'FAKE_PNG_BINARY_DATA',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $output = $converter->toStdout('png');

        $this->assertStringContainsString('FAKE_PNG_BINARY_DATA', $output);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--c'));
    }

    #[Test]
    public function it_outputs_to_stdout_with_dimensions_via_resvg(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'PNG_WITH_DIMS',
                errorOutput: '',
                exitCode: 0,
            ),
        ]);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $output = $converter
            ->setDimensions(256, 256, 96)
            ->toStdout('png');

        $this->assertStringContainsString('PNG_WITH_DIMS', $output);

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, '--width 256')
            && str_contains((string) $process->command, '--height 256')
            && str_contains((string) $process->command, '--c'));
    }

    // -------------------------------------------------------------------------
    // Facade stdout
    // -------------------------------------------------------------------------

    #[Test]
    public function it_uses_facade_to_stdout_with_resvg(): void
    {
        Process::fake([
            '*' => Process::result(output: 'FACADE_OUTPUT', errorOutput: '', exitCode: 0),
        ]);

        $output = SvgConverter::open($this->testSvg)
            ->toStdout('png');

        $this->assertStringContainsString('FACADE_OUTPUT', $output);
    }

    #[Test]
    public function it_uses_facade_to_stdout_with_inkscape(): void
    {
        Process::fake([
            '*' => Process::result(output: 'FACADE_OUTPUT', errorOutput: '', exitCode: 0),
        ]);

        $output = SvgConverter::using('inkscape')->open($this->testSvg)
            ->toStdout('png');

        $this->assertStringContainsString('FACADE_OUTPUT', $output);
    }
}
