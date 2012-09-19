<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends BaseCommand
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
        /** @var $phorever \Phorever\Phorever */
        $phorever = $this->getApplication()->getPhorever();
        $phorever->initializeFromFile();

        $daemon = new \Phorever\Daemon($phorever->get('pidfile'));

        $daemon->stop();
    }
}
