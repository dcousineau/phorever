<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Phorever\Phorever;
use Phorever\Daemon;

use Monolog\Logger;
use Phorever\Monolog\Handler\ConsoleHandler;
use Monolog\Handler\StreamHandler;
use Phorever\Monolog\Formatter\ConsoleFormatter;
use Phorever\Monolog\Formatter\FileFormatter;

class StartCommand extends ConfigBasedCommand
{
    protected function configure()
    {
        $this->setName("start")
             ->setDescription("Starts all Phorever processes")
             ->setDefinition(array(
                new InputOption('daemon', 'd', InputOption::VALUE_NONE, 'Run as a daemon'),
                new InputOption('force-pidfile', 'p', InputOption::VALUE_NONE, 'Force the creation of a PID file when not running as a daemon'),
                new InputArgument('role', InputArgument::IS_ARRAY, 'The role(s) to indicate which processes to start, empty indicates all processes', null),
             ))
             ->setHelp(<<<EOT
The <info>start</info> command executes and monitors all processes matching the requested
roles. If no roles are present, Phorever will start and monitor all processes.

Role selection is OR based, meaning executing a "phorever start rolea roleb"
will start all processes with at least rolea OR roleb associated.

Specifying the --daemon option will cause Phorever to fork into the background
leaving behind a pid file in the path specified by the configuration file.

Phorever will NOT start if there is a pid file present.
EOT
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phorever = new Phorever($this->config, $this->getLogger());

        if ($input->getOption('daemon')) {
            $daemon = new Daemon($this->config['pidfile'], $this->getLogger());

            $output->write("Starting Phorever... ");
            $daemon->start(function() use ($phorever, $input) {
                $phorever->run(array(
                    'role' => $input->getArgument('role')
                ));
            });
            $output->writeln("<info>OK!</info>");
        } else {
            if ($input->getOption('force-pidfile')) {
                if (file_exists($this->config['pidfile'])) {
                    throw new \Exception("PID File {$this->config['pidfile']} already exists!");
                }

                $this->logger->debug("Writing PID File");

                file_put_contents($this->config['pidfile'], getmypid());
            }

            $phorever->run(array(
                'role' => $input->getArgument('role')
            ));
        }
    }
}
