<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Config\Definition\Processor;
use Phorever\Configuration;

use Monolog\Logger;

abstract class ConfigBasedCommand extends BaseCommand
{
    /**
     * @var array
     */
    protected $config;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to execute from, defaults to ./phorever.json', './phorever.json');
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($file = realpath($input->getOption('config'))) {
            chdir(dirname($file));
        } else {
            throw new \Exception(sprintf("Could not find configuration file at '%s'", $file));
        }

        $processor = new Processor();
        $raw_config = json_decode(file_get_contents($file), true);
        $this->config = $processor->processConfiguration(new Configuration(), array($raw_config));
    }
}
