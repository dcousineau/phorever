<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class StopCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName("stop")
            ->setDescription("Starts all Phorever processes")
            ->setDefinition(array(
                new InputOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Base directory to execute from, defaults to the current working directory', null),
            ))
            ->setHelp(<<<EOT
The <info>stop</info> command stops all configured Phorever processes
EOT
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

        $daemon->stop();
    }
}
