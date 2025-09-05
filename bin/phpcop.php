#!/usr/bin/env php
<?php
// Try multiple autoloader paths for different installation methods
$autoloaders = [
    __DIR__ . '/../vendor/autoload.php',      // Local development
    __DIR__ . '/../../../autoload.php',       // Global Composer install
    __DIR__ . '/../../../../vendor/autoload.php', // Project dependency
];

$loaded = false;
foreach ($autoloaders as $autoloader) {
    if (file_exists($autoloader)) {
        require $autoloader;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    fwrite(STDERR, "Error: Could not find Composer autoloader. Please run 'composer install'.\n");
    exit(1);
}

use PHPCop\Console\Application;

$app = new Application();
$app->run();