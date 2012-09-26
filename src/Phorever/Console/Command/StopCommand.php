<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Phorever\Daemon;

class StopCommand extends ConfigBasedCommand
{
    protected function configure()
    {
        $this->setName("stop")
            ->setDescription("Starts all Phorever processes")
            ->setDefinition(array(
            ))
            ->setHelp(<<<EOT
The <info>stop</info> command stops all configured Phorever processes
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Stopping Phorever...");
        $daemon = new Daemon($this->config['pidfile']);

        $daemon->stop();
        $output->writeln(" <info>OK!</info>");
    }
}
