<?php

namespace Hgabka\LoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Hgabka\LoggerBundle\Entity\NotifyCall;

/**
 * Notify
 *
 * @ORM\Entity(repositoryClass="Hgabka\LoggerBundle\Repository\NotifyRepository")
 * @ORM\Table(name="hg_logger_notify")
 */
class Notify
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
     * @ORM\Column(type="string", name="controller", nullable=true)
     */
    private $controller;
	
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="action", nullable=true)
     */
    private $action;
	
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="exception_class", nullable=true)
     */
    private $exceptionClass;
	
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="message", nullable=true)
     */
    private $message;
	
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="traces", nullable=true)
     */
    private $traces;
	
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="redirect_url", nullable=true)
     */
    private $redirectUrl;
	
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="request_uri", nullable=true)
     */
    private $requestUri;
	
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="post", nullable=true)
     */
    private $post;
	
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="request", nullable=true)
     */
    private $request;
	
    /**
     * @var string
     *
     * @ORM\Column(type="text", name="params", nullable=true)
     */
    private $params;
	
    /**
     * @var string
     *
     * @ORM\Column(type="int", name="call_number", nullable=true)
     */
    private $callNumber;
	
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="hash", nullable=true)
     */
    private $hash;
	
    /**
     * @var string
     *
     * @ORM\Column(type="boolean", name="send_again", nullable=true)
     */
    private $sendAgain = false;
	
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
