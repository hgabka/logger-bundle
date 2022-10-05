<?php

namespace Hgabka\LoggerBundle\Helper;

abstract class AbstractLoggableEntity implements LoggableEntityInterface
{
    public function getNotLogFields(): ?array
    {
        return null;
    }
}
