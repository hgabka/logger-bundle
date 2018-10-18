<?php

namespace Hgabka\LoggerBundle\Entity;

interface ObjectLogInterface
{
    public function setTable($table);

    public function setForeignId($fk);

    public function setClass($class);
}
