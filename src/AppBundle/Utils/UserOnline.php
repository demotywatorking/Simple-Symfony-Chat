<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;

class UserOnline
{
    private $em;
    private $config;

    public function __construct(EntityManagerInterface $em, ChatConfig $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * Method add user's info to user_online table. I am doing it because I will know when user is disconnected from chat
     * for long time
     *
     * @param int $id User's Id
     */
    public function addUserOnline($user)
    {
        if ( $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $user->getId()
            ])
        ) {
            return;
        }

        $online = new \AppBundle\Entity\UserOnline();

        $online->setUserId($user->getId());
        $online->setOnlineTime(new \DateTime('now'));
        $online->setUserInfo($user);

        $this->em->persist($online);
        $this->em->flush();
    }

    /**
     * Update User's Time in Database - User will not be kicked for inactivity
     *
     * @param $id User's Id
     */
    public function updateUserOnline($id)
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
                        ->findOneBy([
                            'userId' => $id
                        ]);
        $online->setOnlineTime(new \DateTime('now'));

        $this->em->persist($online);
        $this->em->flush();
    }

    /**
     * Get array with online Users
     *
     * @param int $id User's Id
     * @return array array of online Users
     */
    public function getOnlineUsers(int $id)
    {
        $this->deleteInactiveUsers($id);
        $usersOnline = $this->em->getRepository('AppBundle:UserOnline')
            ->findAll();
        foreach ($usersOnline as $user) {
           // $user->setUsername($user->getUserInfo()->getUsername());
        }
        /**
         * TODO:
         * relationship with user, get user name etc.
         */

        return $usersOnline;
    }

    public function deleteUserWhenLogout(int $id)
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $id
            ]);
        $this->em->remove($online);
        $this->em->flush();
    }

    /**
     * Delete Inactive Users from database except current User
     *
     * @param int $id User's Id
     */
    private function deleteInactiveUsers(int $id)
    {
        $time = new \DateTime('now');
        $time->modify('-'.$this->config->getInactiveTime().'sec');

        $this->em->getRepository('AppBundle:UserOnline')
                ->deleteInactiveUsers($time, $id);
    }
}