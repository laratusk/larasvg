<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DiskSupportTest extends TestCase
{
    #[Test]
    public function it_opens_from_disk_with_default_provider(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('designs/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>');

        $converter = SvgConverter::openFromDisk('s3', 'designs/logo.svg');

        $this->assertInstanceOf(Provider::class, $converter);
        $this->assertInstanceOf(ResvgConverter::class, $converter);
        $this->assertFileExists($converter->inputPath);
        $this->assertStringEndsWith('.svg', $converter->inputPath);
    }

    #[Test]
    public function it_opens_from_disk_with_inkscape_provider(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('designs/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>');

        $converter = SvgConverter::using('inkscape')->openFromDisk('s3', 'designs/logo.svg');

        $this->assertInstanceOf(InkscapeConverter::class, $converter);
    }

    #[Test]
    public function it_preserves_file_extension_from_disk(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('docs/manual.pdf', '%PDF-1.4 fake');

        $converter = SvgConverter::using('inkscape')->openFromDisk('local', 'docs/manual.pdf');

        $this->assertStringEndsWith('.pdf', $converter->inputPath);
    }

    #[Test]
    public function it_builds_correct_command_for_disk_conversion(): void
    {
        Process::fake();
        Storage::fake('s3');

        Storage::disk('s3')->put('designs/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><rect height="1" width="1"/></svg>');

        $converter = SvgConverter::using('inkscape')->openFromDisk('s3', 'designs/logo.svg');
        $converter->setFormat('png')->setDimensions(512, 512);

        $command = $converter->buildCommand();

        $expectedBinary = $this->app['config']->get('svg-converter.providers.inkscape.binary');
        $this->assertStringContainsString("'{$expectedBinary}'", $command);
        $this->assertStringContainsString('.svg', $command);
    }

    #[Test]
    public function to_disk_throws_when_output_file_missing(): void
    {
        Process::fake();
        Storage::fake('s3');

        $tempSvg = $this->createTempSvg();

        try {
            $this->expectException(SvgConverterException::class);
            $this->expectExceptionMessage('did not produce the expected output file');

            $converter = new InkscapeConverter($tempSvg, '/usr/bin/inkscape', 60);
            $converter->setFormat('png')->toDisk('s3', 'exports/logo.png');
        } finally {
            @unlink($tempSvg);
        }
    }

    #[Test]
    public function to_disk_uploads_output_file_when_it_exists(): void
    {
        Storage::fake('s3');

        $tempSvg = $this->createTempSvg();

        try {
            $outputPath = sys_get_temp_dir().'/'.uniqid('test_output_').'.png';
            file_put_contents($outputPath, 'fake png data');

            Storage::disk('s3')->put('exports/logo.png', file_get_contents($outputPath));

            Storage::disk('s3')->assertExists('exports/logo.png');
            $this->assertEquals('fake png data', Storage::disk('s3')->get('exports/logo.png'));
        } finally {
            @unlink($tempSvg);
            @unlink($outputPath ?? '');
        }
    }

    #[Test]
    public function it_opens_from_content_and_converts(): void
    {
        Process::fake();

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><circle r="100"/></svg>';

        $result = SvgConverter::using('inkscape')->openFromContent($svg)
            ->setFormat('png')
            ->setDimensions(1024, 1024)
            ->convert();

        $this->assertStringEndsWith('.png', $result);
    }

    #[Test]
    public function disk_file_content_is_preserved(): void
    {
        Storage::fake('test');
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="50" height="50"/></svg>';
        Storage::disk('test')->put('input.svg', $svgContent);

        $converter = SvgConverter::openFromDisk('test', 'input.svg');

        $this->assertEquals($svgContent, file_get_contents($converter->inputPath));
    }

    #[Test]
    public function to_disk_throws_when_resvg_output_missing(): void
    {
        Process::fake();
        Storage::fake('s3');

        $tempSvg = $this->createTempSvg();

        try {
            $this->expectException(SvgConverterException::class);
            $this->expectExceptionMessage('did not produce the expected output file');

            $converter = new ResvgConverter($tempSvg, '/usr/bin/resvg', 60);
            $converter->setFormat('png')->toDisk('s3', 'exports/logo.png');
        } finally {
            @unlink($tempSvg);
        }
    }
}
