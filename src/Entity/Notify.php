<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'hg_logger_notify')]
class Notify
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', name: 'controller', nullable: true)]
    protected ?string $controller = null;

    #[ORM\Column(type: 'string', name: 'exception_class', nullable: true)]
    protected ?string $exceptionClass = null;

    #[ORM\Column(type: 'text', name: 'message', nullable: true)]
    protected ?string $message = null;

    #[ORM\Column(type: 'integer', name: 'code', nullable: true)]
    protected ?int $code = null;

    #[ORM\Column(type: 'string', name: 'file', nullable: true)]
    protected ?string $file = null;

    #[ORM\Column(type: 'integer', name: 'line', nullable: true)]
    protected ?int $line = null;

    #[ORM\Column(type: 'text', name: 'traces', nullable: true)]
    protected ?string $traces = null;

    #[ORM\Column(type: 'string', name: 'server_name', nullable: true)]
    protected ?string $serverName = null;

    #[ORM\Column(type: 'text', name: 'redirect_url', nullable: true)]
    protected ?string $redirectUrl = null;

    #[ORM\Column(type: 'text', name: 'request_uri', nullable: true)]
    protected ?string $requestUri = null;

    #[ORM\Column(type: 'text', name: 'post', nullable: true)]
    protected ?string $post = null;

    #[ORM\Column(type: 'text', name: 'request', nullable: true)]
    protected ?string $request = null;

    #[ORM\Column(type: 'text', name: 'params', nullable: true)]
    protected ?string $params = null;

    #[ORM\Column(type: 'integer', name: 'call_number', nullable: true)]
    protected ?int $callNumber = null;

    #[ORM\Column(type: 'string', name: 'hash', nullable: true)]
    protected ?string $hash = null;

    #[ORM\Column(type: 'boolean', name: 'send_again', nullable: true)]
    protected ?bool $sendAgain = false;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTime $updatedAt = null;

    #[ORM\OneToMany(targetEntity: NotifyCall::class, cascade: ['all'], mappedBy: 'notify', orphanRemoval: true)]
    #[Assert\Valid]
    protected Collection|array|null $calls;

    /**
     * Notify constructor.
     */
    public function __construct()
    {
        $this->calls = new ArrayCollection();
    }

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
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return Notify
     */
    public function setController(?string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getExceptionClass(): ?string
    {
        return $this->exceptionClass;
    }

    /**
     * @param string $exceptionClass
     *
     * @return Notify
     */
    public function setExceptionClass(?string $exceptionClass): self
    {
        $this->exceptionClass = $exceptionClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Notify
     */
    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): ?int
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return Notify
     */
    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return Notify
     */
    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return int
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * @param int $line
     *
     * @return Notify
     */
    public function setLine(?int $line): self
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return string
     */
    public function getTraces(): ?string
    {
        return $this->traces;
    }

    /**
     * @param string $traces
     *
     * @return Notify
     */
    public function setTraces(?string $traces): self
    {
        $this->traces = $traces;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     *
     * @return Notify
     */
    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

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
     * @return Notify
     */
    public function setRequestUri(?string $requestUri): self
    {
        $this->requestUri = $requestUri;

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
     * @return Notify
     */
    public function setPost(?string $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequest(): ?string
    {
        return $this->request;
    }

    /**
     * @param string $request
     *
     * @return Notify
     */
    public function setRequest(?string $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getParams(): ?string
    {
        return $this->params;
    }

    /**
     * @param string $params
     *
     * @return Notify
     */
    public function setParams(?string $params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return int
     */
    public function getCallNumber(): ?int
    {
        return $this->callNumber;
    }

    /**
     * @param int $callNumber
     *
     * @return Notify
     */
    public function setCallNumber(?int $callNumber): self
    {
        $this->callNumber = $callNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Notify
     */
    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string
     */
    public function getSendAgain(): bool|string|null
    {
        return $this->sendAgain;
    }

    /**
     * @param string $sendAgain
     *
     * @return Notify
     */
    public function setSendAgain(bool|string|null $sendAgain): self
    {
        $this->sendAgain = $sendAgain;

        return $this;
    }

    /**
     * @return ArrayCollection|NotifyCall[]
     */
    public function getCalls(): Collection|array|null
    {
        return $this->calls;
    }

    /**
     * @param ArrayCollection|NotifyCall[] $calls
     *
     * @return Notify
     */
    public function setCalls(Collection|array|null $calls): self
    {
        $this->calls = $calls;

        return $this;
    }

    /**
     * Add call.
     *
     * @param NotifyCall $call
     *
     * @return Notify
     */
    public function addCall(NotifyCall $call): self
    {
        if (!$this->calls->contains($call)) {
            $this->calls[] = $call;

            $call->setNotify($this);
        }

        return $this;
    }

    /**
     * Remove call.
     *
     * @param NotifyCall $call
     */
    public function removeCall(NotifyCall $call): self
    {
        $this->calls->removeElement($call);

        return $this;
    }

    /**
     * Sets createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     *
     * @return Notify
     */
    public function setServerName(?string $serverName): self
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * Sets updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
