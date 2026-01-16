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
- 🔥 **Laravel Integration** - Automatic Laravel detection with framework-specific security recommendations
- 📊 **Multiple Output Formats** - Table, JSON, Markdown, and HTML output
- 🎯 **Advanced Filtering** - Filter by dependency type, licenses, and vulnerability severity
- 🎚️ **Configurable Thresholds** - Set custom severity levels and staleness periods
- ⚡ **High Performance** - Parallel API calls and intelligent caching for fast scans
- 🚀 **CI/CD Ready** - Returns appropriate exit codes for automation

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

### Method 3: PHAR Download (Recommended for CI/CD)

Download the latest PHAR release for zero-dependency deployment:

```bash
# Download from GitHub releases
wget https://github.com/hfryan/php-cop/releases/latest/download/phpcop.phar
chmod +x phpcop.phar

# Run directly
php phpcop.phar scan

# Or make it executable (Linux/macOS)
./phpcop.phar scan
```

**Benefits:**
- ✅ No Composer or dependencies required
- ✅ Single file deployment
- ✅ Perfect for CI/CD pipelines
- ✅ Works in Docker containers
- ✅ Consistent across environments

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

# Only scan dev dependencies
phpcop scan --only-dev

# Exclude dev dependencies from scan  
phpcop scan --exclude-dev

# Filter by allowed licenses
phpcop scan --license-allowlist=MIT,Apache-2.0

# Exclude packages with specific licenses
phpcop scan --license-denylist=GPL-3.0

# Only show critical vulnerabilities
phpcop scan --min-severity=critical

# Disable response caching (force fresh API calls)
phpcop scan --no-cache

# Use legacy exit codes for backwards compatibility
phpcop scan --exit-code=legacy
```

## CI/CD Integration 🚀

PHPCop is designed for seamless CI/CD integration with intelligent exit codes and automation-friendly features.

### Enhanced Exit Codes (Default)

PHPCop uses granular exit codes to provide precise information for automated pipelines:

```bash
0 = SUCCESS   - No issues found, all dependencies secure
1 = WARNINGS  - Minor issues (stale packages, low-severity vulnerabilities)  
2 = ERRORS    - Moderate issues (outdated packages, abandoned dependencies, moderate vulnerabilities)
3 = CRITICAL  - High-priority issues (high/critical security vulnerabilities)
```

### CI/CD Examples

```bash
# Basic CI check - fail on any vulnerabilities
phpcop scan --fail-on=low --quiet
echo "Exit code: $?"

# Production deployment - fail only on high/critical vulnerabilities
phpcop scan --fail-on=high --format=json > security-report.json
echo "Exit code: $?"

# Security-focused scan - exclude dev dependencies
phpcop scan --exclude-dev --min-severity=moderate --exit-code=enhanced
echo "Exit code: $?"

# Legacy compatibility mode (simple 0/1 exit codes)
phpcop scan --exit-code=legacy --fail-on=high
echo "Exit code: $?"
```

### GitHub Actions Integration

PHPCop provides a pre-built GitHub Action for seamless CI/CD integration:

#### Quick Setup (Recommended)

```yaml
name: Security Scan
on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Install Dependencies
        run: composer install --no-dev
        
      - name: PHPCop Security Scan
        uses: hfryan/php-cop@main
        with:
          fail-on: 'high'
          exclude-dev: true
          comment-pr: true
```

#### Advanced Configuration

```yaml
- name: Comprehensive Security Scan
  uses: hfryan/php-cop@main
  with:
    format: 'json'
    fail-on: 'moderate'
    stale-months: 12
    min-severity: 'moderate'
    exclude-dev: true
    ignore-packages: 'symfony/polyfill-*,psr/log'
    license-allowlist: 'MIT,Apache-2.0,BSD-3-Clause'
    comment-pr: true
    upload-artifacts: true
    working-directory: './backend'
