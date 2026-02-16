<?php

/**
 * LaraSVG Benchmark
 *
 * Tests all 4 drivers (resvg, inkscape, rsvg-convert, cairosvg) for SVG→PNG conversion.
 * Measures: throughput (conversions/sec), latency (ms/conversion), peak binary RSS (MB).
 *
 * Usage:
 *   php benchmark.php                  # run all available drivers
 *   php benchmark.php resvg            # run one driver
 *   php benchmark.php --max=500        # override max conversions (default 10000)
 *   php benchmark.php --time=30        # override time limit per driver (default 60s)
 *   php benchmark.php --no-write       # skip writing benchmark.md
 */

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Process\ProcessManager;
use Illuminate\Support\Facades\Facade;
use Laratusk\Larasvg\Converters\CairosvgConverter;
use Laratusk\Larasvg\Converters\InkscapeConverter;
use Laratusk\Larasvg\Converters\ResvgConverter;
use Laratusk\Larasvg\Converters\RsvgConvertConverter;
use Laratusk\Larasvg\Exceptions\SvgConverterException;

// ---------------------------------------------------------------------------
// Bootstrap minimal Laravel app (Process facade)
// ---------------------------------------------------------------------------

$app = new Application(__DIR__);
$app->singleton('process', fn ($app) => new ProcessManager($app));
Facade::setFacadeApplication($app);

// ---------------------------------------------------------------------------
// CLI argument parsing
// ---------------------------------------------------------------------------

$args = $argv;
array_shift($args); // remove script name

$maxConversions = 10_000;
$timeLimitSec   = 60;
$writeResults   = true;
$filterDriver   = null;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--max=')) {
        $maxConversions = (int) substr($arg, 6);
    } elseif (str_starts_with($arg, '--time=')) {
        $timeLimitSec = (int) substr($arg, 7);
    } elseif ($arg === '--no-write') {
        $writeResults = false;
    } elseif (! str_starts_with($arg, '--')) {
        $filterDriver = $arg;
    }
}

// ---------------------------------------------------------------------------
// Providers
// ---------------------------------------------------------------------------

$allProviders = [
    'resvg'        => ['class' => ResvgConverter::class,       'binary' => 'resvg'],
    'inkscape'     => ['class' => InkscapeConverter::class,    'binary' => 'inkscape'],
    'rsvg-convert' => ['class' => RsvgConvertConverter::class, 'binary' => 'rsvg-convert'],
    'cairosvg'     => ['class' => CairosvgConverter::class,    'binary' => 'cairosvg'],
];

if ($filterDriver !== null) {
    if (! isset($allProviders[$filterDriver])) {
        fwrite(STDERR, "Unknown driver: {$filterDriver}\nAvailable: ".implode(', ', array_keys($allProviders))."\n");
        exit(1);
    }
    $allProviders = [$filterDriver => $allProviders[$filterDriver]];
}

// ---------------------------------------------------------------------------
// Test SVG
// ---------------------------------------------------------------------------

$svgContent = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
        <defs>
            <linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   style="stop-color:rgb(255,80,0);stop-opacity:1"/>
                <stop offset="100%" style="stop-color:rgb(0,80,255);stop-opacity:1"/>
            </linearGradient>
        </defs>
        <rect width="200" height="200" fill="url(#g1)"/>
        <circle cx="100" cy="100" r="60" fill="rgba(255,255,255,0.4)"/>
        <text x="100" y="108" text-anchor="middle" font-family="Arial" font-size="18" fill="#fff">LaraSVG</text>
        <rect x="20" y="155" width="160" height="28" rx="6" fill="#4CAF50" opacity="0.85"/>
    </svg>
    SVG;

$tempSvg = tempnam(sys_get_temp_dir(), 'larasvg_bench_').'.svg';
file_put_contents($tempSvg, $svgContent);

$outputPng = sys_get_temp_dir().DIRECTORY_SEPARATOR.'larasvg_bench_out.png';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function commandExists(string $binary): bool
{
    $result = shell_exec('which '.escapeshellarg($binary).' 2>/dev/null');

    return trim((string) $result) !== '';
}

/**
 * Measure peak RSS (MB) of the binary for one conversion.
 * Uses /usr/bin/time which is available on macOS and Linux.
 * Returns null if measurement is not supported or fails.
 */
