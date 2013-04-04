<?php
declare(ticks = 1);
namespace Phorever;

use Monolog\Logger;
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

        $roles = array();
        if (isset($options['role'])) {
            $roles = $options['role'];
            if (is_array($roles)) {
                $roles = array_map('strtolower', $roles);
            } else {
                $roles = array(strtolower($roles));
            }

            if (count($roles) == 0)
                $this->logger->addInfo("Starting Phorever with <comment>no role specified</comment> (all processes)");
            else if (count($roles) == 1)
                $this->logger->addInfo(sprintf("Starting Phorever with role <info>%s</info>", $roles[0]));
            else
                $this->logger->addInfo(sprintf("Starting Phorever with roles <info>%s</info>", implode(', ', $roles)));
        } else {
            $this->logger->addWarning("Starting Phorever without a role");
        }

        $loggingConfig = $this->get('logging');

        foreach ($this->get('processes') as $processConfig) {
            //TODO: Centralized logging class to be injected into Process
            $processConfig['log_directory'] = $loggingConfig['directory'];

            //TODO: Select class based on configured type
            $process = new Process($processConfig, $this->logger);

            if (!empty($roles) && !$process->hasRole($roles)) {
                $this->logger->addDebug(sprintf("Skipping '%s' because not in target role", $process->getName()));
                continue; //Not in our target role
            }

            $this->logger->addDebug(sprintf("Loaded Process '%s'", $process->getName()));

            foreach (range(1, $processConfig['clones']) as $i) {
                $clone = clone $process;

                if ($i > 1) {
                    $this->logger->addDebug(sprintf("Adding clone %d for '%s'", $i, $clone->getName()));
                }

                $clone->execute();

                $this->processes[] = $clone;
            }

            unset($process);
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
                sleep((int)$this->get('tick', 2));
            }
        }
    }

    public function stop() {
        $this->logger->addInfo("Stopping Phorever and all subprocesses");
        foreach (range(1,10) as $i) {
            foreach ($this->processes as $j => $process) {
                /** @var $process Process */
                if (!$process->isRunning()) {
                    unset($this->processes[$j]);
                    continue;
                }

                switch ($i) {
                    case 10:
                        $this->logger->debug("Attempting to KILL {$process->getName()}...");
                        if ($process->kill()) {
                            unset($this->processes[$j]);
                        }
                        break;
                    default:
                        $this->logger->debug("Attempting to terminate {$process->getName()}...");
                        if ($process->terminate()) {
                           unset($this->processes[$j]);
                        }
                        break;
                }
            }

            if (count($this->processes) == 0) break;

            sleep(1);
        }

        if (count($this->processes) != 0) {
            $this->logger->addCritical("Could not stop all processes!");
            return false;
        } else {
            $this->logger->addInfo("Stopped all subprocesses, exiting!");
            return true;
        }
    }

    public function receiveSig($sig) {
        $this->signalled = true;
        switch($sig) {
            case SIGTERM:
                $this->logger->addError("Received SIGTERM");
                exit($this->stop() ? 0 : -1);

                break;
            case SIGINT:
                $this->logger->addWarning("Received SIGINT");
                exit($this->stop() ? 0 : -1);

                break;
            default:
                $this->logger->addCritical("Received Unknown Signal $sig");
                exit($this->stop() ? 127 : 127);

                break;
        }
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