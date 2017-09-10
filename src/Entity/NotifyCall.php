<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Hgabka\LoggerBundle\Entity\Notify;

/**
 * NotifyCall
 *
 * @ORM\Entity
 * @ORM\Table(name="hg_logger_notify_call")
 */
class NotifyCall
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var Notify
     *
     * @ORM\ManyToOne(targetEntity="Hgabka\LoggerBundle\Entity\Notify", inversedBy="calls", cascade={"persist"})
     * @ORM\JoinColumn(name="notify_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $notify;
		
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="server", nullable=true)
     */
    private $server;
	
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
     * @var ArrayCollection|NotifyCall[]
     *
     * @ORM\OneToMany(targetEntity="Hgabka\LoggerBundle\Entity\NotifyCall", cascade={"all"}, mappedBy="notify", orphanRemoval=true)
     *
     * @Assert\Valid()
     */
    private $calls;
	
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id The unique identifier
     *
     * @return Notify
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
	
    /**
     * Sets createdAt.
     *
     * @param  \DateTime $createdAt
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
     * Sets updatedAt.
     *
     * @param  \DateTime $updatedAt
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
}
