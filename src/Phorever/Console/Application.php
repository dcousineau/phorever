<?php
namespace Phorever\Console;

use Phorever\Phorever;
use Phorever\Console\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @var Phorever
     */
    protected $phorever;

    public function __construct(Phorever $phorever = null) {
        $this->phorever = $phorever;
        parent::__construct('Phorever', '0.1.0');
    }

    public function getDefaultCommands() {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\StartCommand();
        $commands[] = new Command\StopCommand();
        $commands[] = new Command\StatusCommand();

        return $commands;
    }



    /**
     * @param Phorever $daemon
     */
    public function setPhorever(Phorever $phorever) {
        $this->phorever = $phorever;
    }

    /**
     * @return Phorever
     */
    public function getPhorever() {
        return $this->phorever;
    }
}