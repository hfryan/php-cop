<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as In;
use Symfony\Component\Console\Output\OutputInterface as Out;
use Symfony\Component\Console\Input\InputOption;
use PHPCop\Services\{ComposerReader, PackagistClient, AuditRunner};

final class ScanCommand extends Command
{
    protected static $defaultName = 'scan';

    protected function configure(): void
    {
        $this
            ->setName('scan')
            ->setDescription('Scan dependencies for vulns, abandonment, and staleness')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'table|json|md|html', 'table')
            ->addOption('stale-months', null, InputOption::VALUE_REQUIRED, 'Months to flag as stale', 18)
            ->addOption('fail-on', null, InputOption::VALUE_REQUIRED, 'low|moderate|high|critical', 'high')
            ->addOption('composer-bin', null, InputOption::VALUE_REQUIRED, 'composer or composer.bat', 'composer');
    }

    protected function execute(In $in, Out $out): int
    {
        $reader    = new ComposerReader();
        $audit     = new AuditRunner();
        $packagist = new PackagistClient();

        $pkgs       = $reader->readLock();
        $composerBin = (string)$in->getOption('composer-bin');
        $auditData  = $audit->run($composerBin);
        $advisories = $auditData['advisories']['packages'] ?? $auditData['advisories'] ?? [];

        $issues = [];
        $now = new \DateTimeImmutable();
        $staleMonths = (int)$in->getOption('stale-months');

        foreach ($pkgs as $p) {
            $name = $p['name']; $version = $p['version'];
            $info = $packagist->packageInfo($name);

            $latestNorm = $info['latest']['version_normalized'] ?? null;
            $latestDisp = $info['latest']['version'] ?? null;
            $isOutdated = $latestNorm && $latestDisp && $version !== $latestDisp;

            $abandoned = $info['abandoned'] ?? false;

            $time = isset($info['time']) ? new \DateTimeImmutable($info['time']) : null;
            $isStale = $time && $time < $now->modify("-{$staleMonths} months");

            $license = $info['license'] ?? null;

            $pkgAdvisories = [];
            foreach (($advisories[$name] ?? []) as $adv) {
                $pkgAdvisories[] = [
                    'title'    => $adv['title'] ?? ($adv['cve'] ?? 'Advisory'),
                    'cve'      => $adv['cve'] ?? null,
                    'link'     => $adv['link'] ?? null,
                    'severity' => strtolower($adv['severity'] ?? 'unknown'),
                    'affected' => $adv['affectedVersions'] ?? null,
                ];
            }

            if ($isOutdated || $abandoned || $isStale || $pkgAdvisories) {
                $issues[] = compact('name','version','license','isOutdated','abandoned','isStale','pkgAdvisories','latestDisp','latestNorm');
            }
        }

        $format = (string)$in->getOption('format');
        if ($format === 'table') {
            $out->writeln("<info>🚓 PHP Cop: Dependency Patrol — Case File</info>");
            $out->writeln(str_repeat('-', 80));
            foreach ($issues as $i) {
                $badges = [];
                if ($i['pkgAdvisories']) $badges[] = '⚠️ Vulns';
                if ($i['abandoned'])     $badges[] = '🚫 Abandoned';
                if ($i['isOutdated'])    $badges[] = '⬆️ Outdated → '.$i['latestDisp'];
                if ($i['isStale'])       $badges[] = '⌛ Stale';
                $out->writeln(sprintf("• %s %s  [%s]", $i['name'], $i['version'], implode(' ', $badges)));
                foreach ($i['pkgAdvisories'] as $a) {
                    $out->writeln("   └─ 🚨 {$a['severity']} {$a['title']} {$a['link']}");
                }
            }
        } else {
            $payload = ['generatedAt'=> (new \DateTimeImmutable())->format(\DateTime::ATOM),'issues'=>$issues];
            $out->writeln(json_encode($payload, JSON_PRETTY_PRINT));
        }

        $thresholdMap = ['low'=>1,'moderate'=>2,'high'=>3,'critical'=>4];
        $threshold = $thresholdMap[$in->getOption('fail-on')] ?? 3;
        $max = 0;
        foreach ($issues as $i) {
            foreach ($i['pkgAdvisories'] as $a) {
                $max = max($max, $thresholdMap[$a['severity']] ?? 0);
            }
        }

        return ($max >= $threshold) ? Command::FAILURE : Command::SUCCESS;
    }
}
