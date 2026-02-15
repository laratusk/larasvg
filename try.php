<?php

require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Process\ProcessManager;
use Illuminate\Support\Facades\Facade;
use Laratusk\Larasvg\Contracts\Provider;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Converters\RsvgConvertConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

// Bootstrap minimal Laravel app so Process facade works
$app = new Application(__DIR__);
$app->singleton('process', fn ($app) => new ProcessManager($app));
Facade::setFacadeApplication($app);

// ---------------------------------------------------------------------------
// Providers
// ---------------------------------------------------------------------------

$providers = [
    'resvg' => ResvgConverter::class,
    'inkscape' => InkscapeConverter::class,
    'rsvg-convert' => RsvgConvertConverter::class,
];

$name = $argv[1] ?? null;

if ($name === null || ! isset($providers[$name])) {
    echo "Usage: php try.php <provider>\n";
    echo 'Available: '.implode(', ', array_keys($providers))."\n";
    exit($name !== null ? 1 : 0);
}

// ---------------------------------------------------------------------------
// Setup
// ---------------------------------------------------------------------------

$svg = __DIR__.'/art/laratusk/drawing.svg';
$tmp = sys_get_temp_dir();
$class = $providers[$name];

/** @param class-string<Provider> $class */
$make = fn () => new $class($svg, $name, 60);

// ---------------------------------------------------------------------------
// Info
// ---------------------------------------------------------------------------

echo "Provider:  {$name}\n";

try {
    echo 'Version:   '.$make()->version()."\n";
} catch (SvgConverterException $e) {
    echo "Version:   [error: {$e->getMessage()}]\n";
    exit(1);
}

$supported = $make()->supportedFormats();
echo 'Formats:   '.implode(', ', $supported)."\n";
echo str_repeat('─', 50)."\n\n";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

$toFile = function (string $format, ?callable $configure = null) use ($make, $tmp, $name): void {
    $out = "{$tmp}/try-{$name}.{$format}";

    try {
        $c = $make()->setFormat($format);
        if ($configure !== null) {
            $configure($c);
        }
        $c->toFile($out);
        echo strtoupper($format)."  → {$out} (".number_format(filesize($out))." bytes)\n";
    } catch (SvgConverterException $e) {
        echo strtoupper($format)."  → failed: {$e->getMessage()}\n";
    }
};

$supports = fn (string $format) => in_array($format, $supported, true);

// ---------------------------------------------------------------------------
// Conversions
// ---------------------------------------------------------------------------

$toFile('png', fn (Provider $c) => $c->setDimensions(512, 512));

if ($supports('pdf')) {
    $toFile('pdf');
}

foreach (['ps', 'eps', 'svg'] as $format) {
    if ($supports($format)) {
        $toFile($format);
    }
}

// Stdout
try {
    $bytes = $make()->setDimensions(64, 64)->toStdout('png');
    $valid = str_starts_with($bytes, "\x89PNG") ? '✓ valid PNG' : '✗ invalid';
    echo 'STDOUT → '.number_format(strlen($bytes))." bytes, {$valid}\n";
} catch (SvgConverterException $e) {
    echo "STDOUT → failed: {$e->getMessage()}\n";
}
