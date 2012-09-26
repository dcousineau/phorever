<?php
declare(ticks = 1);
namespace Phorever;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Phorever\Process\Process;
use Phorever\Monolog\Formatter\ConsoleFormatter;

class Phorever {
    protected $processes = array();
    protected $signalled = false;


    protected $config = array();

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(array $config, Logger $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function run(array $options = array()) {
        pcntl_signal(SIGINT, array($this, 'receiveSig'));
        pcntl_signal(SIGTERM, array($this, 'receiveSig'));
        pcntl_signal(SIGHUP, array($this, 'receiveSig'));

        $role = null;
        if (isset($options['role'])) {
            $role = strtolower($options['role']);
            $this->logger->addInfo(sprintf("Starting Phorever with role <info>%s</info>", $role));
        } else {
            $this->logger->addWarning("Starting Phorever without a role");
        }

        $loggingConfig = $this->get('logging');

        foreach ($this->get('processes') as $processConfig) {
            //TODO: Centralized logging class to be injected into Process
            $processConfig['log_directory'] = $loggingConfig['directory'];

            //TODO: Select class based on configured type
            $process = new Process($processConfig, $this->logger);

            if ($role && !$process->hasRole($role))
                continue; //Not in our target role

            $this->logger->addInfo(sprintf("Loaded Process '%s'", $process->getName()));

            $process->execute();

            $this->processes[] = $process;
        }

        while (!$this->signalled) {
            $this->logger->addDebug("Tick!");

            foreach ($this->processes as $i => &$process) {
                /** @var $process Process */
                $resp = $process->tick();

                if ($resp == Process::STATUS_GARBAGE_COLLECT) {
                    $this->logger->addWarning(sprintf("Process '%s' marked as ready for garbage collection", $process->getName()));
                    unset($this->processes[$i]);
                }
            }

            if (empty($this->processes)) {
                $this->logger->addWarning("Process list empty, exiting");
                exit(0);
            } else {
                sleep(1);
            }
        }
    }

    public function stop() {
        $this->logger->addInfo("Stopping Phorever and all subprocesses");
        foreach ($this->processes as $process) {
            /** @var $process Process */
            $process->terminate();
        }
    }

    public function receiveSig($sig) {
        $this->signalled = true;
        switch($sig) {
            case SIGTERM:
                $this->logger->addError("Received SIGTERM");
                $this->stop();
                exit(0);

                break;
            case SIGINT:
                $this->logger->addWarning("Received SIGINT");
                $this->stop();
                exit(0);

                break;
            case SIGHUP:


                break;
            default:

                break;
        }

        exit(127);
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function get($key, $default = null) {
        if (isset($this->config[$key])) return $this->config[$key];
        else return $default;
    }

    public function set($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
}