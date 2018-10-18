<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LogAction.
 *
 * @ORM\Entity
 * @ORM\Table(name="hg_logger_log_action")
 */
class LogAction implements ObjectLogInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="ident", nullable=true)
     */
    protected $ident;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="user_id", nullable=true)
     */
    protected $userId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="original_user_id", nullable=true)
     */
    protected $originalUserId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="username", nullable=true)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="original_username", nullable=true)
     */
    protected $originalUsername;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="session_id", nullable=true)
     */
    protected $sessionId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="time", nullable=true)
     */
    protected $time;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="end_time", nullable=true)
     */
    protected $endTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="controller", nullable=true)
     */
    protected $controller;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="request_uri", nullable=true)
     */
    protected $requestUri;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="success", nullable=true)
     */
    protected $success;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="client_ip", nullable=true)
     */
    protected $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="user_agent", nullable=true)
     */
    protected $userAgent;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="log_type", nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="method", nullable=true)
     */
    protected $method;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="post", nullable=true)
     */
    protected $post;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="request_attributes", nullable=true)
     */
    protected $requestAttributes;
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="table_name", nullable=true)
     */
    protected $table;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="entity_class", nullable=true)
     */
    protected $class;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="foreign_id", nullable=true)
     */
    protected $foreignId;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="extra_parameters", nullable=true)
     */
    protected $extraParameters;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, name="priority", nullable=true)
     */
    protected $priority;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id The unique identifier
     *
     * @return LogAction
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param string $ident
     *
     * @return LogAction
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return LogAction
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalUserId()
    {
        return $this->originalUserId;
    }

    /**
     * @param int $originalUserId
     *
     * @return LogAction
     */
    public function setOriginalUserId($originalUserId)
    {
        $this->originalUserId = $originalUserId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return LogAction
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     *
     * @return LogAction
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     *
     * @return LogAction
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return LogAction
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return LogAction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     *
     * @return LogAction
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return LogAction
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp
     *
     * @return LogAction
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     *
     * @return LogAction
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return LogAction
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return LogAction
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return LogAction
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestAttributes()
    {
        return $this->requestAttributes;
    }

    /**
     * @param string $requestAttributes
     *
     * @return LogAction
     */
    public function setRequestAttributes($requestAttributes)
    {
        $this->requestAttributes = $requestAttributes;

        return $this;
    }

    /**
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param string $post
     *
     * @return LogAction
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return LogAction
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Sets createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getExtraParameters()
    {
        return $this->extraParameters;
    }

    /**
     * @param string $extraParameters
     *
     * @return LogAction
     */
    public function setExtraParameters($extraParameters)
    {
        $this->extraParameters = $extraParameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return LogAction
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return LogAction
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * @param string $foreignId
     *
     * @return LogAction
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return LogAction
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalUsername()
    {
        return $this->originalUsername;
    }

    /**
     * @param int $originalUsername
     *
     * @return LogAction
     */
    public function setOriginalUsername($originalUsername)
    {
        $this->originalUsername = $originalUsername;

        return $this;
    }
}
