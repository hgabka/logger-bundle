<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'hg_logger_log_action')]
#[ORM\Index(name: 'user_action_idx', columns: ['user_id'])]
#[ORM\Index(name: 'foreign_id_action_idx', columns: ['foreign_id'])]
#[ORM\Index(name: 'table_name_action_idx', columns: ['table_name'])]
#[ORM\Index(name: 'log_type_action_idx', columns: ['log_type'])]
#[ORM\Index(name: 'original_user_action_idx', columns: ['original_user_id'])]
class LogAction implements ObjectLogInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', name: 'ident', nullable: true)]
    protected ?string $ident = null;

    #[ORM\Column(type: 'integer', name: 'user_id', nullable: true)]
    protected ?int $userId = null;

    #[ORM\Column(type: 'integer', name: 'original_user_id', nullable: true)]
    protected ?int $originalUserId = null;

    #[ORM\Column(type: 'string', name: 'username', nullable: true)]
    protected ?string $username = null;

    #[ORM\Column(type: 'string', name: 'original_username', nullable: true)]
    protected ?string $originalUsername = null;

    #[ORM\Column(type: 'string', name: 'session_id', nullable: true)]
    protected ?string $sessionId = null;

    #[ORM\Column(type: 'datetime', name: 'time', nullable: true)]
    protected ?\DateTime $time = null;

    #[ORM\Column(type: 'datetime', name: 'end_time', nullable: true)]
    protected ?\DateTime $endTime = null;

    #[ORM\Column(type: 'string', name: 'controller', nullable: true)]
    protected ?string $controller = null;

    #[ORM\Column(type: 'text', name: 'description', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: 'text', name: 'request_uri', nullable: true)]
    protected ?string $requestUri;

    #[ORM\Column(type: 'boolean', name: 'success', nullable: true)]
    protected ?bool $success = null;

    #[ORM\Column(type: 'string', name: 'client_ip', nullable: true)]
    protected ?string $clientIp = null;

    #[ORM\Column(type: 'text', name: 'user_agent', nullable: true)]
    protected ?string $userAgent = null;

    #[ORM\Column(type: 'string', name: 'log_type', nullable: true)]
    protected ?string $type = null;

    #[ORM\Column(type: 'string', name: 'method', nullable: true)]
    protected ?string $method = null;

    #[ORM\Column(type: 'text', name: 'post', nullable: true)]
    protected ?string $post = null;

    #[ORM\Column(type: 'text', name: 'request_attributes', nullable: true)]
    protected ?string $requestAttributes = null;

    #[ORM\Column(type: 'string', name: 'table_name', nullable: true)]
    protected ?string $table = null;

    #[ORM\Column(type: 'string', name: 'entity_class', nullable: true)]
    protected ?string $class = null;

    #[ORM\Column(type: 'string', name: 'foreign_id', nullable: true)]
    protected ?string $foreignId = null;

    #[ORM\Column(type: 'text', name: 'extra_parameters', nullable: true)]
    protected ?string $extraParameters = null;

    #[ORM\Column(type: 'string', name: 'priority', length: 10, nullable: true)]
    protected ?string $priority = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdent(): ?string
    {
        return $this->ident;
    }

    /**
     * @param string $ident
     *
     * @return LogAction
     */
    public function setIdent(?string $ident): self
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return LogAction
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalUserId(): ?int
    {
        return $this->originalUserId;
    }

    /**
     * @param int $originalUserId
     *
     * @return LogAction
     */
    public function setOriginalUserId(?int $originalUserId): self
    {
        $this->originalUserId = $originalUserId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return LogAction
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalUsername(): ?string
    {
        return $this->originalUsername;
    }

    /**
     * @param string $originalUsername
     *
     * @return LogAction
     */
    public function setOriginalUsername(?string $originalUsername): self
    {
        $this->originalUsername = $originalUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return LogAction
     */
    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTime(): ?\DateTime
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     *
     * @return LogAction
     */
    public function setTime(?\DateTime $time): self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     *
     * @return LogAction
     */
    public function setEndTime(?\DateTime $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return LogAction
     */
    public function setController(?string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return LogAction
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     *
     * @return LogAction
     */
    public function setRequestUri(?string $requestUri): self
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return LogAction
     */
    public function setSuccess(?bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp
     *
     * @return LogAction
     */
    public function setClientIp(?string $clientIp): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     *
     * @return LogAction
     */
    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return LogAction
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return LogAction
     */
    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getPost(): ?string
    {
        return $this->post;
    }

    /**
     * @param string $post
     *
     * @return LogAction
     */
    public function setPost(?string $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestAttributes(): ?string
    {
        return $this->requestAttributes;
    }

    /**
     * @param string $requestAttributes
     *
     * @return LogAction
     */
    public function setRequestAttributes(?string $requestAttributes): self
    {
        $this->requestAttributes = $requestAttributes;

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return LogAction
     */
    public function setTable(?string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return LogAction
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignId(): ?string
    {
        return $this->foreignId;
    }

    /**
     * @param string $foreignId
     *
     * @return LogAction
     */
    public function setForeignId(?string $foreignId): self
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraParameters(): ?string
    {
        return $this->extraParameters;
    }

    /**
     * @param string $extraParameters
     *
     * @return LogAction
     */
    public function setExtraParameters(?string $extraParameters): self
    {
        $this->extraParameters = $extraParameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return LogAction
     */
    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return LogAction
     */
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return LogAction
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
