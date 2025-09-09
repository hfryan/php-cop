# PHPCop 🚓

<p align="center">
  <img src="phpcop.png" alt="PHPCop Logo" width="300">
</p>

<p align="center">
  <strong>Dependency Patrol — PHP Security Scanner</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/hfryan/php-cop">
    <img src="https://img.shields.io/packagist/v/hfryan/php-cop?style=flat-square&logo=packagist&logoColor=white" alt="Latest Version">
  </a>
  <a href="https://packagist.org/packages/hfryan/php-cop">
    <img src="https://img.shields.io/packagist/dt/hfryan/php-cop?style=flat-square&logo=packagist&logoColor=white" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/hfryan/php-cop">
    <img src="https://img.shields.io/packagist/php-v/hfryan/php-cop?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
  </a>
  <a href="https://github.com/hfryan/php-cop/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/hfryan/php-cop?style=flat-square" alt="License">
  </a>
  <a href="https://github.com/hfryan/php-cop/releases">
    <img src="https://img.shields.io/github/v/release/hfryan/php-cop?style=flat-square&logo=github" alt="GitHub Release">
  </a>
  <a href="https://github.com/hfryan/php-cop">
    <img src="https://img.shields.io/github/stars/hfryan/php-cop?style=flat-square&logo=github" alt="GitHub Stars">
  </a>
</p>

**PHPCop** is a powerful PHP security scanner that analyzes your `composer.lock` file to identify vulnerabilities, outdated packages, and maintenance issues in your dependencies. Keep your applications secure with comprehensive dependency health monitoring.

## Why PHPCop? 🤔

- **🛡️ Security First** - Detect known CVEs and security vulnerabilities before they impact your application
- **📊 Professional Reports** - Generate beautiful HTML and Markdown reports for stakeholders  
- **⚙️ CI/CD Ready** - Perfect exit codes and quiet modes for automated pipelines
- **🎯 Zero Configuration** - Works out of the box, configure only what you need
- **🚀 Fast & Efficient** - Minimal overhead with intelligent caching and parallel processing
- **👥 Team Friendly** - Share security policies via committed configuration files

## Features

- 🚨 **Security Vulnerability Detection** - Scans for known CVEs using `composer audit`
- ⬆️ **Outdated Package Detection** - Identifies packages with newer versions available
- 🚫 **Abandoned Package Detection** - Flags packages that are no longer maintained
- ⌛ **Stale Package Detection** - Finds packages that haven't been updated in months
- 📊 **Multiple Output Formats** - Table, JSON, Markdown, and HTML output
- 🎯 **Configurable Thresholds** - Set custom severity levels and staleness periods
- ⚡ **CI/CD Ready** - Returns appropriate exit codes for automation

## Quick Start 🚀

```bash
# Install PHPCop globally
composer global require hfryan/php-cop

# Scan your project (run in any PHP project directory)
phpcop scan

# Generate a beautiful HTML report
phpcop scan --format=html > security-report.html
```

That's it! PHPCop will analyze your `composer.lock` and show you any security issues, outdated packages, or maintenance concerns.

## Installation

### Method 1: Global Installation (Recommended for regular use)

```bash
# Install globally
composer global require hfryan/php-cop

# Run the setup helper to configure PATH
php ~/.composer/vendor/hfryan/php-cop/bin/phpcop.php setup
```

**Alternative manual setup:** If you prefer to configure PATH manually:

**On macOS/Linux:**
```bash
# Add to your shell profile (~/.zshrc, ~/.bash_profile, etc.)
export PATH="$HOME/.composer/vendor/bin:$PATH"

# Then restart your terminal or run:
source ~/.zshrc

# Now you can use:
phpcop.php scan
```

**On Windows:**
```bash
# Add this directory to your Windows PATH environment variable:
C:\Users\{YourUsername}\AppData\Roaming\Composer\vendor\bin

# Or use the full path directly:
php C:\Users\{YourUsername}\AppData\Roaming\Composer\vendor\hfryan\php-cop\bin\phpcop.php scan
```

### Method 2: Per-Project Installation (Simplest)

```bash
# Install in your PHP project
composer require --dev hfryan/php-cop

# Run from project directory
php vendor/bin/phpcop.php scan
```

### Method 3: Direct Download

Download the latest release and run directly:
```bash
php phpcop.phar scan
```

## Usage

### Basic Scan
```bash
phpcop scan
```

### Output Formats
```bash
# Terminal-friendly table (default)
phpcop scan --format=table

# JSON for automation/CI
phpcop scan --format=json

# Markdown for documentation
phpcop scan --format=md > security-report.md

# HTML for web viewing
phpcop scan --format=html > report.html
```

### Custom Options
```bash
# Custom staleness threshold (12 months)
phpcop scan --stale-months=12

# Fail on moderate vulnerabilities instead of high
phpcop scan --fail-on=moderate

# Ignore specific packages
phpcop scan --ignore-packages=vendor/package,psr/log

# Use custom config file
phpcop scan --config=custom-config.json

# Silent mode for automation
phpcop scan --quiet
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

## Configuration

### Configuration File

Create a `.phpcop.json` file in your project root for persistent settings:

```json
{
  "format": "table",
  "stale-months": 18,
  "fail-on": "high",
  "composer-bin": "composer",
  "quiet": false,
  "ignore-packages": [
    "symfony/polyfill-*",
    "psr/log"
  ]
}
```

### Command Options

| Option | Default | Description |
|--------|---------|-------------|
| `--format` | `table` | Output format: `table`, `json`, `md`, `html` |
| `--stale-months` | `18` | Months to flag packages as stale |
| `--fail-on` | `high` | Minimum severity to fail: `low`, `moderate`, `high`, `critical` |
| `--composer-bin` | `composer` | Path to composer executable |
| `--quiet`, `-q` | `false` | Disable progress bar and animations |
| `--config`, `-c` | `.phpcop.json` | Path to configuration file |
| `--ignore-packages` | `[]` | Comma-separated packages to ignore |

**Note:** Command-line options override configuration file settings.

## Requirements

- PHP 8.1 or higher
- Composer 2.x
- A `composer.lock` file in your project

## Contributing 🤝

We welcome contributions! Here's how you can help:

- **🐛 Bug Reports** - [Open an issue](https://github.com/hfryan/php-cop/issues) with details and reproduction steps
- **💡 Feature Requests** - Share your ideas for new functionality
- **🔧 Code Contributions** - Submit a pull request with your improvements
- **📖 Documentation** - Help improve our docs and examples
- **🌟 Spread the Word** - Star the repo, share with colleagues, write blog posts

## Support

- **📚 Documentation** - Check our comprehensive [README](README.md) and examples
- **🐛 Issues** - Report bugs on [GitHub Issues](https://github.com/hfryan/php-cop/issues)
- **💬 Discussions** - Join conversations in [GitHub Discussions](https://github.com/hfryan/php-cop/discussions)
- **📦 Packagist** - View package details on [Packagist](https://packagist.org/packages/hfryan/php-cop)

## License

Released under the [MIT License](LICENSE). Free for personal and commercial use.

---

<p align="center">
  <strong>Built with ❤️ for the PHP community</strong><br>
  <em>Keep your dependencies secure, one scan at a time! 🚓</em>
</p>