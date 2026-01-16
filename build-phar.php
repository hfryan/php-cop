<?php
/**
 * PHPCop PHAR Build Script
 * 
 * This script creates a self-contained PHAR archive of PHPCop
 * that can be distributed and run without requiring Composer or dependencies.
 */

declare(strict_types=1);

// Enable PHAR creation
if (!Phar::canWrite()) {
    echo "❌ PHAR creation is disabled. Run: php -d phar.readonly=0 build-phar.php\n";
    exit(1);
}

$buildDir = __DIR__;
$pharFile = $buildDir . '/phpcop.phar';
$stubFile = $buildDir . '/bin/phpcop.php';

// Remove existing PHAR if it exists
if (file_exists($pharFile)) {
    unlink($pharFile);
    echo "🗑️  Removed existing PHAR file\n";
}

echo "🔨 Building PHPCop PHAR...\n";

try {
    // Create PHAR archive
    $phar = new Phar($pharFile);
    $phar->startBuffering();

    // Set metadata
    $version = getVersion();
    echo "📋 Using version: $version\n";
    $phar->setMetadata([
        'name' => 'PHPCop',
        'version' => $version,
        'built' => date('Y-m-d H:i:s T'),
    ]);

    // Add source files
    echo "📦 Adding source files...\n";
    $phar->buildFromDirectory($buildDir, getFileFilter());

    // Set stub (entry point)
    echo "🚀 Setting entry point...\n";
    $stub = getStub();
    $phar->setStub($stub);

    $phar->stopBuffering();

    // Make executable
    chmod($pharFile, 0755);

    echo "✅ PHAR created successfully: " . basename($pharFile) . "\n";
    echo "📊 Size: " . formatBytes(filesize($pharFile)) . "\n";
    echo "🧪 Testing PHAR...\n";

    // Test the PHAR
    $testOutput = shell_exec("php \"$pharFile\" --version 2>&1");
    if (str_contains($testOutput, 'PHP Cop')) {
        echo "✅ PHAR test successful\n";
        echo "🎉 Build complete! Use: php phpcop.phar scan\n";
    } else {
        echo "❌ PHAR test failed:\n$testOutput\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ Build failed: " . $e->getMessage() . "\n";
    exit(1);
}

function getVersion(): string
{
    // Try different approaches to get git version
    $gitVersion = null;
    
    // Method 1: Direct git command
    if (function_exists('exec')) {
        $output = [];
        $returnVar = 0;
        exec('git describe --tags --always 2>&1', $output, $returnVar);
        if ($returnVar === 0 && !empty($output)) {
            $gitVersion = trim(implode(' ', $output));
            if ($gitVersion && !str_contains($gitVersion, 'not recognized') && !str_contains($gitVersion, 'fatal')) {
                return $gitVersion;
            }
        }
    }
    
    // Method 2: shell_exec with different commands
    $gitCommands = [
        'git describe --tags --always',
        'git describe --tags --always 2>/dev/null',
        'git describe --tags --always 2>nul',
    ];
    
    foreach ($gitCommands as $cmd) {
        $gitVersion = trim(shell_exec($cmd) ?? '');
        if ($gitVersion && $gitVersion !== '' && !str_contains($gitVersion, 'not recognized') && !str_contains($gitVersion, 'fatal')) {
            return $gitVersion;
        }
    }

    // Fallback to composer.json version
    $composerFile = __DIR__ . '/composer.json';
    if (file_exists($composerFile)) {
        $composer = json_decode(file_get_contents($composerFile), true);
        if (isset($composer['version'])) {
            return $composer['version'];
        }
    }

    return 'dev-' . date('Y-m-d');
}

function getFileFilter(): string
{
    // Regex to include only necessary files
    return '~^(?!.*(?:
        \.git/|
        \.idea/|
        \.claude/|
        /tests?/|
        /test/|
        build-phar\.php|
        Makefile|
        \.gitignore|
        \.phpcop\.json\.example|
        CLAUDE\.md|
        phpcop\.png|
        README\.md|
        LICENSE
    )).*\.(php|json|txt|md)$~x';
}

function getStub(): string
{
    return <<<'STUB'
#!/usr/bin/env php
<?php
/**
 * PHPCop PHAR - Dependency Patrol
 */

// Check PHP version
if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    fwrite(STDERR, "PHPCop requires PHP 8.3 or higher. You are running " . PHP_VERSION . "\n");
    exit(1);
}

Phar::mapPhar('phpcop.phar');

// Set up autoloader
require_once 'phar://phpcop.phar/vendor/autoload.php';

// Run the application
require_once 'phar://phpcop.phar/bin/phpcop.php';

__HALT_COMPILER();
STUB;
}

function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}