# PHPCop GitHub Action 🚓

A GitHub Action for running [PHPCop](https://github.com/hfryan/php-cop) security scans on your PHP projects. PHPCop analyzes your `composer.lock` file to identify vulnerabilities, outdated packages, abandoned dependencies, and stale packages.

## Features

- 🛡️ **Security First** - Detect known CVEs and security vulnerabilities
- 📊 **Professional Reports** - Generate beautiful reports in multiple formats
- 🚀 **CI/CD Ready** - Intelligent exit codes and automation-friendly output
- 💬 **PR Comments** - Automatic pull request comments with scan results
- 📁 **Artifact Upload** - Save detailed reports for later review
- ⚙️ **Highly Configurable** - Extensive customization options

## Quick Start

Add this to your `.github/workflows/security.yml`:

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
        uses: hfryan/php-cop@v1
        with:
          fail-on: 'high'
          exclude-dev: true
```

## Inputs

### Core Options

| Input | Required | Default | Description |
|-------|----------|---------|-------------|
| `format` | No | `table` | Output format: `table`, `json`, `md`, `html` |
| `fail-on` | No | `high` | Minimum severity to fail: `low`, `moderate`, `high`, `critical` |
| `stale-months` | No | `18` | Months to flag packages as stale |
| `min-severity` | No | `low` | Minimum vulnerability severity to report |
| `exit-code` | No | `enhanced` | Exit code behavior: `legacy`, `enhanced` |

### Filtering Options

| Input | Required | Default | Description |
|-------|----------|---------|-------------|
| `exclude-dev` | No | `false` | Exclude dev dependencies from scan |
| `only-dev` | No | `false` | Only scan dev dependencies |
| `ignore-packages` | No | `''` | Comma-separated packages to ignore |
| `license-allowlist` | No | `''` | Comma-separated allowed licenses |
| `license-denylist` | No | `''` | Comma-separated denied licenses |

### GitHub Integration

| Input | Required | Default | Description |
|-------|----------|---------|-------------|
| `comment-pr` | No | `true` | Post scan results as PR comment |
| `upload-artifacts` | No | `true` | Upload reports as artifacts |
| `working-directory` | No | `.` | Directory to run scan in |

## Outputs

| Output | Description |
|--------|-------------|
| `exit-code` | The exit code from PHPCop scan (0=success, 1=warnings, 2=errors, 3=critical) |
| `issues-found` | Number of issues found |
| `vulnerabilities-found` | Number of vulnerabilities found |
| `report-file` | Path to the generated report file |

## Examples

### Basic Security Scan

```yaml
- name: Security Scan
  uses: hfryan/php-cop@v1
  with:
    fail-on: 'high'
    exclude-dev: true
```

### Production Deployment Check

```yaml
- name: Production Security Check
  uses: hfryan/php-cop@v1
  with:
    fail-on: 'moderate'
    exclude-dev: true
    min-severity: 'moderate'
    license-allowlist: 'MIT,Apache-2.0,BSD-3-Clause'
    upload-artifacts: true
```

### Development Dependencies Audit

```yaml
- name: Dev Dependencies Audit
  uses: hfryan/php-cop@v1
  with:
    only-dev: true
    fail-on: 'critical'
    comment-pr: false
  continue-on-error: true
```

### Multi-Project Monorepo

```yaml
strategy:
  matrix:
    project: [frontend, backend, api]
    
steps:
  - name: Scan ${{ matrix.project }}
    uses: hfryan/php-cop@v1
    with:
      working-directory: ./${{ matrix.project }}
      fail-on: 'high'
```

### Using Outputs

```yaml
- name: PHPCop Scan
  id: security
  uses: hfryan/php-cop@v1
  with:
    fail-on: 'critical'
    
- name: Handle Results
  run: |
    echo "Exit code: ${{ steps.security.outputs.exit-code }}"
    echo "Issues: ${{ steps.security.outputs.issues-found }}"
    echo "Vulnerabilities: ${{ steps.security.outputs.vulnerabilities-found }}"
    
- name: Notify on Critical Issues
  if: steps.security.outputs.exit-code == '3'
  run: echo "🚨 Critical security vulnerabilities found!"
```

## Exit Codes

PHPCop uses intelligent exit codes for precise CI/CD control:

- **0 (Success)** - No issues found
- **1 (Warnings)** - Minor issues (stale packages, low vulnerabilities)
- **2 (Errors)** - Moderate issues (outdated packages, abandoned dependencies)
- **3 (Critical)** - High-priority security vulnerabilities

## Requirements

- PHP 8.3+ (automatically provided by the action)
- `composer.lock` file in your project
- Composer dependencies installed

## License

This action is distributed under the MIT License. See [LICENSE](../../../LICENSE) for details.

## Support

- 📚 **Documentation**: [PHPCop README](https://github.com/hfryan/php-cop)
- 🐛 **Issues**: [GitHub Issues](https://github.com/hfryan/php-cop/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/hfryan/php-cop/discussions)