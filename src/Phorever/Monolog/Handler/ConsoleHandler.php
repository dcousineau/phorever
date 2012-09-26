<?php
namespace Phorever\Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHandler extends AbstractProcessingHandler {
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output, $level = Logger::DEBUG, $bubble = true) {
        parent::__construct($level, $bubble);
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->output->write($record['formatted']);
    }
}