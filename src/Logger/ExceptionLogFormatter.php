<?php

namespace Hgabka\LoggerBundle\Logger;

use Monolog\Formatter\LineFormatter;

class ExceptionLogFormatter extends LineFormatter
{
    const SIMPLE_FORMAT = "[%datetime%] %message%\n";

    public function stringify($value) : string
    {
        return $this->convertToString($value);
    }
}
