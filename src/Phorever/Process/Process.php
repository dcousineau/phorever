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

    public function tick() {
        $now = microtime(true);

        if (!$this->isRunning()) {
            if (!$this->down) {
                //Down state is new, log it
                $this->down = $now;
            }

            //If process has been down
            if ($now - $this->down >= $this->get('resurrect_after', 60)) {
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
            foreach ($this->pipes as &$pipe) {
                if (is_resource($pipe))
                    fclose($pipe);
            }
        }

        if (is_resource($this->proc)) {
            proc_close($this->proc);
        }
    }

    public function isRunning() {
        if (!is_resource($this->proc)) return false;

        $status = proc_get_status($this->proc);
        return (bool)$status['running'];
    }

    public function getRunningPid() {
        if (!is_resource($this->proc)) return false;

        $status = proc_get_status($this->proc);

        return $status['pid'];
    }
}