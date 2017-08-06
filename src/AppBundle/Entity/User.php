<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="UserOnline", mappedBy="userInfo")
     */
    protected $userOnline;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="userInfo")
     */
    protected $userMessage;

    public function __construct()
    {
        parent::__construct();
        $this->userMessage = new ArrayCollection();
    }

    public function getChatRoleAsText()
    {
        $role = $this->getRoles();
        switch ($role[0]) {
            case 'ROLE_ADMIN':
                return 'administrator';
            case 'ROLE_MODERATOR':
                return 'moderator';
            default:
                return 'user';
        }

    }
}
