<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Application as Base;
use PHPCop\Console\ScanCommand;
use PHPCop\Console\SetupCommand;

final class Application extends Base {
    public function __construct(){
        parent::__construct('PHP Cop - Dependency Patrol', $this->getVersion());
        $this->add(new ScanCommand());
        $this->add(new SetupCommand());
    }

    public function getVersion(): string
    {
        // If running from PHAR, get version from PHAR metadata
        if (\Phar::running()) {
            try {
                $phar = new \Phar(\Phar::running(false));
                $metadata = $phar->getMetadata();
                if (is_array($metadata) && isset($metadata['version'])) {
                    return $metadata['version'];
                }
            } catch (\Exception $e) {
                // Fallback if metadata reading fails
            }
        }

        // Try to get version from git tag first
        $gitVersion = $this->getGitVersion();
        if ($gitVersion) {
            return $gitVersion;
        }

        // Fallback to composer.json version
        $composerVersion = $this->getComposerVersion();
        if ($composerVersion) {
            return $composerVersion;
        }

        // Final fallback
        return 'dev-' . date('Y-m-d');
    }

    private function getGitVersion(): ?string
    {
        if (function_exists('shell_exec')) {
            $version = trim(shell_exec('git describe --tags --always 2>/dev/null') ?? '');
            if ($version && $version !== '' && !str_contains($version, 'fatal')) {
                return $version;
            }
        }
        return null;
    }

    private function getComposerVersion(): ?string
    {
        $composerFile = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            if ($content) {
                $composer = json_decode($content, true);
                if (isset($composer['version'])) {
                    return $composer['version'];
                }
            }
        }
        return null;
    }

    protected function getDefaultCommands(): array
    {
        // Only include core commands, not the problematic DumpCompletionCommand
        return [
            new \Symfony\Component\Console\Command\HelpCommand(),
            new \Symfony\Component\Console\Command\ListCommand(),
        ];
    }
}