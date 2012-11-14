<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Phorever\Daemon;

class StatusCommand extends ConfigBasedCommand
{
    protected function configure()
    {
        $this->setName("status")
             ->setDescription("Checks the status of Phorever")
             ->setDefinition(array())
             ->setHelp(<<<HTML
The <info>status</info> shows the status of Phorever
HTML
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $daemon = new Daemon($this->config['pidfile']);

        switch ($daemon->status()) {
            case Daemon::RUNNING_OK:
                $output->writeln("<info>Phorever is running.</info>");
                break;
            case Daemon::STOPPED_BUT_PID_PRESENT:
                $output->writeln("<error>Phorever is NOT running, but PID file is present!</error>");
                break;
            case Daemon::STOPPED_OK:
                $output->writeln("Phorever is stopped.");
                break;
        }
    }
}
