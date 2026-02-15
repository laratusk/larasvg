# Installation

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x

## Install via Composer

```bash
composer require laratusk/larasvg
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=larasvg-config
```

This will create `config/svg-converter.php` with default settings.

## Setup Providers

Run the interactive setup command to detect and install conversion providers:

```bash
php artisan larasvg:setup
```

The command will:

1. Detect your operating system (macOS, Ubuntu, Fedora, Arch, Alpine, etc.)
2. Check which providers are already installed and show their versions
3. Prompt you to select a provider to install
4. Install the selected provider using the appropriate package manager
5. Suggest the `.env` configuration to use

```
  LaraSVG — Provider Setup

  System: macos (macos)

  ● Inkscape ··· Inkscape 1.4.3 (0d15f75, 2025-12-25) /opt/homebrew/bin/inkscape
  ○ Resvg ····· not installed

  Which provider would you like to install?
  › Inkscape — already installed (Inkscape 1.4.3)        (disabled)
    Resvg — PNG — fast, lightweight
    Skip — I'll install manually later
```

Already-installed providers are shown but cannot be selected.

## Manual Installation

You can install providers manually using the bundled shell script:

```bash
# Show status
./vendor/laratusk/larasvg/bin/install.sh

# Install a specific provider
./vendor/laratusk/larasvg/bin/install.sh resvg
./vendor/laratusk/larasvg/bin/install.sh inkscape
```

### Installing Resvg

::: code-group

```bash [macOS]
brew install resvg
```

```bash [Ubuntu/Debian]
cargo install resvg
```

```bash [Arch]
pacman -S resvg
```

:::

### Installing Inkscape

::: code-group

```bash [macOS]
brew install --cask inkscape
```

```bash [Ubuntu/Debian]
sudo apt install inkscape
```

```bash [Arch]
pacman -S inkscape
```

:::