function measurePeakRssMB(string $command): ?float
{
    $isMac   = PHP_OS_FAMILY === 'Darwin';
    $isLinux = PHP_OS_FAMILY === 'Linux';

    if ($isMac) {
        // macOS: /usr/bin/time -l prints peak RSS in bytes to stderr
        $timeCmd = '/usr/bin/time -l '.$command.' 2>&1 >/dev/null';
        $output  = (string) shell_exec($timeCmd);

        // "   12345678  maximum resident set size"
        if (preg_match('/(\d+)\s+maximum resident set size/', $output, $m)) {
            return round((int) $m[1] / 1024 / 1024, 1);
        }
    } elseif ($isLinux) {
        // Linux: /usr/bin/time -v prints peak RSS in kbytes to stderr
        $timeCmd = '/usr/bin/time -v '.$command.' 2>&1 >/dev/null';
        $output  = (string) shell_exec($timeCmd);

        // "	Maximum resident set size (kbytes): 12345"
        if (preg_match('/Maximum resident set size \(kbytes\):\s*(\d+)/', $output, $m)) {
            return round((int) $m[1] / 1024, 1);
        }
    }

    return null;
}

function formatMs(float $ms): string
{
    return number_format($ms, 1).' ms';
}

function formatMb(float $mb): string
{
    return number_format($mb, 1).' MB';
}

function bar(float $value, float $max, int $width = 20): string
{
    $fill  = $max > 0 ? (int) round(($value / $max) * $width) : 0;
    $empty = $width - $fill;

    return '['.str_repeat('#', $fill).str_repeat('-', $empty).']';
}

// ---------------------------------------------------------------------------
// Run benchmark
// ---------------------------------------------------------------------------

echo "\n";
echo "LaraSVG Benchmark\n";
echo str_repeat('═', 60)."\n";
echo "Max conversions : ".number_format($maxConversions)."\n";
echo "Time limit      : {$timeLimitSec}s per driver\n";
echo "Output size     : 200×200 px PNG\n";
echo "Platform        : ".PHP_OS_FAMILY.' ('.php_uname('m').")\n";
echo "PHP             : ".PHP_VERSION."\n";
echo str_repeat('─', 60)."\n\n";

$benchmarkDate = date('Y-m-d H:i:s');
$results       = [];

