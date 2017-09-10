<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hgabka\LoggerBundle\Logger;

use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class ExceptionLogger
{
    protected $logger;

    public function __construct(LoggerInterface $logger, $path)
    {
        $this->logger = $logger;
        $handler = new StreamHandler($path.'/'.date('Ymd').'.log');
        $handler->setFormatter(new ExceptionLogFormatter());
        $this->logger->setHandlers([$handler]);
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
