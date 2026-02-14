<?php

namespace Laratusk\Larasvg;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

class SvgConverterManager
{
    /**
     * The provider to use for the next operation.
     */
    protected ?string $provider = null;

    public function __construct(
        protected Application $app,
    ) {}

    /**
     * Open a local file for processing.
     */
    public function open(string $path): Provider
    {
        if (! file_exists($path)) {
            throw new SvgConverterException("Input file does not exist: {$path}");
        }

        return $this->newInstance($path);
    }

    /**
     * Open a file from a Laravel filesystem disk.
     *
     * Downloads the file to a temp location, then opens it.
     * Temp files are cleaned up when the converter instance is destroyed.
     */
    public function openFromDisk(string $disk, string $path): Provider
    {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new SvgConverterException("File does not exist on disk [{$disk}]: {$path}");
        }

        $content = $storage->get($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'svg';

        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('svgconverter_disk_').'.'.$extension;
        file_put_contents($tempPath, $content);

        $instance = $this->newInstance($tempPath);
        $instance->addTempFile($tempPath);

        return $instance;
    }

    /**
     * Open from raw file content (string).
     *
     * Writes content to a temp file, then opens it.
     */
    public function openFromContent(string $content, string $extension = 'svg'): Provider
    {
        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('svgconverter_content_').'.'.$extension;
        file_put_contents($tempPath, $content);

        $instance = $this->newInstance($tempPath);
        $instance->addTempFile($tempPath);

        return $instance;
    }

    /**
     * Switch provider for this call.
     */
    public function using(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the version string of the given or default provider.
     */
    public function version(?string $provider = null): string
    {
        $providerName = $provider ?? $this->resolveProviderName();

        $binary = $this->getBinary($providerName);
        $timeout = $this->getTimeout($providerName);

        // Create a dummy instance just to call version()
        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('svgconverter_version_').'.svg';
        file_put_contents($tempPath, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        try {
            $instance = $this->createConverter($providerName, $tempPath, $binary, $timeout);

            return $instance->version();
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Get the list of available Inkscape actions.
     * Only available when using the Inkscape provider.
     */
    public function actionList(): string
    {
        $providerName = $this->resolveProviderName();
        $binary = $this->getBinary($providerName);
        $timeout = $this->getTimeout($providerName);

        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('svgconverter_actions_').'.svg';
        file_put_contents($tempPath, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        try {
            $instance = $this->createConverter($providerName, $tempPath, $binary, $timeout);

            if (! $instance instanceof InkscapeConverter) {
                throw new SvgConverterException('actionList() is only available for the Inkscape provider.');
            }

            return $instance->actionList();
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Get the configured binary path for the given or default provider.
     */
    public function getBinary(?string $provider = null): string
    {
        $provider ??= $this->resolveProviderName();

        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        $binary = $config->get("svg-converter.providers.{$provider}.binary", $provider);

        return is_string($binary) ? $binary : $provider;
    }

    /**
     * Get the configured timeout for the given or default provider.
     */
    public function getTimeout(?string $provider = null): int
    {
        $provider ??= $this->resolveProviderName();

        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        $timeout = $config->get("svg-converter.providers.{$provider}.timeout", 60);

        return is_int($timeout) ? $timeout : 60;
    }

    /**
     * Get the configured default disk.
     */
    public function getDefaultDisk(): string
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        $disk = $config->get('svg-converter.default_disk', 'local');

        return is_string($disk) ? $disk : 'local';
    }

    /**
     * Resolve the provider name to use.
     */
    protected function resolveProviderName(): string
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        $default = $config->get('svg-converter.default', 'resvg');
        $provider = $this->provider ?? (is_string($default) ? $default : 'resvg');

        // Reset for next call
        $this->provider = null;

        return $provider;
    }

    /**
     * Create a new converter instance with the configured defaults.
     */
    protected function newInstance(string $inputPath): Provider
    {
        $providerName = $this->resolveProviderName();
        $binary = $this->getBinary($providerName);
        $timeout = $this->getTimeout($providerName);

        return $this->createConverter($providerName, $inputPath, $binary, $timeout);
    }

    /**
     * Create a converter instance for the given provider.
     */
    protected function createConverter(string $provider, string $inputPath, string $binary, int $timeout): Provider
    {
        return match ($provider) {
            'inkscape' => new InkscapeConverter($inputPath, $binary, $timeout),
            'resvg' => new ResvgConverter($inputPath, $binary, $timeout),
            default => throw new SvgConverterException("Unknown SVG converter provider: {$provider}"),
        };
    }
}