```

#### Action Inputs

| Input | Default | Description |
|-------|---------|-------------|
| `format` | `table` | Output format: `table`, `json`, `md`, `html` |
| `fail-on` | `high` | Minimum severity to fail: `low`, `moderate`, `high`, `critical` |
| `stale-months` | `18` | Months to flag packages as stale |
| `exclude-dev` | `false` | Exclude dev dependencies from scan |
| `only-dev` | `false` | Only scan dev dependencies |
| `min-severity` | `low` | Minimum vulnerability severity to report |
| `ignore-packages` | `''` | Comma-separated packages to ignore |
| `license-allowlist` | `''` | Comma-separated allowed licenses |
| `license-denylist` | `''` | Comma-separated denied licenses |
| `exit-code` | `enhanced` | Exit code behavior: `legacy`, `enhanced` |
| `comment-pr` | `true` | Post scan results as PR comment |
| `upload-artifacts` | `true` | Upload reports as artifacts |
| `working-directory` | `.` | Directory to run scan in |

#### Action Outputs

| Output | Description |
|--------|-------------|
| `exit-code` | The exit code from PHPCop scan |
| `issues-found` | Number of issues found |
| `vulnerabilities-found` | Number of vulnerabilities found |
| `report-file` | Path to the generated report file |

#### Using Outputs

```yaml
- name: PHPCop Scan
  id: phpcop
  uses: hfryan/php-cop@main
  with:
    fail-on: 'critical'
    
- name: Handle Results
  run: |
    echo "Exit code: ${{ steps.phpcop.outputs.exit-code }}"
    echo "Issues found: ${{ steps.phpcop.outputs.issues-found }}"
    echo "Vulnerabilities: ${{ steps.phpcop.outputs.vulnerabilities-found }}"
```

#### Manual PHAR Download (Alternative)

```yaml
- name: Security Scan
  run: |
    wget https://github.com/hfryan/php-cop/releases/latest/download/phpcop.phar
    php phpcop.phar scan --format=json --quiet
    
- name: Handle Security Results
  if: ${{ failure() }}
  run: echo "Security issues found - check the logs"
```

### Docker Integration

```dockerfile
RUN wget https://github.com/hfryan/php-cop/releases/latest/download/phpcop.phar && \
    php phpcop.phar scan --exclude-dev --fail-on=high
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

## Laravel Integration 🔥

PHPCop automatically detects Laravel projects and provides framework-specific security insights!

### Automatic Detection

PHPCop detects Laravel projects automatically by looking for:
- `artisan` file in project root
- `laravel/framework` in composer dependencies

When a Laravel project is detected, PHPCop provides:

### Laravel-Specific Features

**🎯 Framework Version Display**
```bash
🚓 PHP Cop: Dependency Patrol — Case File
Project Type: Laravel 11.35.1
```

**🔥 Laravel Package Highlighting**
Laravel ecosystem packages are highlighted with a 🔥 badge:
- `laravel/framework`
- `laravel/sanctum`, `laravel/passport`
- `livewire/livewire`
- `laravel/horizon`, `laravel/telescope`
- And more!

**⚠️ Laravel Security Recommendations**
Automatic security checks for common Laravel issues:
- **.env file protection** - Warns if .env might be committed to git
- **EOL version detection** - Flags Laravel 10 and earlier as end-of-life
- **Critical CVE awareness** - Highlights known Laravel vulnerabilities:
  - CVE-2025-54068 (Livewire v3 RCE)
  - CVE-2024-52301 (Environment variable manipulation)

**📦 Laravel Package Context**
Get specific security guidance for Laravel packages:
```bash
• livewire/livewire 3.5.0  [⬆️ Outdated → 3.6.4] 🔥 Laravel
   ℹ️  Critical: Check for Livewire v3 RCE vulnerability (CVE-2025-54068)
```

### Example Output

**For a Laravel 11 Project:**
```bash
🚓 PHP Cop: Dependency Patrol — Case File
Project Type: Laravel 11.35.1

Laravel Security Recommendations:
  🚨 Add .env to .gitignore to prevent leaking APP_KEY

--------------------------------------------------------------------------------
• laravel/framework 11.35.0  [⬆️ Outdated → 11.35.1] 🔥 Laravel
• livewire/livewire 3.5.0  [⬆️ Outdated → 3.6.4] 🔥 Laravel
   ℹ️  Critical: Check for Livewire v3 RCE vulnerability (CVE-2025-54068)
```

**JSON Output with Laravel Data:**
```json
{
  "generatedAt": "2025-10-21T14:30:00Z",
  "projectType": "Laravel 11.35.1",
  "isLaravel": true,
  "laravelVersion": "11.35.1",
  "laravelRecommendations": [
    "Add .env to .gitignore to prevent leaking APP_KEY"
  ],
  "issues": [...]
}
```

