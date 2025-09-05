<?php
namespace PHPCop\Services;

use GuzzleHttp\Client;
use Composer\Semver\Semver;

final class PackagistClient {
    private Client $http;
    public function __construct(?Client $http = null) {
        $this->http = $http ?: new Client(['base_uri' => 'https://packagist.org/']);
    }

    public function packageInfo(string $name): array {
        // p2 compact format
        $resp = $this->http->get("p2/{$name}.json");
        $data = json_decode($resp->getBody()->getContents(), true);
        // flatten the versions
        $versions = $data['packages'][$name] ?? [];
        usort($versions, fn($a,$b) => strcmp($b['version_normalized'], $a['version_normalized']));
        $latest = $versions[0] ?? [];
        $abandoned = $latest['abandoned'] ?? false;
        $license = $latest['license'][0] ?? null;
        $time = $latest['time'] ?? null;
        return compact('latest', 'abandoned', 'license', 'time', 'versions');
    }
}