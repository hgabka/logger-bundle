<?php

namespace Hgabka\LoggerBundle\Entity;

interface ObjectLogInterface
{
    public function setTable(?string $table): self;

    public function setForeignId(?string $fk): self;

    public function setClass(?string $class): self;
}
