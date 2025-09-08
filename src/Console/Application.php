<?php
namespace PHPCop\Console;

use Symfony\Component\Console\Application as Base;
use PHPCop\Console\ScanCommand;
use PHPCop\Console\SetupCommand;

final class Application extends Base {
    public function __construct(){
        parent::__construct('PHP Cop - Dependency Patrol', '0.1.0');
        $this->add(new ScanCommand());
        $this->add(new SetupCommand());
    }
}