foreach ($allProviders as $name => ['class' => $class, 'binary' => $binary]) {
    echo "  [{$name}]";

    // Check binary
    if (! commandExists($binary)) {
        echo " — skipped (binary not found)\n";
        $results[$name] = ['status' => 'skipped', 'reason' => 'binary not found'];

        continue;
    }

    // Version check
    try {
        $version = (new $class($tempSvg, $binary, 10))->version();
    } catch (SvgConverterException $e) {
        echo " — skipped ({$e->getMessage()})\n";
        $results[$name] = ['status' => 'skipped', 'reason' => $e->getMessage()];

        continue;
    }

    echo " v{$version}\n";

    // Warmup (first run is always slower due to OS page faults / disk cache)
    echo "    Warmup...";

    try {
        (new $class($tempSvg, $binary, 60))
            ->setFormat('png')
            ->setDimensions(200, 200)
            ->toFile($outputPng);
        echo " done\n";
    } catch (SvgConverterException $e) {
        echo " FAILED: {$e->getMessage()}\n";
        $results[$name] = ['status' => 'failed', 'reason' => $e->getMessage()];

        continue;
    }

    // Measure binary peak RSS via /usr/bin/time on a probe instance
    echo "    Probing RSS...";
    $rssMB = null;

    try {
        // Build the exact command used for a real conversion
        $probe = new $class($tempSvg, $binary, 60);
        $probe->setFormat('png')->setDimensions(200, 200);
        // toFile sets up applyExportOptions internally, then we can read buildCommand()
        $probe->toFile($outputPng);
        $cmd   = $probe->buildCommand();
        $rssMB = measurePeakRssMB($cmd);
        echo $rssMB !== null ? ' '.formatMb($rssMB)."\n" : " n/a\n";
    } catch (\Throwable $e) {
        echo " n/a\n";
    }

    // Throughput benchmark
    echo "    Running benchmark";

    $times    = [];
    $errors   = 0;
    $start    = microtime(true);

    while (count($times) < $maxConversions) {
        $elapsed = microtime(true) - $start;

        if ($elapsed >= $timeLimitSec) {
            break;
        }

        if (count($times) % 50 === 0 && count($times) > 0) {
            echo '.';
        }

        $t0 = microtime(true);

        try {
            (new $class($tempSvg, $binary, 60))
                ->setFormat('png')
                ->setDimensions(200, 200)
                ->toFile($outputPng);
            $times[] = microtime(true) - $t0;
        } catch (SvgConverterException $e) {
            $errors++;

            if ($errors > 5) {
                echo "\n    Too many errors, stopping.\n";
                break;
            }
        }
    }

    echo "\n";

    $done      = count($times);
    $totalTime = microtime(true) - $start;

    if ($done === 0) {
        echo "    No successful conversions.\n";
        $results[$name] = ['status' => 'failed', 'reason' => 'no successful conversions'];

        continue;
    }

    sort($times);
    $avgMs    = (array_sum($times) / $done) * 1000;
    $minMs    = $times[0] * 1000;
    $maxMs    = $times[$done - 1] * 1000;
    $p50Ms    = $times[(int) ($done * 0.5)] * 1000;
    $p95Ms    = $times[(int) ($done * 0.95)] * 1000;
    $perSec   = $done / $totalTime;
    $hitLimit = $done >= $maxConversions ? 'max conversions reached' : "time limit ({$timeLimitSec}s) reached";

    printf("    Conversions : %s in %.1fs (%s)\n", number_format($done), $totalTime, $hitLimit);
    printf("    Throughput  : %.1f conv/sec\n", $perSec);
    printf("    Latency     : avg=%.1fms  min=%.1fms  p50=%.1fms  p95=%.1fms  max=%.1fms\n",
        $avgMs, $minMs, $p50Ms, $p95Ms, $maxMs);
    if ($rssMB !== null) {
        printf("    Peak RSS    : %.1f MB\n", $rssMB);
    }
    if ($errors > 0) {
        printf("    Errors      : %d\n", $errors);
    }
    echo "\n";

    $results[$name] = [
        'status'    => 'ok',
        'version'   => $version,
        'done'      => $done,
        'totalTime' => $totalTime,
        'perSec'    => $perSec,
        'avgMs'     => $avgMs,
        'minMs'     => $minMs,
        'maxMs'     => $maxMs,
        'p50Ms'     => $p50Ms,
        'p95Ms'     => $p95Ms,
        'rssMB'     => $rssMB,
        'hitLimit'  => $hitLimit,
        'errors'    => $errors,
    ];
}

// Cleanup
@unlink($tempSvg);
@unlink($outputPng);

// ---------------------------------------------------------------------------
// Write benchmark.md
// ---------------------------------------------------------------------------

if (! $writeResults) {
    echo "Skipping benchmark.md write (--no-write).\n";
    exit(0);
}

$md = generateMarkdown($results, $benchmarkDate, $maxConversions, $timeLimitSec);
file_put_contents(__DIR__.'/benchmark.md', $md);
echo "Results written to benchmark.md\n\n";

// ---------------------------------------------------------------------------
// Markdown generation
// ---------------------------------------------------------------------------

