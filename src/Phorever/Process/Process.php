<?php
namespace Phorever\Process;

class Process extends AbstractProcess {
    /**
     * Open resource
     * @var resource
     */
    protected $proc;

    /**
     * @var float microtime timestamp
     */
    protected $down = null;

    /**
     * Array of streams
     *  0: stdin
     *  1: stdout
     *  2: stderr
     *
     * @var array
     */
    protected $pipes = array(0 => null, 1 => null, 2 => null);

    /**
     * @var string|false false if no file should be logged for stdout output
     */
    protected $stdout_file = false;

    /**
     * @var string|false false if no file should be logged for stderr output
     */
    protected $stderr_file = false;

    public function init() {

        if ($this->get('log_forwarding')) {
            $this->logger->addDebug("Enabling log forwarding");

            $stdout_logfile = $this->get('log_directory') . str_replace(array('%name%'), array($this->getMachineName()), $this->get('stdout_file'));
            $stderr_logfile = $this->get('log_directory') . str_replace(array('%name%'), array($this->getMachineName()), $this->get('stderr_file'));

            if ($stdout_logfile) {
                $file = $stdout_logfile;
                $dir = dirname($file);

                if (!file_exists($dir)) {
                    $success = mkdir($dir, 0750, true);
                    if (!$success) throw new \Exception(sprintf("Unable to create '%s' directory for log files!", $dir));
                }

                if (!file_exists($file)) {
                    $success = touch($file);
                    if (!$success) throw new \Exception(sprintf("Unable to create '%s' file for logging!", $file));
                }

                $this->stdout_file = $file;
            } else {
                $this->stdout_file = false;
            }

            if ($stderr_logfile) {
                $file = $stderr_logfile;
                $dir = dirname($file);

                if (!file_exists($dir)) {
                    $success = mkdir($dir, 0750, true);
                    if (!$success) throw new \Exception(sprintf("Unable to create '%s' directory for error log files!", $dir));
                }

                if (!file_exists($file)) {
                    $success = touch($file);
                    if (!$success) throw new \Exception(sprintf("Unable to create '%s' file for error logging!", $file));
                }

                $this->stderr_file = $file;
            } else {
                $this->stderr_file = false;
            }
        }
    }

    public function tick() {
        $now = microtime(true);

        if (is_resource($this->getStdOut())) {
            stream_set_blocking($this->getStdOut(), false);
            $out = stream_get_contents($this->getStdOut());
            if ($this->stdout_file) //Should we write the log?
                file_put_contents($this->stdout_file, $out, FILE_APPEND);
            unset($out);
        }


        if (is_resource($this->getStdErr())) {
            stream_set_blocking($this->getStdErr(), false);
            $out = stream_get_contents($this->getStdErr());
            if ($this->stderr_file) //Should we write the log?
                file_put_contents($this->stderr_file, $out, FILE_APPEND);
            unset($out);
        }

        if (!$this->isRunning()) {
            if (!$this->down) {
                //Down state is new, log it
                $this->down = $now;
            }

            //If process has been down
            if ($now - $this->down >= $this->get('resurrect_after', 60)) {
                $this->down = null;
                $this->execute();
            }
        }

        return self::STATUS_OKAY;
    }

    public function execute() {
        $cmd = $this->get('up');
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $this->proc = proc_open($cmd, $descriptorspec, $this->pipes);
    }

    public function terminate() {
        if (!empty($this->pipes)) {
            foreach ($this->pipes as $i => $pipe) {
                if (is_resource($pipe))
                    fclose($pipe);
                    $this->pipes[$i] = null;
            }
        }

        if (is_resource($this->proc)) {
            proc_close($this->proc);
        }
    }

    /**
     * @return bool
     */
    public function isRunning() {
        if (!is_resource($this->proc)) return false;

        $status = proc_get_status($this->proc);
        return (bool)$status['running'];
    }

    /**
     * @return int|false integer value of the PID or false if the process was not started or has been unset
     */
    public function getRunningPid() {
        if (!is_resource($this->proc)) return false;

        $status = proc_get_status($this->proc);

        return $status['pid'];
    }

    /**
     * @return resource
     */
    public function getStdOut() {
        return $this->pipes[1];
    }

    /**
     * @return resource
     */
    public function getStdErr() {
        return $this->pipes[2];
    }

    /**
     * @return resource
     */
    public function getStdIn() {
        return $this->pipes[0];
    }
}