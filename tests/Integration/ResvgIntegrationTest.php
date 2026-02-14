<?php

namespace Laratusk\Larasvg\Tests\Integration;

use Illuminate\Support\Facades\Storage;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests that run real Resvg commands.
 * Requires Resvg to be installed on the system.
 */
class ResvgIntegrationTest extends TestCase
{
    private string $testSvg;

    private array $cleanupFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $binary = $this->app['config']->get('svg-converter.providers.resvg.binary', 'resvg');

        if (! file_exists($binary) && ! $this->commandExists($binary)) {
            $this->markTestSkipped("Resvg binary not found at: {$binary}");
        }

        $this->testSvg = $this->createTempSvg($this->richSvgContent());
        $this->cleanupFiles[] = $this->testSvg;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->cleanupFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Version
    // -------------------------------------------------------------------------

    #[Test]
    public function it_gets_real_resvg_version(): void
    {
        $version = SvgConverter::version('resvg');

        $this->assertNotEmpty($version);
        $this->assertMatchesRegularExpression('/\d+\.\d+/', $version);
    }

    // -------------------------------------------------------------------------
    // SVG -> PNG Conversion
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_png_for_real(): void
    {
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));
        $this->assertPngFile($outputPath);
    }

    #[Test]
    public function it_converts_svg_to_png_with_dimensions(): void
    {
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(512, 512)
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);

        $imageInfo = getimagesize($outputPath);
        $this->assertNotFalse($imageInfo);
        $this->assertEquals(512, $imageInfo[0], 'Width should be 512px');
        $this->assertEquals(512, $imageInfo[1], 'Height should be 512px');
    }

    #[Test]
    public function it_converts_svg_to_png_with_background(): void
    {
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(200, 200)
            ->setBackground('#ffffff')
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertPngFile($outputPath);
    }

    #[Test]
    public function it_converts_svg_to_png_with_zoom(): void
    {
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setZoom(2.0)
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertPngFile($outputPath);
    }

    // -------------------------------------------------------------------------
    // Stdout Output
    // -------------------------------------------------------------------------

    #[Test]
    public function it_outputs_png_to_stdout_for_real(): void
    {
        $output = SvgConverter::open($this->testSvg)
            ->setDimensions(64, 64)
            ->toStdout('png');

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith("\x89PNG", $output);
    }

    // -------------------------------------------------------------------------
    // convert() Method
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_with_auto_export_name(): void
    {
        $result = SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(100, 100)
            ->convert();

        $this->cleanupFiles[] = $result;

        $this->assertFileExists($result);
        $this->assertStringEndsWith('.png', $result);
        $this->assertPngFile($result);
    }

    #[Test]
    public function it_converts_with_explicit_export_name(): void
    {
        $exportName = sys_get_temp_dir().'/'.uniqid('resvg_explicit_').'.png';
        $this->cleanupFiles[] = $exportName;

        $result = SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(128, 128)
            ->convert($exportName);

        $this->assertEquals($exportName, $result);
        $this->assertFileExists($result);
        $this->assertPngFile($result);
    }

    // -------------------------------------------------------------------------
    // Disk Support
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_to_disk_for_real(): void
    {
        Storage::fake('output-disk');

        $result = SvgConverter::open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(128, 128)
            ->toDisk('output-disk', 'exports/converted.png');

        $this->assertEquals('exports/converted.png', $result);
        Storage::disk('output-disk')->assertExists('exports/converted.png');

        $content = Storage::disk('output-disk')->get('exports/converted.png');
        $this->assertStringStartsWith("\x89PNG", $content);
    }

    // -------------------------------------------------------------------------
    // openFromContent
    // -------------------------------------------------------------------------

    #[Test]
    public function it_opens_from_content_and_converts_for_real(): void
    {
        $svgContent = $this->richSvgContent();
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::openFromContent($svgContent)
            ->setFormat('png')
            ->setDimensions(200, 200)
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertPngFile($outputPath);
    }

    // -------------------------------------------------------------------------
    // Error Handling
    // -------------------------------------------------------------------------

    #[Test]
    public function it_throws_on_invalid_input_file(): void
    {
        $this->expectException(SvgConverterException::class);

        SvgConverter::open('/nonexistent/file.svg');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function tempOutputPath(string $extension): string
    {
        $path = sys_get_temp_dir().'/'.uniqid('resvg_test_').'.'.$extension;
        $this->cleanupFiles[] = $path;

        return $path;
    }

    private function richSvgContent(): string
    {
        return <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
                <defs>
                    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:rgb(255,0,0);stop-opacity:1" />
                        <stop offset="100%" style="stop-color:rgb(0,0,255);stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect width="200" height="200" fill="url(#grad1)" />
                <circle cx="100" cy="100" r="60" fill="rgba(255,255,255,0.5)" />
                <text x="100" y="105" text-anchor="middle" font-family="Arial" font-size="20" fill="#333">Test</text>
                <rect x="20" y="150" width="160" height="30" rx="5" fill="#4CAF50" />
            </svg>
            SVG;
    }

    private function assertPngFile(string $path): void
    {
        $header = file_get_contents($path, false, null, 0, 8);
        $this->assertStringStartsWith("\x89PNG", $header, 'File is not a valid PNG');
    }

    private function commandExists(string $command): bool
    {
        $result = shell_exec("which {$command} 2>/dev/null");

        return ! in_array(trim((string) $result), ['', '0'], true);
    }
}
