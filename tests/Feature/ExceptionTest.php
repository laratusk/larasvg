<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExceptionTest extends TestCase
{
    private string $testSvg;

    protected function setUp(): void
    {
        parent::setUp();
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
    public function it_throws_on_inkscape_process_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'Unknown option: --invalid-flag',
                exitCode: 1,
            ),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unknown option: --invalid-flag');

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->setFormat('png')->convert();
    }

    #[Test]
    public function it_throws_on_resvg_process_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'Error: invalid input',
                exitCode: 1,
            ),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Error: invalid input');

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setFormat('png')->convert();
    }

    #[Test]
    public function it_includes_exit_code_in_exception(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'partial output',
                errorOutput: 'Segmentation fault',
                exitCode: 139,
            ),
        ]);

        try {
            $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
            $converter->setFormat('png')->convert();
            $this->fail('Expected SvgConverterException was not thrown');
        } catch (SvgConverterException $e) {
            $this->assertEquals(139, $e->exitCode);
            $this->assertStringContainsString('partial output', $e->output);
            $this->assertStringContainsString('Segmentation fault', $e->errorOutput);
        }
    }

    #[Test]
    public function it_includes_stderr_in_exception_message(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'Error: Could not open file "nonexistent.svg"',
                exitCode: 1,
            ),
        ]);

        try {
            $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
            $converter->setFormat('png')->convert();
            $this->fail('Expected SvgConverterException was not thrown');
        } catch (SvgConverterException $e) {
            $this->assertStringContainsString('Could not open file', $e->getMessage());
        }
    }

    #[Test]
    public function it_provides_exception_summary(): void
    {
        $exception = new SvgConverterException(
            message: 'Test failure',
            output: 'some stdout',
            errorOutput: 'some stderr',
            exitCode: 2,
        );

        $summary = $exception->getSummary();

        $this->assertStringContainsString('Exit code: 2', $summary);
        $this->assertStringContainsString('Stderr: some stderr', $summary);
        $this->assertStringContainsString('Stdout: some stdout', $summary);
    }

    #[Test]
    public function it_creates_exception_from_process_result(): void
    {
        Process::fake([
            '*' => Process::result(
                output: 'output data',
                errorOutput: 'error data',
                exitCode: 42,
            ),
        ]);

        $result = Process::run('fake command');
        $exception = SvgConverterException::fromProcess($result, 'resvg --test', 'Resvg');

        $this->assertStringContainsString('error data', $exception->getMessage());
        $this->assertStringContainsString('Resvg', $exception->getMessage());
        $this->assertStringContainsString('resvg --test', $exception->getMessage());
        $this->assertStringContainsString('output data', $exception->output);
        $this->assertStringContainsString('error data', $exception->errorOutput);
        $this->assertEquals(42, $exception->exitCode);
    }

    #[Test]
    public function it_provides_fallback_message_when_stderr_is_empty(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: '',
                exitCode: 1,
            ),
        ]);

        $result = Process::run('fake');
        $exception = SvgConverterException::fromProcess($result, '', 'Resvg');

        $this->assertEquals('Resvg process failed.', $exception->getMessage());
    }

    #[Test]
    public function it_throws_on_version_check_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'resvg: command not found',
                exitCode: 127,
            ),
        ]);

        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('command not found');

        SvgConverter::version();
    }

    #[Test]
    public function it_throws_for_unsupported_format_on_inkscape(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format');

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->setFormat('jpg');
    }

    #[Test]
    public function it_throws_for_unsupported_format_on_resvg(): void
    {
        $this->expectException(SvgConverterException::class);
        $this->expectExceptionMessage('Unsupported export format: pdf');

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setFormat('pdf');
    }

    #[Test]
    public function it_throws_for_invalid_background_color(): void
    {
        $this->expectException(SvgConverterException::class);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setBackground('invalid');
    }

    #[Test]
    public function it_throws_for_invalid_opacity_too_high(): void
    {
        $this->expectException(SvgConverterException::class);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setBackgroundOpacity(2.0);
    }

    #[Test]
    public function it_throws_for_invalid_opacity_negative(): void
    {
        $this->expectException(SvgConverterException::class);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->setBackgroundOpacity(-0.1);
    }

    #[Test]
    public function raw_does_not_throw_on_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'some error',
                exitCode: 1,
            ),
        ]);

        $converter = new InkscapeConverter($this->testSvg, '/usr/bin/inkscape', 60);
        $converter->withFlag('version');

        $result = $converter->raw();

        $this->assertTrue($result->failed());
        $this->assertEquals(1, $result->exitCode());
        $this->assertStringContainsString('some error', $result->errorOutput());
    }

    #[Test]
    public function resvg_raw_does_not_throw_on_failure(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'some error',
                exitCode: 1,
            ),
        ]);

        $converter = new ResvgConverter($this->testSvg, '/usr/bin/resvg', 60);
        $converter->withFlag('version');

        $result = $converter->raw();

        $this->assertTrue($result->failed());
        $this->assertEquals(1, $result->exitCode());
    }

    #[Test]
    public function it_includes_provider_name_in_exception_message(): void
    {
        Process::fake([
            '*' => Process::result(
                output: '',
                errorOutput: 'some error',
                exitCode: 1,
            ),
        ]);

        $result = Process::run('fake');

        $inkscapeException = SvgConverterException::fromProcess($result, 'cmd', 'Inkscape');
        $this->assertStringContainsString('Inkscape', $inkscapeException->getMessage());

        $resvgException = SvgConverterException::fromProcess($result, 'cmd', 'Resvg');
        $this->assertStringContainsString('Resvg', $resvgException->getMessage());
    }
}
