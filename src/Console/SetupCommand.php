<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as In;
use Symfony\Component\Console\Output\OutputInterface as Out;

final class SetupCommand extends Command
{
    protected static $defaultName = 'setup';

    protected function configure(): void
    {
        $this
            ->setName('setup')
            ->setDescription('Help configure PHPCop for global usage');
    }

    protected function execute(In $in, Out $out): int
    {
        $out->writeln('<info>🚓 PHPCop Global Setup Helper</info>');
        $out->writeln(str_repeat('-', 50));

        $os = PHP_OS_FAMILY;
        $binDir = exec('composer global config bin-dir --absolute');

        $out->writeln("Detected OS: <comment>{$os}</comment>");
        $out->writeln("Composer global bin directory: <comment>{$binDir}</comment>");
        $out->writeln('');

        if ($os === 'Windows') {
            $this->handleWindows($out, $binDir);
        } else {
            $this->handleUnix($out, $binDir);
        }

        $out->writeln('');
        $out->writeln('🎉 <info>Setup complete! You should now be able to run:</info>');
        $out->writeln('   <comment>phpcop.php scan</comment>');
        $out->writeln('');
        $out->writeln('💡 <comment>Tip:</comment> If the command still doesn\'t work, restart your terminal.');

        return Command::SUCCESS;
    }

    private function handleWindows(Out $out, string $binDir): void
    {
        $out->writeln('<comment>Windows Setup Instructions:</comment>');
        $out->writeln('');
        $out->writeln('1. Add the following directory to your Windows PATH:');
        $out->writeln("   <info>{$binDir}</info>");
        $out->writeln('');
        $out->writeln('2. To add to PATH:');
        $out->writeln('   - Right-click "This PC" → Properties → Advanced System Settings');
        $out->writeln('   - Click "Environment Variables"');
        $out->writeln('   - Under "User variables", find and edit "Path"');
        $out->writeln('   - Click "New" and add the directory above');
        $out->writeln('   - Click OK to save');
        $out->writeln('');
        $out->writeln('<comment>Alternative:</comment> Use the full path directly:');
        $out->writeln("   <info>php {$binDir}\\phpcop.php scan</info>");
    }

    private function handleUnix(Out $out, string $binDir): void
    {
        $shell = getenv('SHELL');
        $rcFile = str_contains($shell, 'zsh') ? '~/.zshrc' : '~/.bash_profile';

        $out->writeln('<comment>macOS/Linux Setup Instructions:</comment>');
        $out->writeln('');
        $out->writeln("1. Add Composer's bin directory to your PATH by running:");
        $out->writeln("   <info>echo 'export PATH=\"{$binDir}:\$PATH\"' >> {$rcFile}</info>");
        $out->writeln('');
        $out->writeln('2. Reload your shell configuration:');
        $out->writeln("   <info>source {$rcFile}</info>");
        $out->writeln('');
        $out->writeln('<comment>Alternative:</comment> Use the full path directly:');
        $out->writeln("   <info>php {$binDir}/phpcop.php scan</info>");

        // Try to automatically add to PATH
        $homeDir = getenv('HOME');
        $fullRcPath = str_replace('~', $homeDir, $rcFile);

        if (is_writable(dirname($fullRcPath))) {
            $pathLine = "export PATH=\"{$binDir}:\$PATH\"";
            
            if (file_exists($fullRcPath)) {
                $currentContent = file_get_contents($fullRcPath);
                if (!str_contains($currentContent, $binDir)) {
                    file_put_contents($fullRcPath, "\n# PHPCop global bin directory\n{$pathLine}\n", FILE_APPEND);
                    $out->writeln('');
                    $out->writeln("✅ <info>Automatically added PATH to {$rcFile}</info>");
                }
            }
        }
    }
}