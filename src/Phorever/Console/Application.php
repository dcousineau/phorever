<?php
namespace Phorever\Console;

use Phorever\Phorever;
use Phorever\Console\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function getDefaultCommands() {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\StartCommand();
        $commands[] = new Command\StopCommand();
        $commands[] = new Command\StatusCommand();

        return $commands;
    }
}