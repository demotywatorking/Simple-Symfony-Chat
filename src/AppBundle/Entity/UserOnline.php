<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserOnline
 *
 * @ORM\Table(name="user_online")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserOnlineRepository")
 */
class UserOnline
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", unique=true)
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="online_time", type="datetime")
     */
    private $onlineTime;


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
     * Set userId
     *
     * @param integer $userId
     *
     * @return UserOnline
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set onlineTime
     *
     * @param \DateTime $onlineTime
     *
     * @return UserOnline
     */
    public function setOnlineTime($onlineTime)
    {
        $this->onlineTime = $onlineTime;

        return $this;
    }

    /**
     * Get onlineTime
     *
     * @return \DateTime
     */
    public function getOnlineTime()
    {
        return $this->onlineTime;
    }
}
