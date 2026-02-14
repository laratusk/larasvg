<?php

namespace Laratusk\Larasvg;

use Laratusk\Larasvg\Commands\SetupCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SvgConverterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('larasvg')
            ->hasConfigFile('svg-converter')
            ->hasCommand(SetupCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SvgConverterManager::class, fn ($app): \Laratusk\Larasvg\SvgConverterManager => new SvgConverterManager($app));
    }
}
