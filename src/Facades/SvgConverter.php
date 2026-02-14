<?php

namespace Laratusk\SvgConverter\Facades;

use Illuminate\Support\Facades\Facade;
use Laratusk\SvgConverter\Contracts\Provider;
use Laratusk\SvgConverter\SvgConverterManager;

/**
 * @method static Provider            open(string $path)
 * @method static Provider            openFromDisk(string $disk, string $path)
 * @method static Provider            openFromContent(string $content, string $extension = 'svg')
 * @method static SvgConverterManager using(string $provider)
 * @method static string              version(?string $provider = null)
 * @method static string              actionList()
 * @method static string              getBinary(?string $provider = null)
 * @method static int                 getTimeout(?string $provider = null)
 * @method static string              getDefaultDisk()
 *
 * @see SvgConverterManager
 */
class SvgConverter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SvgConverterManager::class;
    }
}
