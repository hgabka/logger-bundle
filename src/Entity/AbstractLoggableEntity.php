<?php

namespace Hgabka\LoggerBundle\Entity;

use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;

abstract class AbstractLoggableEntity implements LoggableEntityInterface
{
    public function getNotLogFields(): ?array
    {
        return null;
    }
}
