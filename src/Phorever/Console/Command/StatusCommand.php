<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName("status")
            ->setDescription("Checks the status of Phorever")
            ->setDefinition(array(
                new InputOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Base directory to execute from, defaults to the current working directory', null),
            ))
            ->setHelp(<<<HTML
The <info>status</info> shows the status of Phorever
HTML
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($dir = $input->getOption('directory')) {
            if (!file_exists($dir)) throw new \Exception("Invalid directory");
            chdir($dir);
        }

        /** @var $phorever \Phorever\Phorever */
        $phorever = $this->getApplication()->getPhorever();
        $phorever->initializeFromFile();

        $daemon = new \Phorever\Daemon($phorever->get('pidfile'));

        switch ($daemon->status()) {
            case \Phorever\Daemon::RUNNING_OK:
                $output->writeln("<info>Phorever is running.</info>");
                break;
            case \Phorever\Daemon::STOPPED_BUT_PID_PRESENT:
                $output->writeln("<error>Phorever is NOT running, but PID file is present!</error>");
                break;
            case \Phorever\Daemon::STOPPED_OK:
                $output->writeln("Phorever is stopped.");
                break;
        }
    }
}
