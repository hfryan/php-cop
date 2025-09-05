<?php
namespace PHPCop\Services;

use Symfony\Component\Process\Process;

final class AuditRunner {
    public function run(?string $composerBin = 'composer'): array {
        $p = new Process([$composerBin, 'audit', '--format=json']);
        $p->run();

        $output = $p->getOutput();

        $json = json_decode($output ?: '{}', true);
        return $json ?: ['advisories' => [], 'counts' => []];
    }
}