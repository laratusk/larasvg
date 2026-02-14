<?php

namespace Laratusk\Larasvg\Tests;

use Laratusk\Larasvg\Facades\SvgConverter;
use Laratusk\Larasvg\SvgConverterServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
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
        $app['config']->set('svg-converter.providers.resvg.binary', '/usr/local/bin/resvg');
        $app['config']->set('svg-converter.providers.resvg.timeout', 60);
        $app['config']->set('svg-converter.providers.inkscape.binary', '/usr/local/bin/inkscape');
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
}
