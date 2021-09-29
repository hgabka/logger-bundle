<?php

namespace Hgabka\LoggerBundle\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class ExceptionLogger
{
    protected $logger;

    public function __construct(LoggerInterface $logger, FormatterInterface $formatter, $path)
    {
        $this->logger = $logger;
        if (!empty($path)) {
            $handler = new StreamHandler($path.'/'.date('Ymd').'.log');
            $handler->setFormatter($formatter);
            $this->logger->setHandlers([$handler]);
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return ExceptionLogger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
