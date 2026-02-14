<?php

namespace Laratusk\Larasvg\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class SetupCommand extends Command
{
    protected $signature = 'larasvg:setup';

    protected $description = 'Install and configure SVG conversion providers (Inkscape, Resvg)';

    /**
     * @var array<string, array{installed: bool, version: string, path: string}>
     */
    private array $providers = [];

    private string $scriptPath;

    public function handle(): int
    {
        $this->scriptPath = dirname(__DIR__, 2).'/bin/install.sh';

        if (! file_exists($this->scriptPath)) {
            error('Install script not found at: '.$this->scriptPath);

            return self::FAILURE;
        }

        $this->newLine();
        info('LaraSVG — Provider Setup');
        $this->newLine();

        $status = $this->detectProviders();

        if ($status === null) {
            error('Failed to detect system providers.');

            return self::FAILURE;
        }

        $this->displaySystemInfo($status);
        $this->displayProviderStatus();

        $allInstalled = collect($this->providers)->every(fn (array $p): bool => $p['installed']);

        if ($allInstalled) {
            $this->newLine();
            info('All providers are already installed. You\'re all set!');
            $this->newLine();

            return self::SUCCESS;
        }

        $selected = $this->promptForProvider();

        if ($selected === null) {
            return self::SUCCESS;
        }

        return $this->installProvider($selected);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function detectProviders(): ?array
    {
        /** @var array<string, mixed>|null $status */
        $status = spin(
            callback: function (): ?array {
                $result = Process::timeout(30)->run(
                    'bash '.escapeshellarg($this->scriptPath).' --check',
                );

                if ($result->failed()) {
                    return null;
                }

                /** @var array<string, mixed>|null */
                return json_decode($result->output(), true);
            },
            message: 'Detecting installed providers...',
        );

        if (! is_array($status)) {
            return null;
        }

        foreach (['inkscape', 'resvg'] as $name) {
            if (isset($status[$name]) && is_array($status[$name])) {
                /** @var array{installed?: bool, version?: string, path?: string} $providerData */
                $providerData = $status[$name];

                $this->providers[$name] = [
                    'installed' => (bool) ($providerData['installed'] ?? false),
                    'version' => (string) ($providerData['version'] ?? ''),
                    'path' => (string) ($providerData['path'] ?? ''),
                ];
            }
        }

        return $status;
    }

    /**
     * @param array<string, mixed> $status
     */
    private function displaySystemInfo(array $status): void
    {
        $os = isset($status['os']) && is_string($status['os']) ? $status['os'] : 'unknown';
        $distro = isset($status['distro']) && is_string($status['distro']) ? $status['distro'] : 'unknown';

        note("System: {$os} ({$distro})");
        $this->newLine();
    }

    private function displayProviderStatus(): void
    {
        foreach ($this->providers as $name => $provider) {
            $label = ucfirst($name);

            if ($provider['installed']) {
                $this->components->twoColumnDetail(
                    "<fg=green;options=bold>● {$label}</>",
                    "<fg=gray>{$provider['version']}</> <fg=gray;options=bold>{$provider['path']}</>",
                );
            } else {
                $this->components->twoColumnDetail(
                    "<fg=red>○ {$label}</>",
                    '<fg=red>not installed</>',
                );
            }
        }
    }

    private function promptForProvider(): ?string
    {
        $options = [];
        $disabledOptions = [];

        foreach ($this->providers as $name => $provider) {
            $label = ucfirst($name);

            if ($provider['installed']) {
                $options[$name] = "{$label} — already installed ({$provider['version']})";
                $disabledOptions[] = $name;
            } else {
                $formats = match ($name) {
                    'inkscape' => 'PNG, PDF, PS, EPS, EMF, WMF',
                    'resvg' => 'PNG — fast, lightweight',
                    default => '',
                };
                $options[$name] = "{$label} — {$formats}";
            }
        }

        $options['skip'] = 'Skip — I\'ll install manually later';

        $available = array_keys(array_filter(
            $options,
            fn (string $key): bool => ! in_array($key, $disabledOptions, true),
            ARRAY_FILTER_USE_KEY,
        ));

        $default = collect($available)->first(fn (string $key): bool => $key !== 'skip') ?? 'skip';

        $this->newLine();
        $selected = select(
            label: 'Which provider would you like to install?',
            options: $options,
            default: $default,
            validate: function (string $value) use ($disabledOptions): ?string {
                if (in_array($value, $disabledOptions, true)) {
                    return ucfirst($value).' is already installed.';
                }

                return null;
            },
            hint: 'Installed providers cannot be selected.',
        );

        if ($selected === 'skip') {
            $this->newLine();
            warning('Skipped. You can run this command again anytime.');
            $this->newLine();

            return null;
        }

        return (string) $selected;
    }

    private function installProvider(string $provider): int
    {
        $label = ucfirst($provider);

        $this->newLine();
        info("Installing {$label}...");
        $this->newLine();

        $result = Process::timeout(600)
            ->run('bash '.escapeshellarg($this->scriptPath).' '.escapeshellarg($provider));

        if ($result->output() !== '' && $result->output() !== '0') {
            $this->output->write($result->output());
        }

        if ($result->failed()) {
            $this->newLine();
            error("{$label} installation failed.");

            if ($result->errorOutput() !== '' && $result->errorOutput() !== '0') {
                $this->output->write($result->errorOutput());
            }

            return self::FAILURE;
        }

        $this->newLine();
        info("{$label} installed successfully!");

        $this->suggestConfig($provider);

        return self::SUCCESS;
    }

    private function suggestConfig(string $provider): void
    {
        $this->newLine();
        note('Add this to your .env file:');
        $this->newLine();

        $envVar = match ($provider) {
            'inkscape' => 'SVG_CONVERTER_DRIVER=inkscape',
            'resvg' => 'SVG_CONVERTER_DRIVER=resvg',
            default => '',
        };

        $this->line("  <fg=cyan>{$envVar}</>");
        $this->newLine();
    }
}
