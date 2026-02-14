# Artisan Commands

## `larasvg:setup`

The interactive setup command detects your system and helps install conversion providers.

```bash
php artisan larasvg:setup
```

### What It Does

1. **Detects your OS** — macOS, Ubuntu, Fedora, Arch, Alpine, etc.
2. **Checks installed providers** — Shows versions of already-installed providers
3. **Prompts for installation** — Select a provider to install
4. **Installs the provider** — Uses the appropriate package manager
5. **Suggests configuration** — Outputs the `.env` variables to set

### Example Output

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

### Behavior

- Already-installed providers are shown with their version but cannot be selected
- The command detects the correct package manager for your OS
- After installation, it suggests the `.env` configuration

### Supported Package Managers

| OS | Package Manager |
|----|----------------|
| macOS | Homebrew |
| Ubuntu/Debian | apt |
| Fedora | dnf |
| Arch | pacman |
| Alpine | apk |
