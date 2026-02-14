<?php

namespace Laratusk\Larasvg\Tests\Feature;

use Illuminate\Support\Facades\Process;
use Laratusk\Larasvg\Commands\SetupCommand;
use Laratusk\Larasvg\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SetupCommandTest extends TestCase
{
    #[Test]
    public function it_validates_provider_selection(): void
    {
        $command = new SetupCommand;

        $this->assertNull($command->validateProvider('resvg', []));
        $this->assertStringContainsString('already installed', $command->validateProvider('resvg', ['resvg']));
    }

    #[Test]
    public function it_detects_all_providers_installed_and_exits_successfully(): void
    {
        $json = json_encode([
            'os' => 'Linux',
            'distro' => 'Ubuntu 22.04',
            'inkscape' => [
                'installed' => true,
                'version' => 'Inkscape 1.4',
                'path' => '/usr/bin/inkscape',
            ],
            'resvg' => [
                'installed' => true,
                'version' => 'resvg 0.44.0',
                'path' => '/usr/bin/resvg',
            ],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $json, exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->assertSuccessful();
    }

    #[Test]
    public function it_fails_when_detection_process_fails(): void
    {
        Process::fake([
            '*--check*' => Process::result(output: '', errorOutput: 'script error', exitCode: 1),
        ]);

        $this->artisan('larasvg:setup')
            ->assertFailed();
    }

    #[Test]
    public function it_fails_when_detection_returns_invalid_json(): void
    {
        Process::fake([
            '*--check*' => Process::result(output: 'not json', exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->assertFailed();
    }

    #[Test]
    public function it_prompts_to_install_missing_provider(): void
    {
        $detectJson = json_encode([
            'os' => 'Darwin',
            'distro' => 'macOS 15',
            'inkscape' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
            'resvg' => [
                'installed' => true,
                'version' => 'resvg 0.44.0',
                'path' => '/opt/homebrew/bin/resvg',
            ],
        ]);

        $installOutput = 'Installing inkscape...done';

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
            '*inkscape*' => Process::result(output: $installOutput, exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsQuestion('Which provider would you like to install?', 'inkscape')
            ->assertSuccessful();

        Process::assertRan(fn ($process): bool => str_contains((string) $process->command, 'inkscape')
            && ! str_contains((string) $process->command, '--check'));
    }

    #[Test]
    public function it_handles_install_failure(): void
    {
        $detectJson = json_encode([
            'os' => 'Linux',
            'distro' => 'Ubuntu',
            'inkscape' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
            'resvg' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
            '*' => Process::result(output: '', errorOutput: 'Permission denied', exitCode: 1),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsQuestion('Which provider would you like to install?', 'resvg')
            ->assertFailed();
    }

    #[Test]
    public function it_allows_skipping_installation(): void
    {
        $detectJson = json_encode([
            'os' => 'Linux',
            'distro' => 'Ubuntu',
            'inkscape' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
            'resvg' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsQuestion('Which provider would you like to install?', 'skip')
            ->assertSuccessful();
    }

    #[Test]
    public function it_suggests_resvg_env_config_after_install(): void
    {
        $detectJson = json_encode([
            'os' => 'Linux',
            'distro' => 'Ubuntu',
            'inkscape' => [
                'installed' => true,
                'version' => 'Inkscape 1.4',
                'path' => '/usr/bin/inkscape',
            ],
            'resvg' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
            '*resvg*' => Process::result(output: 'Done', exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsQuestion('Which provider would you like to install?', 'resvg')
            ->expectsOutputToContain('SVG_CONVERTER_DRIVER=resvg')
            ->assertSuccessful();
    }

    #[Test]
    public function it_suggests_inkscape_env_config_after_install(): void
    {
        $detectJson = json_encode([
            'os' => 'Linux',
            'distro' => 'Ubuntu',
            'inkscape' => [
                'installed' => false,
                'version' => '',
                'path' => '',
            ],
            'resvg' => [
                'installed' => true,
                'version' => 'resvg 0.44.0',
                'path' => '/usr/bin/resvg',
            ],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
            '*inkscape*' => Process::result(output: 'Done', exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsQuestion('Which provider would you like to install?', 'inkscape')
            ->expectsOutputToContain('SVG_CONVERTER_DRIVER=inkscape')
            ->assertSuccessful();
    }

    #[Test]
    public function it_handles_missing_os_info_gently(): void
    {
        $detectJson = json_encode([
            // Missing os/distro
            'inkscape' => ['installed' => true, 'version' => '1.0', 'path' => '/bin/ink'],
            'resvg' => ['installed' => true, 'version' => '1.0', 'path' => '/bin/res'],
        ]);

        Process::fake([
            '*--check*' => Process::result(output: $detectJson, exitCode: 0),
        ]);

        $this->artisan('larasvg:setup')
            ->expectsOutputToContain('System: unknown (unknown)')
            ->assertSuccessful();
    }
}
