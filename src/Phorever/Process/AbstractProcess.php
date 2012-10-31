<?php
namespace Phorever\Process;

use Monolog\Logger;

abstract class AbstractProcess {
    /**
     * Returned by tick function to indicate the process is in a known state and everything
     * is okay. (e.g. the process could be down, but the tick function is aware and waiting
     * to respawn)
     *
     * @see tick
     */
    const STATUS_OKAY = 'still_okay';

    /**
     * Returned by tick function to indicate that the process will be taking no further
     * action, everything is shut down, and the object can safely be freed.
     *
     * @see tick
     */
    const STATUS_GARBAGE_COLLECT = 'garbage_collect';

    /**
     * @var array
     */
    protected $config;

    /**
     * Generated machine name
     *
     * @var string
     */
    protected $machine_name;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param array $config Individual process configuration array
     */
    public function __construct(array $config, Logger $logger) {
        $this->config = $config;
        $this->logger = $logger;
        $this->init();
    }

    /**
     * Overridable init function called after construct
     */
    public function init() {
        //noop
    }

    /**
     * Ensure process is terminated on destruction to ensure no orphans
     */
    public function __destruct() {
        $this->terminate();
    }

    /**
     * Called every iteration to check the status of the process, determine if it's time to respawn,
     * and perform any running maintenence
     *
     * THIS FUNCTION SHOULD NOT BLOCK!
     *
     * @return int Status constant
     */
    abstract public function tick();

    /**
     * Starts process
     */
    abstract public function execute();

    /**
     * Signals process to terminate. Should block until process is shut down
     */
    abstract public function terminate();

    /**
     * Indicates is associated process is alive & running
     *
     * @return bool
     */
    abstract public function isRunning();

    /**
     * Get configuration value or $default if no configuration value set
     *
     * @param string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        if (isset($this->config[$key])) return $this->config[$key];
        else return $default;
    }

    /**
     * Shortcut to get the name of the process
     *
     * @return string
     */
    public function getName() {
        return $this->get('name');
    }

    public function getMachineName() {
        if (!$this->machine_name) {
            $this->machine_name = preg_replace('/[^a-z0-9_\-\.]+/i', '_', strtolower(trim($this->getName())));
        }

        return $this->machine_name;
    }

    /**
     * Shortcut to check a specified role against the roles of the process
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($roles) {
        $roles = (array)$roles;

        $member = $this->get('roles', array());
        $member = array_map('strtolower', $member);

        $intersection = array_intersect($roles, $member);

        return !empty($intersection);
    }

    protected function debug($message, array $context = array()) {
        $this->logger->addDebug(sprintf("[%s] %s", $this->getName(), $message), $context);
    }
    protected function info($message, array $context = array()) {
        $this->logger->addInfo(sprintf("[%s] %s", $this->getName(), $message), $context);
    }
    protected function warn($message, array $context = array()) {
        $this->logger->addWarning(sprintf("[%s] %s", $this->getName(), $message), $context);
    }
    protected function error($message, array $context = array()) {
        $this->logger->addError(sprintf("[%s] %s", $this->getName(), $message), $context);
    }
}