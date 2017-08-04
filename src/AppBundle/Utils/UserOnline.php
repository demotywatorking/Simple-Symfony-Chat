<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;

class UserOnline
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Method add user's info to user_online table. I am doing it because I will know when user is disconnected from chat
     * for long time
     *
     * @param int $id User's Id
     */
    public function addUserOnline(int $id)
    {
        $online = new \AppBundle\Entity\UserOnline();
        $online->setUserId($id);
        $online->setOnlineTime(new \DateTime('now'));
        
        $this->em->getRepository('AppBundle:UserOnline');
        $this->em->persist($online);
        $this->em->flush();
    }
}