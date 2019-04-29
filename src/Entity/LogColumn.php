<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LogColumn.
 *
 * @ORM\Entity
 * @ORM\Table(name="hg_logger_log_column", indexes={
 *     @ORM\Index(name="user", columns={"user_id"}),
 *     @ORM\Index(name="original_user", columns={"original_user_id"})
 * })
 */
class LogColumn
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
     * @ORM\Column(type="string", name="column_name", nullable=true)
     */
    protected $column;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="field_name", nullable=true)
     */
    protected $field;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="foreign_id", nullable=true)
     */
    protected $foreignId;

    /**
     * @var string
     *
     * @ORM\Column(type="hg_utils_longblob", name="old_value", nullable=true)
     */
    protected $oldValue;

    /**
     * @var string
     *
     * @ORM\Column(type="hg_utils_longblob", name="new_value", nullable=true)
     */
    protected $newValue;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="mod_type", nullable=true)
     */
    protected $modType;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="entity_data", nullable=true)
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="session_id", nullable=true)
     */
    protected $sessionId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="controller", nullable=true)
     */
    protected $controller;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="request_uri", nullable=true)
     */
    protected $requestUri;

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
     * @ORM\Column(type="string", name="method", nullable=true)
     */
    protected $method;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="note", nullable=true)
     */
    protected $note;

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
     * @return LogColumn
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
     * @return LogColumn
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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setOriginalUserId($originalUserId)
    {
        $this->originalUserId = $originalUserId;

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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $column
     *
     * @return LogColumn
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return LogColumn
     */
    public function setField($field)
    {
        $this->field = $field;

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
     * @return LogColumn
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @param string $oldValue
     *
     * @return LogColumn
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param string $newValue
     *
     * @return LogColumn
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getModType()
    {
        return $this->modType;
    }

    /**
     * @param string $modType
     *
     * @return LogColumn
     */
    public function setModType($modType)
    {
        $this->modType = $modType;

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
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     *
     * @return Notify
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return LogColumn
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return LogColumn
     */
    public function setNote($note)
    {
        $this->note = $note;

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
     * @return LogColumn
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

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
     * @return LogColumn
     */
    public function setController($controller)
    {
        $this->controller = $controller;

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
     * @return LogColumn
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;

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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

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
     * @return LogColumn
     */
    public function setPost($post)
    {
        $this->post = $post;

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
     * @return LogColumn
     */
    public function setRequestAttributes($requestAttributes)
    {
        $this->requestAttributes = $requestAttributes;

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
     * @return LogColumn
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return LogColumn
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
     * @param string $originalUsername
     *
     * @return LogColumn
     */
    public function setOriginalUsername($originalUsername)
    {
        $this->originalUsername = $originalUsername;

        return $this;
    }
}
