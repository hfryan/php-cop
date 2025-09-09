<?php
namespace PHPCop\Services;

final class ComposerReader {
    public function readLock(string $path = 'composer.lock', ?string $dependencyType = null): array
    {
        if (!is_file($path)) throw new \RuntimeException("Missing $path");
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        
        $prodPackages = $data['packages'] ?? [];
        $devPackages = $data['packages-dev'] ?? [];
        
        // Add metadata to distinguish package types
        foreach ($prodPackages as &$pkg) {
            $pkg['_phpcop_dev_dependency'] = false;
        }
        foreach ($devPackages as &$pkg) {
            $pkg['_phpcop_dev_dependency'] = true;
        }
        
        // Filter by dependency type if requested
        switch ($dependencyType) {
            case 'only-dev':
                return $devPackages;
            case 'exclude-dev':
                return $prodPackages;
            case 'all':
            default:
                return array_merge($prodPackages, $devPackages);
        }
    }
}