<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'hg_logger_notify_call')]
class NotifyCall
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Notify::class, inversedBy: 'calls', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'notify_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Notify $notify = null;

    #[ORM\Column(type: 'text', name: '`server`', nullable: true)]
    protected ?string $server = null;

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

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNotify(): ?Notify
    {
        return $this->notify;
    }

    public function setNotify(?Notify $notify): self
    {
        $this->notify = $notify;

        return $this;
    }

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function setServer(?string $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
