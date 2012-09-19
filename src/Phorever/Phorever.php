<?php
declare(ticks = 1);
namespace Phorever;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phorever\Process\Process;

class Phorever {
    protected $processes = array();
    protected $signalled = false;

    /**
     * @var \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    protected $configuration;
    protected $config = array();

    public function __construct(ConfigurationInterface $configuration) {
        $this->configuration = $configuration;
    }

    public function run(array $options = array()) {
        pcntl_signal(SIGINT, array($this, 'receiveSig'));
        pcntl_signal(SIGTERM, array($this, 'receiveSig'));
        pcntl_signal(SIGHUP, array($this, 'receiveSig'));

        $role = null;
        if (isset($options['role']))
            $role = strtolower($options['role']);

        foreach ($this->get('processes') as $processConfig) {
            //TODO: Select class based on configured type
            $process = new Process($processConfig);

            if ($role && !$process->hasRole($role))
                continue; //Not in our target role

            $process->execute();

            $this->processes[] = $process;

            echo sprintf("Started %s...\n", $process->getName());
        }

        while (!$this->signalled) {
            foreach ($this->processes as $i => &$process) {
                /** @var $process Process */
                $resp = $process->tick();

                if ($resp == Process::STATUS_GARBAGE_COLLECT) {
                    unset($this->processes[$i]);
                }
            }

            if (empty($this->processes))
                exit();
            else
                sleep(1);
        }
    }

    public function receiveSig($sig) {
        $this->signalled = true;
        switch($sig) {
            case SIGTERM:
            case SIGINT:

                foreach ($this->processes as $process) {
                    /** @var $process Process */
                    $process->terminate();
                }
                exit();

                break;
            case SIGHUP:

                break;
            default:

                break;
        }
    }

    public function loadConfig(array $config) {
        $processor = new Processor();

        $this->config = $processor->processConfiguration($this->configuration, array($config));

        return $this;
    }

    public function initializeFromFile($file = null) {
        if ($file == null)
            $file = getcwd() . '/phorever.json';

        if (!file_exists($file)) {
            throw new \Exception("Could not find phorever configuration file");
        }

        $this->loadConfig(json_decode(file_get_contents($file), true));
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