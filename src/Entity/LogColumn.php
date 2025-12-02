<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'hg_logger_log_column')]
#[ORM\Index(name: 'user_column_idx', columns: ['user_id'])]
#[ORM\Index(name: 'original_user_column_idx', columns: ['original_user_id'])]
class LogColumn
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, name: 'ident', nullable: true)]
    protected ?string $ident = null;

    #[ORM\Column(type: Types::INTEGER, name: 'user_id', nullable: true)]
    protected ?int $userId = null;

    #[ORM\Column(type: Types::INTEGER, name: 'original_user_id', nullable: true)]
    protected ?int $originalUserId = null;

    #[ORM\Column(type: Types::STRING, name: 'username', nullable: true)]
    protected ?string $username = null;

    #[ORM\Column(type: Types::STRING, name: 'original_username', nullable: true)]
    protected ?string $originalUsername = null;

    #[ORM\Column(type: Types::STRING, name: 'table_name', nullable: true)]
    protected ?string $table = null;

    #[ORM\Column(type: Types::STRING, name: 'entity_class', nullable: true)]
    protected ?string $class = null;

    #[ORM\Column(type: Types::STRING, name: 'column_name', nullable: true)]
    protected ?string $column = null;

    #[ORM\Column(type: Types::STRING, name: 'field_name', nullable: true)]
    protected ?string $field = null;

    #[ORM\Column(type: Types::STRING, name: 'foreign_id', nullable: true)]
    protected ?string $foreignId = null;

    #[ORM\Column(type: 'hg_utils_longblob', name: 'old_value', nullable: true)]
    protected ?string $oldValue = null;

    #[ORM\Column(type: 'hg_utils_longblob', name: 'new_value', nullable: true)]
    protected ?string $newValue = null;

    #[ORM\Column(type: Types::STRING, name: 'mod_type', nullable: true)]
    protected ?string $modType = null;

    #[ORM\Column(type: Types::TEXT, name: 'entity_data', nullable: true)]
    protected ?string $data = null;

    #[ORM\Column(type: Types::STRING, name: 'session_id', nullable: true)]
    protected ?string $sessionId = null;

    #[ORM\Column(type: Types::STRING, name: 'controller', nullable: true)]
    protected ?string $controller = null;

    #[ORM\Column(type: Types::TEXT, name: 'request_uri', nullable: true)]
    protected ?string $requestUri = null;

    #[ORM\Column(type: Types::STRING, name: 'client_ip', nullable: true)]
    protected ?string $clientIp = null;

    #[ORM\Column(type: Types::TEXT, name: 'user_agent', nullable: true)]
    protected ?string $userAgent = null;

    #[ORM\Column(type: Types::TEXT, name: 'post', nullable: true)]
    protected ?string $post = null;

    #[ORM\Column(type: Types::TEXT, name: 'request_attributes', nullable: true)]
    protected ?string $requestAttributes = null;

    #[ORM\Column(type: Types::STRING, name: 'method', nullable: true)]
    protected ?string $method = null;

    #[ORM\Column(type: Types::TEXT, name: 'note', nullable: true)]
    protected ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'created_at')]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'updated_at')]
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
     * @return LogColumn
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
     * @return LogColumn
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
     * @return LogColumn
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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setOriginalUsername(?string $originalUsername): self
    {
        $this->originalUsername = $originalUsername;

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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumn(): ?string
    {
        return $this->column;
    }

    /**
     * @param string $column
     *
     * @return LogColumn
     */
    public function setColumn(?string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return LogColumn
     */
    public function setField(?string $field): self
    {
        $this->field = $field;

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
     * @return LogColumn
     */
    public function setForeignId(?string $foreignId): self
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    /**
     * @param string $oldValue
     *
     * @return LogColumn
     */
    public function setOldValue(?string $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    /**
     * @param string $newValue
     *
     * @return LogColumn
     */
    public function setNewValue(?string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getModType(): ?string
    {
        return $this->modType;
    }

    /**
     * @param string $modType
     *
     * @return LogColumn
     */
    public function setModType(?string $modType): self
    {
        $this->modType = $modType;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return LogColumn
     */
    public function setData(?string $data): self
    {
        $this->data = $data;

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
     * @return LogColumn
     */
    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;

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
     * @return LogColumn
     */
    public function setController(?string $controller): self
    {
        $this->controller = $controller;

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
     * @return LogColumn
     */
    public function setRequestUri(?string $requestUri): self
    {
        $this->requestUri = $requestUri;

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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

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
     * @return LogColumn
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
     * @return LogColumn
     */
    public function setRequestAttributes(?string $requestAttributes): self
    {
        $this->requestAttributes = $requestAttributes;

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
     * @return LogColumn
     */
    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return LogColumn
     */
    public function setNote(?string $note): self
    {
        $this->note = $note;

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
     * @return LogColumn
     */
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return LogColumn
     */
    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
