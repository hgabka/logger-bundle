<?php

namespace Hgabka\LoggerBundle\Helper;

interface LoggableEntityInterface
{
    public function getLogFields(): ?array;

    public function getNotLogFields(): ?array;
}
