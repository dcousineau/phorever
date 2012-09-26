<?php
namespace Phorever\Monolog\Formatter;

use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;

class ConsoleFormatter extends NormalizerFormatter {
    /**
     * Array of formats, ORDER BY SEVERITY FROM HIGHEST TO LOWEST OR SELECTION WILL NOT WORK
     *
     * @var array
     */
    protected static $formats = array(
        Logger::ERROR   => "<error>[%datetime%] %message%</error>\n",
        Logger::WARNING => "<comment>[%datetime%] %message%</comment>\n",
        Logger::INFO    => "[%datetime%] %message%\n",
    );

    protected static function getFormatForLevel($level) {
        foreach (self::$formats as $severity => $format) {
            if ($level >= $severity) return $format;
        }

        return self::$formats[Logger::INFO];
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::format($record);

        $output = self::getFormatForLevel($record['level']);

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->convertToString($val), $output);
                unset($vars['extra'][$var]);
            }
        }
        foreach ($vars as $var => $val) {
            $output = str_replace('%'.$var.'%', $this->convertToString($val), $output);
        }

        return $output;
    }

    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    protected function normalize($data)
    {
        if (is_bool($data) || is_null($data)) {
            return var_export($data, true);
        }

        return parent::normalize($data);
    }

    protected function convertToString($data)
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }


        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $json_string = json_encode($this->normalize($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $json_string = stripslashes(json_encode($this->normalize($data)));
        }

        if ($json_string == '[]') return '';
        else return $json_string;
    }
}