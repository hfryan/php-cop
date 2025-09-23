<?php
namespace PHPCop\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;
use Composer\Semver\Semver;

final class PackagistClient {
    private Client $http;
    private array $cache = [];
    private string $cacheDir;
    private int $cacheTtl = 3600; // 1 hour cache TTL
    private bool $cacheEnabled = true;

    public function __construct(?Client $http = null, bool $cacheEnabled = true, int $cacheTtl = 3600) {
        $this->http = $http ?: new Client([
            'base_uri' => 'https://packagist.org/',
            'timeout' => 10,
            'connect_timeout' => 5
        ]);
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheTtl = $cacheTtl;
        $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpcop-cache';
        if ($this->cacheEnabled) {
            $this->ensureCacheDir();
        }
    }

    public function packageInfo(string $name): array {
        // Check memory cache first
        if ($this->cacheEnabled && isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // Check file cache
        if ($this->cacheEnabled) {
            $cachedData = $this->getCachedData($name);
            if ($cachedData !== null) {
                $this->cache[$name] = $cachedData;
                return $cachedData;
            }
        }

        // Fetch from API
        try {
            $resp = $this->http->get("p2/{$name}.json");
            $data = json_decode($resp->getBody()->getContents(), true);
            $result = $this->processPackageData($name, $data);
            
            // Cache the result
            if ($this->cacheEnabled) {
                $this->cache[$name] = $result;
                $this->setCachedData($name, $result);
            }
            
            return $result;
        } catch (RequestException $e) {
            // Return empty result on failure
            $result = ['latest' => [], 'abandoned' => false, 'license' => null, 'time' => null, 'versions' => []];
            if ($this->cacheEnabled) {
                $this->cache[$name] = $result;
            }
            return $result;
        }
    }

    public function packageInfoBatch(array $packageNames): array {
        $results = [];
        $toFetch = [];

        // Check cache for each package first (if caching enabled)
        foreach ($packageNames as $name) {
            if ($this->cacheEnabled && isset($this->cache[$name])) {
                $results[$name] = $this->cache[$name];
                continue;
            }

            if ($this->cacheEnabled) {
                $cachedData = $this->getCachedData($name);
                if ($cachedData !== null) {
                    $this->cache[$name] = $cachedData;
                    $results[$name] = $cachedData;
                    continue;
                }
            }
            
            $toFetch[] = $name;
        }

        if (empty($toFetch)) {
            return $results;
        }

        // Create async requests for remaining packages
        $promises = [];
        foreach ($toFetch as $name) {
            $promises[$name] = $this->http->getAsync("p2/{$name}.json");
        }

        // Execute requests in parallel
        $responses = Promise\Utils::settle($promises)->wait();

        // Process responses
        foreach ($responses as $name => $response) {
            if ($response['state'] === 'fulfilled') {
                try {
                    $data = json_decode($response['value']->getBody()->getContents(), true);
                    $result = $this->processPackageData($name, $data);
                } catch (\Exception $e) {
                    $result = ['latest' => [], 'abandoned' => false, 'license' => null, 'time' => null, 'versions' => []];
                }
            } else {
                // Request failed
                $result = ['latest' => [], 'abandoned' => false, 'license' => null, 'time' => null, 'versions' => []];
            }

            if ($this->cacheEnabled) {
                $this->cache[$name] = $result;
                $this->setCachedData($name, $result);
            }
            $results[$name] = $result;
        }

        return $results;
    }

    private function processPackageData(string $name, array $data): array {
        $versions = $data['packages'][$name] ?? [];
        
        // Sort versions using proper semantic version comparison
        // Filter out non-stable versions and sort by version_normalized
        $stableVersions = array_filter($versions, function($version) {
            // Only include stable versions (no dev, alpha, beta, RC unless no stable versions exist)
            $versionString = $version['version'] ?? '';
            return !preg_match('/-(dev|alpha|beta|rc|RC)/i', $versionString);
        });
        
        // If no stable versions, use all versions
        if (empty($stableVersions)) {
            $stableVersions = $versions;
        }
        
        // Sort using semantic version comparison on normalized versions
        usort($stableVersions, function($a, $b) {
            $aVersion = $a['version_normalized'] ?? '0.0.0.0';
            $bVersion = $b['version_normalized'] ?? '0.0.0.0';
            
            // Convert normalized version to comparable format
            // Packagist normalizes versions to x.y.z.w format
            return version_compare($bVersion, $aVersion);
        });
        
        $latest = $stableVersions[0] ?? [];
        $abandoned = $latest['abandoned'] ?? false;
        $license = $latest['license'][0] ?? null;
        $time = $latest['time'] ?? null;
        return compact('latest', 'abandoned', 'license', 'time', 'versions');
    }

    private function ensureCacheDir(): void {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getCacheFileName(string $packageName): string {
        return $this->cacheDir . DIRECTORY_SEPARATOR . 'pkg_' . hash('sha256', $packageName) . '.json';
    }

    private function getCachedData(string $packageName): ?array {
        $cacheFile = $this->getCacheFileName($packageName);
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        $cacheTime = filemtime($cacheFile);
        if ($cacheTime < time() - $this->cacheTtl) {
            @unlink($cacheFile);
            return null;
        }

        $content = @file_get_contents($cacheFile);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        return $data ?: null;
    }

    private function setCachedData(string $packageName, array $data): void {
        $cacheFile = $this->getCacheFileName($packageName);
        @file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}