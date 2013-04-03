<?php
namespace Phorever;

use Monolog\Logger;

class Daemon
{
    const RUNNING_OK = "running_ok";
    const STOPPED_BUT_PID_PRESENT = "stopped_but_pid_present";
    const STOPPED_OK = "stopped_ok";

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $isChild;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var string
     */
    protected $pidfile;

    /**
     * @var callback
     */
    protected $callback;

    public function __construct($pidfilepath, Logger $logger) {
        $this->setPidFilePath($pidfilepath);
        $this->logger = $logger;
    }

    public function setPidFilePath($file) {
        $this->pidfile = $file;
    }

    public function getPidFilePath() {
        return $this->pidfile;
    }

    public function start($callback) {
        if ($this->getPid())
            throw new \Exception("Process already started");

        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new \Exception("Unable to fork");
        } else if ($pid > 0) {
            $this->writePid($pid);
        } else {
            $this->isChild = true;
            call_user_func_array($callback, array());
            $this->clearPid();
        }
    }

    public function stop() {
        if (!$this->getPid())
            throw new \Exception("Process not running");

        foreach(range(1, 10) as $i) {
            switch ($i) {
                case 1:
                    $this->logger->debug('Sending SIGTERM');
                    posix_kill($this->getPid(), SIGTERM);

                    if (posix_get_last_error() == SOCKET_EPERM)
                        throw new \Exception("You do not have permission to stop this process");

                    if ($this->status() != self::RUNNING_OK) {
                        $this->logger->debug('First attempt results in full stop!');
                        break 2;
                    }

                    break;
                case 10:
                    $this->logger->debug('Sending SIGKILL!!');
                    posix_kill($this->getPid(), SIGKILL);

                    if ($this->status() != self::RUNNING_OK) {
                        $this->logger->debug('Results in full stop!');
                        break 2;
                    }

                    break;
                default:
                    $this->logger->debug('Sending another SIGTERM');
                    posix_kill($this->getPid(), SIGTERM);

                    if ($this->status() != self::RUNNING_OK) {
                        $this->logger->debug('Results in full stop!');
                        break 2;
                    }

                    break;
            }
            sleep(3);
        }

        if ($this->status() == self::RUNNING_OK)
            throw new \Exception("There was an error attempting to end the process");

        $this->clearPid();
    }

    public function status() {
        if ($pid = $this->getPid(true)) {
            $up = posix_kill($pid, 0);

            // check the number of lines that were returned
            if($up){
                return self::RUNNING_OK;
            } else {
                return self::STOPPED_BUT_PID_PRESENT;
            }
        } else {
            return self::STOPPED_OK;
        }
    }

    public function getPid($force_file = false) {
        if ($force_file) $this->pid = null;

        if (!$this->pid && file_exists($this->pidfile))
            $this->pid = (int)file_get_contents($this->pidfile);

        return $this->pid;
    }

    public function writePid($pid) {
        $this->pid = $pid;
        file_put_contents($this->pidfile, $this->pid, LOCK_EX);
    }

    public function clearPid() {
        $this->pid = null;
        unlink($this->pidfile);
    }
}