<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName("status")
            ->setDescription("Checks the status of Phorever")
            ->setDefinition(array(

            ))
            ->setHelp(<<<HTML
The <info>status</info> shows the status of Phorever
HTML
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();

        if (!file_exists("$cwd/phorever.json")) {
            $output->getErrorOutput()->writeln("<error>Can not find phorever.json in your current working directory</error>");
            return -1;
        }

//        /** @var $daemon \Phorever\Daemon */
//        $daemon = $this->getApplication()->getDaemon();
//        $daemon->loadConfig(json_decode(file_get_contents("$cwd/phorever.json"), true));
//
//        $daemon->start();
    }
}
