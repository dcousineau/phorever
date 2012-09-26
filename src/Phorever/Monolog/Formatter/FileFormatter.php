<?php
namespace Phorever\Monolog\Formatter;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class FileFormatter extends LineFormatter {
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = parent::format($record);
        return strip_tags($output);
    }
}