<?php
namespace PHPCop\Services;

final class ConfigReader
{
    public function readConfig(string $configFile = '.phpcop.json'): array
    {
        if (!is_file($configFile)) {
            return [];
        }

        $content = file_get_contents($configFile);
        if ($content === false) {
            throw new \RuntimeException("Unable to read config file: {$configFile}");
        }

        try {
            $config = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Invalid JSON in config file {$configFile}: " . $e->getMessage());
        }

        return $this->validateConfig($config ?? []);
    }

    private function validateConfig(array $config): array
    {
        $defaults = [
            'format' => 'table',
            'stale-months' => 18,
            'fail-on' => 'high',
            'composer-bin' => 'composer',
            'quiet' => false,
            'ignore-packages' => [],
        ];

        // Merge with defaults
        $config = array_merge($defaults, $config);

        // Validate format
        if (!in_array($config['format'], ['table', 'json', 'md', 'html'])) {
            throw new \RuntimeException("Invalid format '{$config['format']}'. Must be: table, json, md, html");
        }

        // Validate fail-on
        if (!in_array($config['fail-on'], ['low', 'moderate', 'high', 'critical'])) {
            throw new \RuntimeException("Invalid fail-on '{$config['fail-on']}'. Must be: low, moderate, high, critical");
        }

        // Validate stale-months
        if (!is_int($config['stale-months']) || $config['stale-months'] < 1) {
            throw new \RuntimeException("stale-months must be a positive integer");
        }

        // Validate ignore-packages is array
        if (!is_array($config['ignore-packages'])) {
            throw new \RuntimeException("ignore-packages must be an array of package names");
        }

        // Validate quiet is boolean
        if (!is_bool($config['quiet'])) {
            throw new \RuntimeException("quiet must be a boolean (true/false)");
        }

        return $config;
    }
}