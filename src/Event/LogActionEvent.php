<?php

namespace Hgabka\LoggerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class LogActionEvent extends Event
{
    public const EVENT_START = 'hgabka_logger.log_start';
    public const EVENT_DONE = 'hgabka_logger.log_done';
    public const EVENT_UPDATE = 'hgabka_logger.log_update';
    public const EVENT_LOG = 'hgabka_logger.log_log';
    public const EVENT_FORM = 'hgabka_logger.log_form';

    /** @var string */
    protected $type;

    /** @var array */
    protected $parameters;

    /** @var array|\Traversable */
    protected $extraParameters;

    /** @var string */
    protected $priority;

    /** @var object */
    protected $object;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return LogActionEvent
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return LogActionEvent
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return LogActionEvent
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return array|\Traversable
     */
    public function getExtraParameters()
    {
        return $this->extraParameters;
    }

    /**
     * @param array|\Traversable $extraParameters
     *
     * @return LogActionEvent
     */
    public function setExtraParameters($extraParameters)
    {
        $this->extraParameters = $extraParameters;

        return $this;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param object $object
     *
     * @return LogActionEvent
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }
}
