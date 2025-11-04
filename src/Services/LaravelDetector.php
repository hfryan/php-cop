<?php
namespace PHPCop\Services;

/**
 * Laravel Framework Detector
 *
 * Detects if the project is a Laravel application and provides
 * Laravel-specific information for enhanced security scanning.
 */
final class LaravelDetector
{
    private string $projectPath;
    private ?array $composerJson = null;
    private ?bool $isLaravel = null;

    /**
     * Known Laravel ecosystem packages
     */
    private const LARAVEL_PACKAGES = [
        'laravel/framework',
        'laravel/sanctum',
        'laravel/passport',
        'laravel/horizon',
        'laravel/telescope',
        'laravel/nova',
        'laravel/cashier',
        'laravel/scout',
        'laravel/socialite',
        'laravel/tinker',
        'laravel/sail',
        'laravel/breeze',
        'laravel/jetstream',
        'laravel/fortify',
        'laravel/octane',
        'livewire/livewire',
        'inertiajs/inertia-laravel',
        'spatie/laravel-permission',
        'spatie/laravel-backup',
        'spatie/laravel-medialibrary',
        'barryvdh/laravel-debugbar',
    ];

    public function __construct(string $projectPath = '.')
    {
        $this->projectPath = rtrim($projectPath, '/\\');
    }

    /**
     * Check if the current project is a Laravel application
     */
    public function isLaravelProject(): bool
    {
        if ($this->isLaravel !== null) {
            return $this->isLaravel;
        }

        // Method 1: Check for artisan file (most reliable)
        if (file_exists($this->projectPath . '/artisan')) {
            $this->isLaravel = true;
            return true;
        }

        // Method 2: Check composer.json for laravel/framework
        $composerJson = $this->getComposerJson();
        if ($composerJson) {
            $allDeps = array_merge(
                $composerJson['require'] ?? [],
                $composerJson['require-dev'] ?? []
            );

            if (isset($allDeps['laravel/framework'])) {
                $this->isLaravel = true;
                return true;
            }
        }

        $this->isLaravel = false;
        return false;
    }

    /**
     * Get Laravel framework version from composer.lock
     */
    public function getLaravelVersion(): ?string
    {
        if (!$this->isLaravelProject()) {
            return null;
        }

        $lockPath = $this->projectPath . '/composer.lock';
        if (!file_exists($lockPath)) {
            return null;
        }

        try {
            $lock = json_decode(file_get_contents($lockPath), true, 512, JSON_THROW_ON_ERROR);
            $packages = array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []);

            foreach ($packages as $package) {
                if ($package['name'] === 'laravel/framework') {
                    return ltrim($package['version'], 'v');
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Get Laravel major version (e.g., 8, 9, 10, 11)
     */
    public function getLaravelMajorVersion(): ?int
    {
        $version = $this->getLaravelVersion();
        if (!$version) {
            return null;
        }

        // Extract major version from version string (e.g., "11.35.1" -> 11)
        preg_match('/^(\d+)\./', $version, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    /**
     * Check if Laravel version is end-of-life
     */
    public function isLaravelVersionEOL(): bool
    {
        $majorVersion = $this->getLaravelMajorVersion();
        if (!$majorVersion) {
            return false;
        }

        // Laravel EOL versions as of October 2025
        // Laravel 6, 7, 8, 9 are EOL
        // Laravel 10 supported until August 2025 (recently EOL)
        return $majorVersion < 11;
    }

    /**
     * Get Laravel ecosystem packages from composer.lock
     */
    public function getLaravelPackages(array $allPackages): array
    {
        $laravelPackages = [];

        foreach ($allPackages as $package) {
            $name = $package['name'] ?? '';

            // Check if it's a known Laravel package
            if (in_array($name, self::LARAVEL_PACKAGES, true)) {
                $laravelPackages[] = $package;
                continue;
            }

            // Also check for packages that start with 'laravel/'
            if (str_starts_with($name, 'laravel/')) {
                $laravelPackages[] = $package;
            }
        }

        return $laravelPackages;
    }

    /**
     * Check if a specific package is a Laravel ecosystem package
     */
    public function isLaravelPackage(string $packageName): bool
    {
        return in_array($packageName, self::LARAVEL_PACKAGES, true)
            || str_starts_with($packageName, 'laravel/');
    }

    /**
     * Get Laravel-specific security recommendations
     */
    public function getSecurityRecommendations(): array
    {
        $recommendations = [];

        if (!$this->isLaravelProject()) {
            return $recommendations;
        }

        // Check for .env file in git
        $gitignorePath = $this->projectPath . '/.gitignore';
        if (file_exists($gitignorePath)) {
            $gitignore = file_get_contents($gitignorePath);
            if (strpos($gitignore, '.env') === false) {
                $recommendations[] = '⚠️  Add .env to .gitignore to prevent leaking APP_KEY';
            }
        }

        // Check if Laravel version is EOL
        if ($this->isLaravelVersionEOL()) {
            $version = $this->getLaravelVersion();
            $recommendations[] = "🚨 Laravel {$version} is End-of-Life - upgrade to Laravel 11+";
        }

        return $recommendations;
    }

    /**
     * Get project type label for output
     */
    public function getProjectTypeLabel(): string
    {
        if (!$this->isLaravelProject()) {
            return 'PHP Project';
        }

        $version = $this->getLaravelVersion();
        return $version ? "Laravel {$version}" : 'Laravel Project';
    }

    /**
     * Read and cache composer.json
     */
    private function getComposerJson(): ?array
    {
        if ($this->composerJson !== null) {
            return $this->composerJson;
        }

        $composerPath = $this->projectPath . '/composer.json';
        if (!file_exists($composerPath)) {
            $this->composerJson = [];
            return null;
        }

        try {
            $this->composerJson = json_decode(
                file_get_contents($composerPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            return $this->composerJson;
        } catch (\Exception $e) {
            $this->composerJson = [];
            return null;
        }
    }

    /**
     * Get Laravel-specific vulnerability context
     */
    public function getVulnerabilityContext(string $packageName): ?string
    {
        // Add context for known Laravel package vulnerabilities
        $contexts = [
            'livewire/livewire' => 'Critical: Check for Livewire v3 RCE vulnerability (CVE-2025-54068)',
            'laravel/framework' => 'Check for environment variable manipulation (CVE-2024-52301)',
            'laravel/sanctum' => 'Ensure proper token validation and security settings',
            'laravel/passport' => 'Verify OAuth2 implementation security',
        ];

        return $contexts[$packageName] ?? null;
    }
}
