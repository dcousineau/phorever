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
                new InputArgument('role', InputArgument::IS_ARRAY, 'The role(s) to indicate which processes to start, empty indicates all processes', null),
             ))
             ->setHelp(<<<EOT
The <info>start</info> command starts Phorever processes
EOT
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $level = false;
        if ($input->getOption('verbose'))
            $level = Logger::DEBUG;

        $logger = new Logger('phorever');

        if ($input->getOption('daemon')) {
            if (!file_exists($this->config['logging']['directory'])) {
                if (!mkdir($this->config['logging']['directory'], 0777, true)) {
                    throw new \Exception("Unable to create logging directory");
                }
            }

            $logger->pushHandler($handler = new StreamHandler($this->config['logging']['directory'] . 'phorever.log', $level ?: Logger::INFO));
            $handler->setFormatter(new FileFormatter());
        } else {
            $logger->pushHandler($stdoutHandler = new ConsoleHandler($output, $level ?: Logger::INFO));
            $logger->pushHandler($stderrHandler = new ConsoleHandler($output->getErrorOutput(), Logger::ERROR, false));

            $stderrHandler->setFormatter(new ConsoleFormatter());
            $stdoutHandler->setFormatter(new ConsoleFormatter());
        }

        $phorever = new Phorever($this->config, $logger);

        if ($input->getOption('daemon')) {
            $daemon = new Daemon($this->config['pidfile']);

            $output->write("Starting Phorever... ");
            $daemon->start(function() use ($phorever, $input) {
                $phorever->run(array(
                    'role' => $input->getArgument('role')
                ));
            });
            $output->writeln("<info>OK!</info>");
        } else {
            $phorever->run(array(
                'role' => $input->getArgument('role')
            ));
        }
    }
}
