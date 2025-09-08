<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as In;
use Symfony\Component\Console\Output\OutputInterface as Out;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
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
            ->addOption('composer-bin', null, InputOption::VALUE_REQUIRED, 'composer or composer.bat', 'composer')
            ->addOption('quiet', 'q', InputOption::VALUE_NONE, 'Disable progress bar and animations');
    }

    protected function execute(In $in, Out $out): int
    {
        $reader    = new ComposerReader();
        $audit     = new AuditRunner();
        $packagist = new PackagistClient();
        $isQuiet   = $in->getOption('quiet');

        // Create progress bar (unless quiet mode)
        $progressBar = null;
        if (!$isQuiet) {
            $progressBar = new ProgressBar($out);
            $progressBar->setFormat('🚨 %message%' . "\n" . ' %bar% %percent:3s%%');
            $progressBar->setBarCharacter('<fg=red>█</>');
            $progressBar->setEmptyBarCharacter('<fg=blue>█</>');
            $progressBar->setProgressCharacter('<fg=yellow>█</>');

            $progressBar->setMessage('Reading composer.lock...');
            $progressBar->start(4);
            $progressBar->advance();
        }

        $pkgs = $reader->readLock();
        
        if ($progressBar) {
            $progressBar->setMessage('Running security audit...');
            $progressBar->advance();
        }
        
        $composerBin = (string)$in->getOption('composer-bin');
        $auditData  = $audit->run($composerBin);
        $advisories = $auditData['advisories']['packages'] ?? $auditData['advisories'] ?? [];

        if ($progressBar) {
            $progressBar->setMessage('Analyzing ' . count($pkgs) . ' packages...');
            $progressBar->advance();
        }

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

        if ($progressBar) {
            $progressBar->setMessage('Generating report...');
            $progressBar->advance();
            $progressBar->finish();
            $out->writeln('');  // Add line break after progress bar
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
