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
             ->setDescription("Starts Phorever processes based on specified roles")
             ->setDefinition(array())
             ->setHelp(<<<EOT
The <info>stop</info> command stops the Phorever daemon.

This command will attempt to gracefully stop said daemon for 30 seconds before
sending a SIGKILL.

Each process is given 10 seconds by the daemon to successfully terminate before
it sends said process a SIGKILL.

The pid file is only cleaned up on a successful stop.
EOT
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Stopping Phorever...");
        $daemon = new Daemon($this->config['pidfile'], $this->getLogger());

        $daemon->stop();
        $output->writeln(" <info>OK!</info>");
    }
}