### Laravel Best Practices

PHPCop helps enforce Laravel security best practices:
- ✅ Keep Laravel framework updated
- ✅ Monitor Laravel ecosystem packages (Livewire, Sanctum, etc.)
- ✅ Prevent APP_KEY leaks
- ✅ Stay on supported Laravel versions (11+)
- ✅ Watch for framework-specific CVEs

## Advanced Filtering 🎯

PHPCop provides powerful filtering options to focus your security analysis:

### Dependency Type Filtering
```bash
# Scan only development dependencies
phpcop scan --only-dev

# Exclude development dependencies (production only)
phpcop scan --exclude-dev
```

### License Filtering
```bash
# Only scan packages with specific licenses
phpcop scan --license-allowlist=MIT,Apache-2.0,BSD-3-Clause

# Exclude packages with unwanted licenses
phpcop scan --license-denylist=GPL-3.0,AGPL-3.0

# Combine with other options
phpcop scan --exclude-dev --license-allowlist=MIT --format=json
```

### Vulnerability Severity Filtering
```bash
# Show only high and critical vulnerabilities
phpcop scan --min-severity=high

# Focus on critical issues only
phpcop scan --min-severity=critical --format=html > critical-report.html
```

### Combined Filtering Examples
```bash
# Production security audit: exclude dev deps, only critical vulns
phpcop scan --exclude-dev --min-severity=critical

# License compliance check: only MIT/Apache packages, exclude dev deps
phpcop scan --exclude-dev --license-allowlist=MIT,Apache-2.0

# Development workflow: dev packages only, moderate+ vulnerabilities
phpcop scan --only-dev --min-severity=moderate --format=md > dev-security.md
```

## Performance & Caching ⚡

PHPCop is optimized for speed with intelligent caching and parallel processing:

### Parallel API Calls
- **Concurrent requests** - Fetches package data in parallel instead of sequentially
- **Significant speedup** - 10-50x faster for projects with many dependencies
- **Automatic batching** - Groups API calls for maximum efficiency

### Intelligent Caching
- **Multi-level cache** - Memory cache + persistent file cache
- **Smart TTL** - 1-hour default cache lifetime (configurable)
- **Automatic cleanup** - Expired cache files are removed automatically
- **Cross-run persistence** - Subsequent scans use cached data for speed

### Cache Control
```bash
# Disable caching for fresh data
phpcop scan --no-cache

# Configure cache in .phpcop.json
{
  "cache-enabled": true,
  "cache-ttl": 1800  // 30 minutes
}
```

**Cache Location:** `{system-temp}/phpcop-cache/`

### Performance Tips
- **First run**: May take longer as cache is populated
- **Subsequent runs**: Near-instant for unchanged dependencies
- **CI environments**: Consider `--no-cache` for fresh builds
- **Development**: Keep caching enabled for faster iteration

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
  ],
  "dependency-type": "exclude-dev",
  "license-allowlist": ["MIT", "Apache-2.0"],
  "license-denylist": ["GPL-3.0"],
  "min-severity": "moderate",
  "cache-enabled": true,
  "cache-ttl": 3600
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
| `--only-dev` | `false` | Only scan dev dependencies |
| `--exclude-dev` | `false` | Exclude dev dependencies from scan |
| `--license-allowlist` | `[]` | Comma-separated list of allowed licenses |
| `--license-denylist` | `[]` | Comma-separated list of denied licenses |
| `--min-severity` | `low` | Minimum vulnerability severity: `low`, `moderate`, `high`, `critical` |
| `--no-cache` | `false` | Disable response caching (force fresh API calls) |
| `--exit-code` | `enhanced` | Exit code behavior: `legacy`, `enhanced` |

**Note:** Command-line options override configuration file settings.

## Requirements

- PHP 8.3 or higher
- Composer 2.x
- A `composer.lock` file in your project

## Building from Source 🔧

### Building the PHAR

To build your own PHAR archive:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Build PHAR (requires phar.readonly=0)
php -d phar.readonly=0 build-phar.php

# Or use make (if available)
make phar
```

The generated `phpcop.phar` file is self-contained and can be distributed independently.

### Development Commands

```bash
# Install dev dependencies
composer install

# Run from source
php bin/phpcop.php scan

# Build PHAR
make phar

# Clean build artifacts  
make clean
```

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