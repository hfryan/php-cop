# PHPCop 🚓

<p align="center">
  <img src="phpcop.png" alt="PHPCop Logo" width="300">
</p>

<p align="center">
  <strong>Dependency Patrol — PHP Security Scanner</strong>
</p>

PHPCop checks your `composer.lock` file and flags outdated or suspicious packages. Keep your PHP dependencies secure and up-to-date with comprehensive security scanning.

## Features

- 🚨 **Security Vulnerability Detection** - Scans for known CVEs using `composer audit`
- ⬆️ **Outdated Package Detection** - Identifies packages with newer versions available
- 🚫 **Abandoned Package Detection** - Flags packages that are no longer maintained
- ⌛ **Stale Package Detection** - Finds packages that haven't been updated in months
- 📊 **Multiple Output Formats** - Table, JSON, Markdown, and HTML output
- 🎯 **Configurable Thresholds** - Set custom severity levels and staleness periods
- ⚡ **CI/CD Ready** - Returns appropriate exit codes for automation

## Installation

### Via Composer (Recommended)

```bash
composer global require hfryan/php-cop
```

Then use anywhere:
```bash
phpcop scan
```

### Via Download

Download the latest release and run directly with PHP:
```bash
php phpcop.phar scan
```

## Usage

### Basic Scan
```bash
phpcop scan
```

### Custom Options
```bash
# JSON output
phpcop scan --format=json

# Custom staleness threshold (12 months)
phpcop scan --stale-months=12

# Fail on moderate vulnerabilities instead of high
phpcop scan --fail-on=moderate

# Use custom composer binary
phpcop scan --composer-bin=composer.bat
```

### Sample Output
```
🚓 PHP Cop: Dependency Patrol — Case File
--------------------------------------------------------------------------------
• guzzlehttp/psr7 2.7.1  [⬆️ Outdated → 2.8.0]
• psr/container 2.0.2  [⌛ Stale]
• symfony/console v7.3.2  [⬆️ Outdated → v7.3.3]
   └─ 🚨 high CVE-2023-12345 https://cve.mitre.org/...
```

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--format` | `table` | Output format: `table`, `json`, `md`, `html` |
| `--stale-months` | `18` | Months to flag packages as stale |
| `--fail-on` | `high` | Minimum severity to fail: `low`, `moderate`, `high`, `critical` |
| `--composer-bin` | `composer` | Path to composer executable |

## Requirements

- PHP 8.1 or higher
- Composer 2.x
- A `composer.lock` file in your project

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

Contributions welcome! Please feel free to submit a Pull Request.