function generateMarkdown(
    array  $results,
    string $date,
    int    $maxConversions,
    int    $timeLimitSec,
): string {
    $platform = PHP_OS_FAMILY.' ('.php_uname('m').') PHP '.PHP_VERSION;

    $lines = [];
    $lines[] = '# LaraSVG Benchmark Results';
    $lines[] = '';
    $lines[] = '> SVG → PNG conversion, 200×200 px output.  ';
    $lines[] = '> Each driver is run for up to '.number_format($maxConversions).' conversions or '.$timeLimitSec.'s, whichever comes first.  ';
    $lines[] = '> Peak RSS is the maximum resident set size of the converter binary for one conversion.';
    $lines[] = '';
    $lines[] = "**Date:** {$date}  ";
    $lines[] = "**Platform:** {$platform}";
    $lines[] = '';

    // Summary table
    $lines[] = '## Summary';
    $lines[] = '';
    $lines[] = '| Driver | Version | Conversions | Throughput | Avg latency | p95 latency | Peak RSS |';
    $lines[] = '|--------|---------|-------------|------------|-------------|-------------|----------|';

    foreach ($results as $name => $r) {
        if ($r['status'] !== 'ok') {
            $reason = $r['reason'] ?? $r['status'];
            $lines[] = "| {$name} | — | — | — | — | — | — | *(skipped: {$reason})* |";

            continue;
        }

        $done    = number_format($r['done']);
        $perSec  = number_format($r['perSec'], 1).' / sec';
        $avg     = number_format($r['avgMs'], 1).' ms';
        $p95     = number_format($r['p95Ms'], 1).' ms';
        $rss     = $r['rssMB'] !== null ? number_format($r['rssMB'], 1).' MB' : 'n/a';
        $ver     = $r['version'];

        $lines[] = "| {$name} | {$ver} | {$done} | {$perSec} | {$avg} | {$p95} | {$rss} |";
    }

    $lines[] = '';

    // Per-driver detail sections
    $lines[] = '## Per-Driver Results';
    $lines[] = '';

    foreach ($results as $name => $r) {
        $lines[] = "### {$name}";
        $lines[] = '';

        if ($r['status'] === 'skipped') {
            $lines[] = '> **Skipped** — '.($r['reason'] ?? 'unknown reason');
            $lines[] = '';

            continue;
        }

        if ($r['status'] === 'failed') {
            $lines[] = '> **Failed** — '.($r['reason'] ?? 'unknown reason');
            $lines[] = '';

            continue;
        }

        $lines[] = "**Version:** {$r['version']}  ";
        $lines[] = "**Result:** {$r['hitLimit']}  ";
        if ($r['errors'] > 0) {
            $lines[] = "**Errors:** {$r['errors']}  ";
        }
        $lines[] = '';
        $lines[] = '| Metric | Value |';
        $lines[] = '|--------|-------|';
        $lines[] = '| Conversions completed | '.number_format($r['done']).' |';
        $lines[] = '| Total time | '.number_format($r['totalTime'], 1).' s |';
        $lines[] = '| Throughput | '.number_format($r['perSec'], 2).' conversions/sec |';
        $lines[] = '| Avg latency | '.number_format($r['avgMs'], 1).' ms |';
        $lines[] = '| Min latency | '.number_format($r['minMs'], 1).' ms |';
        $lines[] = '| p50 latency | '.number_format($r['p50Ms'], 1).' ms |';
        $lines[] = '| p95 latency | '.number_format($r['p95Ms'], 1).' ms |';
        $lines[] = '| Max latency | '.number_format($r['maxMs'], 1).' ms |';
        $lines[] = '| Peak binary RSS | '.($r['rssMB'] !== null ? number_format($r['rssMB'], 1).' MB' : 'n/a').' |';
        $lines[] = '';
    }

    // Visual throughput bar chart
    $okResults = array_filter($results, fn ($r) => $r['status'] === 'ok');

    if (count($okResults) > 1) {
        $maxPerSec = max(array_column($okResults, 'perSec'));

        $lines[] = '## Throughput Comparison';
        $lines[] = '';
        $lines[] = '```';

        foreach ($okResults as $name => $r) {
            $bar   = str_pad('', (int) round(($r['perSec'] / $maxPerSec) * 40), '█');
            $label = str_pad($name, 15);
            $val   = number_format($r['perSec'], 1);
            $lines[] = "{$label} {$bar} {$val} / sec";
        }

        $lines[] = '```';
        $lines[] = '';
    }

    // Notes
    $lines[] = '## Notes';
    $lines[] = '';
    $lines[] = '- Benchmark measures **wall-clock time** per conversion including process spawn overhead.';
    $lines[] = '- **Peak RSS** is sampled via `/usr/bin/time` for a single representative conversion and reflects the converter binary\'s own memory, not PHP.';
    $lines[] = '- First-run warmup is excluded from all measurements.';
    $lines[] = '- Results vary by machine load, SVG complexity, and binary version.';
    $lines[] = '- Inkscape and CairoSVG include interpreter/JVM startup cost per invocation.';
    $lines[] = '- For production use, consider daemon modes (Inkscape shell mode, CairoSVG server) to amortize startup costs.';
    $lines[] = '';
    $lines[] = '---';
    $lines[] = '_Generated by `php benchmark.php`_';

    return implode("\n", $lines)."\n";
}
