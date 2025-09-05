# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHPCop is a PHP CLI security scanner that analyzes `composer.lock` files to identify vulnerabilities, outdated packages, abandoned dependencies, and stale packages. It integrates with Packagist API and `composer audit` to provide comprehensive dependency health reports.

## Development Commands

### Running the Tool
```bash
# On Windows, use cmd for PHP commands
cmd //c "php .\bin\phpcop.php scan --format=table"

# Available options
--format=table|json|md|html    # Output format (default: table)
--stale-months=N               # Flag packages as stale after N months (default: 18)
--fail-on=low|moderate|high|critical  # Exit code threshold (default: high)
--composer-bin=composer        # Composer executable path
```

### Testing the Tool
```bash
# Test with different output formats
cmd //c "php .\bin\phpcop.php scan --format=json"
cmd //c "php .\bin\phpcop.php scan --stale-months=12"
```

### Package Management
```bash
composer install              # Install dependencies
composer update               # Update dependencies
composer dump-autoload        # Regenerate autoloader
```

## Architecture

### Core Components

**Console Layer** (`src/Console/`):
- `Application.php`: Symfony Console application setup with command registration
- `ScanCommand.php`: Main scan command with option parsing and output formatting

**Service Layer** (`src/Services/`):
- `ComposerReader.php`: Reads and parses `composer.lock` files
- `PackagistClient.php`: HTTP client for Packagist API v2 (p2 compact format)
- `AuditRunner.php`: Executes `composer audit` via Symfony Process component

### Data Flow
1. `ComposerReader` extracts package list from `composer.lock`
2. `AuditRunner` fetches security advisories via `composer audit --format=json`
3. `PackagistClient` queries package metadata from Packagist API
4. `ScanCommand` correlates data to identify issues:
   - Outdated: Version mismatches against Packagist latest
   - Abandoned: Flagged by package maintainers
   - Stale: No updates within configurable timeframe
   - Vulnerable: Security advisories from composer audit

### Key Design Patterns
- **Service-oriented**: Clear separation between data access and business logic
- **Dependency injection**: Services accept optional HTTP client for testing
- **Exit codes**: Command returns failure status based on vulnerability severity thresholds

## Important Implementation Details

- **Command Name**: Always set command name explicitly in `configure()` method using `->setName()` - the static `$defaultName` property alone is insufficient
- **Windows Compatibility**: Use `cmd //c` wrapper for PHP commands in development environment
- **API Format**: Uses Packagist p2 compact format for efficiency
- **Version Comparison**: Relies on `composer/semver` for proper semantic version handling
- **Error Handling**: Graceful degradation when services are unavailable

## Environment Requirements

- PHP 8.1+
- Composer 2.x
- Internet access for Packagist API and security advisories
- Windows development environment with Herd PHP manager