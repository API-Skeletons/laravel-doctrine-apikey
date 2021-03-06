<?php

namespace ApiSkeletons\Laravel\Doctrine\ApiKey\Entity;

/**
 * AdminEvent
 */
class AdminEvent
{
    /**
     * @var string
     */
    private $ip_address;

    /**
     * @var string
     */
    private $event;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey
     */
    private $apiKey;


    /**
     * Set ipAddress.
     *
     * @param string $ipAddress
     *
     * @return AdminEvent
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress.
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set event.
     *
     * @param string $event
     *
     * @return AdminEvent
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return AdminEvent
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

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
     * Set apiKey.
     *
     * @param \ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey $apiKey
     *
     * @return AdminEvent
     */
    public function setApiKey(\ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey.
     *
     * @return \ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
