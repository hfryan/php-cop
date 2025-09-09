<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as In;
use Symfony\Component\Console\Output\OutputInterface as Out;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use PHPCop\Services\{ComposerReader, PackagistClient, AuditRunner, ConfigReader};

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
            ->addOption('quiet', 'q', InputOption::VALUE_NONE, 'Disable progress bar and animations')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to config file', '.phpcop.json')
            ->addOption('ignore-packages', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of packages to ignore')
            ->addOption('only-dev', null, InputOption::VALUE_NONE, 'Only scan dev dependencies')
            ->addOption('exclude-dev', null, InputOption::VALUE_NONE, 'Exclude dev dependencies from scan')
            ->addOption('license-allowlist', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of allowed licenses')
            ->addOption('license-denylist', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of denied licenses')
            ->addOption('min-severity', null, InputOption::VALUE_REQUIRED, 'Minimum vulnerability severity: low|moderate|high|critical', 'low');
    }

    protected function execute(In $in, Out $out): int
    {
        $reader    = new ComposerReader();
        $audit     = new AuditRunner();
        $packagist = new PackagistClient();
        $configReader = new ConfigReader();

        // Load configuration file and merge with CLI options
        $configFile = $in->getOption('config');
        $config = $configReader->readConfig($configFile);
        
        // CLI options override config file
        $options = $this->mergeOptions($config, $in);
        $isQuiet = $options['quiet'];

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

        $dependencyType = $options['dependency-type'];
        $pkgs = $reader->readLock('composer.lock', $dependencyType);
        
        if ($progressBar) {
            $progressBar->setMessage('Running security audit...');
            $progressBar->advance();
        }
        
        $composerBin = $options['composer-bin'];
        $auditData  = $audit->run($composerBin);
        $advisories = $auditData['advisories']['packages'] ?? $auditData['advisories'] ?? [];

        if ($progressBar) {
            $progressBar->setMessage('Analyzing ' . count($pkgs) . ' packages...');
            $progressBar->advance();
        }

        $issues = [];
        $now = new \DateTimeImmutable();
        $staleMonths = $options['stale-months'];
        $ignorePackages = $options['ignore-packages'];
        $licenseAllowlist = $options['license-allowlist'];
        $licenseDenylist = $options['license-denylist'];
        $minSeverity = $options['min-severity'];
        $severityMap = ['low' => 1, 'moderate' => 2, 'high' => 3, 'critical' => 4];
        $minSeverityLevel = $severityMap[$minSeverity] ?? 1;

        foreach ($pkgs as $p) {
            $name = $p['name']; $version = $p['version'];
            
            // Skip ignored packages
            if (in_array($name, $ignorePackages, true)) {
                continue;
            }
            
            $info = $packagist->packageInfo($name);

            $latestNorm = $info['latest']['version_normalized'] ?? null;
            $latestDisp = $info['latest']['version'] ?? null;
            $isOutdated = $latestNorm && $latestDisp && $version !== $latestDisp;

            $abandoned = $info['abandoned'] ?? false;

            $time = isset($info['time']) ? new \DateTimeImmutable($info['time']) : null;
            $isStale = $time && $time < $now->modify("-{$staleMonths} months");

            $license = $info['license'] ?? null;

            // Apply license filtering
            if (!empty($licenseAllowlist)) {
                $licenseMatches = false;
                if ($license) {
                    foreach ((array)$license as $lic) {
                        if (in_array($lic, $licenseAllowlist, true)) {
                            $licenseMatches = true;
                            break;
                        }
                    }
                }
                if (!$licenseMatches) {
                    continue; // Skip packages not in allowlist
                }
            }

            if (!empty($licenseDenylist) && $license) {
                $licenseDenied = false;
                foreach ((array)$license as $lic) {
                    if (in_array($lic, $licenseDenylist, true)) {
                        $licenseDenied = true;
                        break;
                    }
                }
                if ($licenseDenied) {
                    continue; // Skip packages in denylist
                }
            }

            $pkgAdvisories = [];
            foreach (($advisories[$name] ?? []) as $adv) {
                $advSeverity = strtolower($adv['severity'] ?? 'unknown');
                $advSeverityLevel = $severityMap[$advSeverity] ?? 0;
                
                // Filter by minimum severity
                if ($advSeverityLevel >= $minSeverityLevel) {
                    $pkgAdvisories[] = [
                        'title'    => $adv['title'] ?? ($adv['cve'] ?? 'Advisory'),
                        'cve'      => $adv['cve'] ?? null,
                        'link'     => $adv['link'] ?? null,
                        'severity' => $advSeverity,
                        'affected' => $adv['affectedVersions'] ?? null,
                    ];
                }
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

        $format = $options['format'];
        switch ($format) {
            case 'table':
                $this->outputTable($out, $issues);
                break;
            case 'json':
                $this->outputJson($out, $issues);
                break;
            case 'md':
                $this->outputMarkdown($out, $issues);
                break;
            case 'html':
                $this->outputHtml($out, $issues);
                break;
            default:
                throw new \RuntimeException("Unsupported format: {$format}");
        }

        $thresholdMap = ['low'=>1,'moderate'=>2,'high'=>3,'critical'=>4];
        $threshold = $thresholdMap[$options['fail-on']] ?? 3;
        $max = 0;
        foreach ($issues as $i) {
            foreach ($i['pkgAdvisories'] as $a) {
                $max = max($max, $thresholdMap[$a['severity']] ?? 0);
            }
        }

        return ($max >= $threshold) ? Command::FAILURE : Command::SUCCESS;
    }

    private function outputTable(Out $out, array $issues): void
    {
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
    }

    private function outputJson(Out $out, array $issues): void
    {
        $payload = ['generatedAt'=> (new \DateTimeImmutable())->format(\DateTime::ATOM),'issues'=>$issues];
        $out->writeln(json_encode($payload, JSON_PRETTY_PRINT));
    }

    private function outputMarkdown(Out $out, array $issues): void
    {
        $out->writeln("# 🚓 PHP Cop: Dependency Patrol — Case File");
        $out->writeln("");
        $out->writeln("Generated: " . (new \DateTimeImmutable())->format('Y-m-d H:i:s T'));
        $out->writeln("");

        if (empty($issues)) {
            $out->writeln("✅ **No issues found!** All dependencies are secure and up-to-date.");
            return;
        }

        $out->writeln("## Issues Found");
        $out->writeln("");

        foreach ($issues as $i) {
            $badges = [];
            if ($i['pkgAdvisories']) $badges[] = '⚠️ **Vulnerabilities**';
            if ($i['abandoned'])     $badges[] = '🚫 **Abandoned**';
            if ($i['isOutdated'])    $badges[] = "⬆️ **Outdated** → `{$i['latestDisp']}`";
            if ($i['isStale'])       $badges[] = '⌛ **Stale**';

            $out->writeln("### `{$i['name']}` v{$i['version']}");
            $out->writeln("");
            $out->writeln(implode(' | ', $badges));
            $out->writeln("");

            if ($i['pkgAdvisories']) {
                $out->writeln("**Security Advisories:**");
                foreach ($i['pkgAdvisories'] as $a) {
                    $severity = strtoupper($a['severity']);
                    $out->writeln("- 🚨 **{$severity}**: {$a['title']} ([Link]({$a['link']}))");
                }
                $out->writeln("");
            }
        }
    }

    private function outputHtml(Out $out, array $issues): void
    {
        $out->writeln("<!DOCTYPE html>");
        $out->writeln("<html lang='en'>");
        $out->writeln("<head>");
        $out->writeln("    <meta charset='UTF-8'>");
        $out->writeln("    <meta name='viewport' content='width=device-width, initial-scale=1.0'>");
        $out->writeln("    <title>🚓 PHP Cop: Dependency Patrol — Case File</title>");
        $out->writeln("    <style>");
        $out->writeln("        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #f8f9fa; }");
        $out->writeln("        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }");
        $out->writeln("        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }");
        $out->writeln("        .package { margin: 20px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 6px; background: #fff; }");
        $out->writeln("        .package-name { font-size: 1.3em; font-weight: bold; color: #495057; margin-bottom: 10px; }");
        $out->writeln("        .badges { margin: 10px 0; }");
        $out->writeln("        .badge { display: inline-block; padding: 4px 8px; margin: 2px; border-radius: 4px; font-size: 0.85em; font-weight: 500; }");
        $out->writeln("        .badge.vuln { background: #f8d7da; color: #721c24; }");
        $out->writeln("        .badge.abandoned { background: #f1c40f; color: #856404; }");
        $out->writeln("        .badge.outdated { background: #d4edda; color: #155724; }");
        $out->writeln("        .badge.stale { background: #ffeaa7; color: #856404; }");
        $out->writeln("        .advisory { margin: 8px 0; padding: 10px; background: #fff5f5; border-left: 4px solid #e74c3c; }");
        $out->writeln("        .severity { font-weight: bold; text-transform: uppercase; }");
        $out->writeln("        .severity.critical { color: #c0392b; }");
        $out->writeln("        .severity.high { color: #e74c3c; }");
        $out->writeln("        .severity.moderate { color: #f39c12; }");
        $out->writeln("        .severity.low { color: #f1c40f; }");
        $out->writeln("        .no-issues { text-align: center; color: #27ae60; font-size: 1.2em; margin: 40px 0; }");
        $out->writeln("    </style>");
        $out->writeln("</head>");
        $out->writeln("<body>");
        $out->writeln("    <div class='container'>");
        $out->writeln("        <div class='header'>");
        $out->writeln("            <h1>🚓 PHP Cop: Dependency Patrol — Case File</h1>");
        $out->writeln("            <p>Generated: " . (new \DateTimeImmutable())->format('Y-m-d H:i:s T') . "</p>");
        $out->writeln("        </div>");

        if (empty($issues)) {
            $out->writeln("        <div class='no-issues'>✅ No issues found! All dependencies are secure and up-to-date.</div>");
        } else {
            foreach ($issues as $i) {
                $out->writeln("        <div class='package'>");
                $out->writeln("            <div class='package-name'>{$i['name']} <code>v{$i['version']}</code></div>");
                $out->writeln("            <div class='badges'>");

                if ($i['pkgAdvisories']) $out->writeln("                <span class='badge vuln'>⚠️ Vulnerabilities</span>");
                if ($i['abandoned'])     $out->writeln("                <span class='badge abandoned'>🚫 Abandoned</span>");
                if ($i['isOutdated'])    $out->writeln("                <span class='badge outdated'>⬆️ Outdated → {$i['latestDisp']}</span>");
                if ($i['isStale'])       $out->writeln("                <span class='badge stale'>⌛ Stale</span>");

                $out->writeln("            </div>");

                if ($i['pkgAdvisories']) {
                    foreach ($i['pkgAdvisories'] as $a) {
                        $severity = strtolower($a['severity']);
                        $out->writeln("            <div class='advisory'>");
                        $out->writeln("                <strong class='severity {$severity}'>{$a['severity']}</strong>: {$a['title']}");
                        if ($a['link']) {
                            $out->writeln("                <br><a href='{$a['link']}' target='_blank'>View Advisory</a>");
                        }
                        $out->writeln("            </div>");
                    }
                }
                $out->writeln("        </div>");
            }
        }

        $out->writeln("    </div>");
        $out->writeln("</body>");
        $out->writeln("</html>");
    }

    private function mergeOptions(array $config, In $input): array
    {
        $options = [];

        // For each option, CLI overrides config, config overrides defaults
        $options['format'] = $input->getOption('format') !== 'table' ? $input->getOption('format') : ($config['format'] ?? 'table');
        $options['stale-months'] = (int)($input->getOption('stale-months') != 18 ? $input->getOption('stale-months') : ($config['stale-months'] ?? 18));
        $options['fail-on'] = $input->getOption('fail-on') !== 'high' ? $input->getOption('fail-on') : ($config['fail-on'] ?? 'high');
        $options['composer-bin'] = $input->getOption('composer-bin') !== 'composer' ? $input->getOption('composer-bin') : ($config['composer-bin'] ?? 'composer');
        $options['quiet'] = $input->getOption('quiet') ?: ($config['quiet'] ?? false);
        
        // Handle ignore-packages from both CLI and config
        $cliIgnore = $input->getOption('ignore-packages');
        $configIgnore = $config['ignore-packages'] ?? [];
        
        if ($cliIgnore) {
            $cliIgnore = array_map('trim', explode(',', $cliIgnore));
            $options['ignore-packages'] = array_unique(array_merge($configIgnore, $cliIgnore));
        } else {
            $options['ignore-packages'] = $configIgnore;
        }

        // Handle dependency type filtering
        $dependencyType = 'all';
        if ($input->getOption('only-dev')) {
            $dependencyType = 'only-dev';
        } elseif ($input->getOption('exclude-dev')) {
            $dependencyType = 'exclude-dev';
        } elseif (isset($config['dependency-type'])) {
            $dependencyType = $config['dependency-type'];
        }
        $options['dependency-type'] = $dependencyType;

        // Handle license filtering
        $cliAllowlist = $input->getOption('license-allowlist');
        $configAllowlist = $config['license-allowlist'] ?? [];
        if ($cliAllowlist) {
            $cliAllowlist = array_map('trim', explode(',', $cliAllowlist));
            $options['license-allowlist'] = array_unique(array_merge($configAllowlist, $cliAllowlist));
        } else {
            $options['license-allowlist'] = $configAllowlist;
        }

        $cliDenylist = $input->getOption('license-denylist');
        $configDenylist = $config['license-denylist'] ?? [];
        if ($cliDenylist) {
            $cliDenylist = array_map('trim', explode(',', $cliDenylist));
            $options['license-denylist'] = array_unique(array_merge($configDenylist, $cliDenylist));
        } else {
            $options['license-denylist'] = $configDenylist;
        }

        // Handle minimum severity
        $options['min-severity'] = $input->getOption('min-severity') !== 'low' ? $input->getOption('min-severity') : ($config['min-severity'] ?? 'low');

        return $options;
    }
}
