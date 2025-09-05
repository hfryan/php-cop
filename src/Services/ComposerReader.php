<?php
namespace PHPCop\Services;

final class ComposerReader {
    public function readLock(string $path = 'composer.lock'): array
    {
        if (!is_file($path)) throw new \RuntimeException("Missing $path");
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        return array_merge($data['packages'] ?? [], $data['packages-dev'] ?? []);
    }
}