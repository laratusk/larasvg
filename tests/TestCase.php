<?php

namespace Laratusk\Larasvg\Tests;

use Dotenv\Dotenv;
use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\SvgConverterServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        $this->loadEnvFile();
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SvgConverterServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'SvgConverter' => SvgConverter::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('svg-converter.default', 'resvg');
        $app['config']->set('svg-converter.providers.resvg.binary', env('RESVG_PATH', 'resvg'));
        $app['config']->set('svg-converter.providers.resvg.timeout', 60);
        $app['config']->set('svg-converter.providers.inkscape.binary', env('INKSCAPE_PATH', 'inkscape'));
        $app['config']->set('svg-converter.providers.inkscape.timeout', 60);
        $app['config']->set('svg-converter.default_disk', 'local');
    }

    /**
     * Create a temporary SVG file for testing.
     */
    protected function createTempSvg(?string $content = null): string
    {
        $content ??= '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="red"/></svg>';
        $path = sys_get_temp_dir().'/'.uniqid('test_svg_').'.svg';
        file_put_contents($path, $content);

        return $path;
    }

    private function loadEnvFile(): void
    {
        $envPath = dirname(__DIR__);

        if (file_exists($envPath.'/.env')) {
            Dotenv::createImmutable($envPath)->safeLoad();
        }
    }
}
