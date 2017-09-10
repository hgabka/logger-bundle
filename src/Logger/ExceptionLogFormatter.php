<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hgabka\LoggerBundle\Logger;

use Monolog\Formatter\LineFormatter;

class ExceptionLogFormatter extends LineFormatter
{
    const SIMPLE_FORMAT = "[%datetime%] %message%\n";

    public function stringify($value)
    {
        return $this->convertToString($value);
    }
}
