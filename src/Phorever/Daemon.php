<?php
namespace Phorever;

class Daemon
{
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

    public function __construct($pidfilepath) {
        $this->setPidFilePath($pidfilepath);
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

        posix_kill($this->getPid(), SIGTERM);
        $this->clearPid();
    }

    public function getPid() {
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