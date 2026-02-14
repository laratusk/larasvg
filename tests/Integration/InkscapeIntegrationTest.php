<?php

namespace Laratusk\SvgConverter\Tests\Integration;

use Illuminate\Support\Facades\Storage;
use Laratusk\SvgConverter\Exceptions\SvgConverterException;
use Laratusk\SvgConverter\Facades\SvgConverter;
use Laratusk\SvgConverter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests that run real Inkscape commands.
 * Requires Inkscape to be installed on the system.
 */
class InkscapeIntegrationTest extends TestCase
{
    private string $testSvg;

    private array $cleanupFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $binary = $this->app['config']->get('svg-converter.providers.inkscape.binary', 'inkscape');

        if (! file_exists($binary) && ! $this->commandExists($binary)) {
            $this->markTestSkipped("Inkscape binary not found at: {$binary}");
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
    // Version & Info
    // -------------------------------------------------------------------------

    #[Test]
    public function it_gets_real_inkscape_version(): void
    {
        $version = SvgConverter::version('inkscape');

        $this->assertNotEmpty($version);
        $this->assertStringContainsString('Inkscape', $version);
    }

    #[Test]
    public function it_gets_real_action_list(): void
    {
        $actions = SvgConverter::using('inkscape')->actionList();

        $this->assertNotEmpty($actions);
        $this->assertStringContainsString('export-filename', $actions);
    }

    // -------------------------------------------------------------------------
    // SVG -> PNG Conversion
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_png_for_real(): void
    {
        $outputPath = $this->tempOutputPath('png');

        SvgConverter::using('inkscape')->open($this->testSvg)
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

        SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(512, 512, 96)
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

        SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(200, 200)
            ->setBackground('#ffffff')
            ->setBackgroundOpacity(1.0)
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertPngFile($outputPath);
    }

    // -------------------------------------------------------------------------
    // SVG -> PDF Conversion
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_pdf_for_real(): void
    {
        $outputPath = $this->tempOutputPath('pdf');

        SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('pdf')
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertPdfFile($outputPath);
    }

    // -------------------------------------------------------------------------
    // SVG -> EPS Conversion
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_svg_to_eps_for_real(): void
    {
        $outputPath = $this->tempOutputPath('eps');

        SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('eps')
            ->toFile($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));
        $this->assertEpsFile($outputPath);
    }

    // -------------------------------------------------------------------------
    // Stdout Output
    // -------------------------------------------------------------------------

    #[Test]
    public function it_outputs_png_to_stdout_for_real(): void
    {
        $output = SvgConverter::using('inkscape')->open($this->testSvg)
            ->setDimensions(64, 64)
            ->toStdout('png');

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith("\x89PNG", $output);
    }

    // -------------------------------------------------------------------------
    // Disk Support
    // -------------------------------------------------------------------------

    #[Test]
    public function it_converts_to_disk_for_real(): void
    {
        Storage::fake('output-disk');

        $result = SvgConverter::using('inkscape')->open($this->testSvg)
            ->setFormat('png')
            ->setDimensions(128, 128)
            ->toDisk('output-disk', 'exports/converted.png');

        $this->assertEquals('exports/converted.png', $result);
        Storage::disk('output-disk')->assertExists('exports/converted.png');

        $content = Storage::disk('output-disk')->get('exports/converted.png');
        $this->assertStringStartsWith("\x89PNG", $content);
    }

    // -------------------------------------------------------------------------
    // Error Handling
    // -------------------------------------------------------------------------

    #[Test]
    public function it_throws_on_invalid_input_file(): void
    {
        $this->expectException(SvgConverterException::class);

        SvgConverter::using('inkscape')->open('/nonexistent/file.svg');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function tempOutputPath(string $extension): string
    {
        $path = sys_get_temp_dir().'/'.uniqid('inkscape_test_').'.'.$extension;
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

    private function assertPdfFile(string $path): void
    {
        $header = file_get_contents($path, false, null, 0, 4);
        $this->assertEquals('%PDF', $header, 'File is not a valid PDF');
    }

    private function assertEpsFile(string $path): void
    {
        $header = file_get_contents($path, false, null, 0, 10);
        $this->assertStringStartsWith('%!PS', $header, 'File is not a valid EPS');
    }

    private function commandExists(string $command): bool
    {
        $result = shell_exec("which {$command} 2>/dev/null");

        return ! in_array(trim((string) $result), ['', '0'], true);
    }
}
