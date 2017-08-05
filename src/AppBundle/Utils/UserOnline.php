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
     *
     */
    public function addUserOnline($user, int $channel)
    {
        if ( $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $user->getId(),
                'channel' => $channel
            ])
        ) {
            return;
        }

        $online = new \AppBundle\Entity\UserOnline();

        $online->setUserId($user->getId());
        $online->setOnlineTime(new \DateTime('now'));
        $online->setUserInfo($user);
        $online->setChannel($channel);

        $this->em->persist($online);
        $this->em->flush();
    }

    /**
     * Update User's Time in Database - User will not be kicked for inactivity
     *
     * @param $id User's Id
     */
    public function updateUserOnline(int $id, int $channel)
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
                        ->findOneBy([
                            'userId' => $id
                        ]);
        $online->setOnlineTime(new \DateTime('now'));
        $online->setChannel($channel);

        $this->em->persist($online);
        $this->em->flush();
    }

    /**
     * Get array with online Users
     *
     * @param int $id User's Id
     * @return array array of online Users
     */
    public function getOnlineUsers(int $id, int $channel)
    {
        $this->deleteInactiveUsers($id, $channel);
        $usersOnline = $this->em->getRepository('AppBundle:UserOnline')
            ->findAll();

        return $usersOnline;
    }

    public function deleteUserWhenLogout(int $id)
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $id,
            ]);
        $this->em->remove($online);
        $this->em->flush();
    }

    /**
     * Delete Inactive Users from database except current User
     *
     * @param int $id User's Id
     */
    private function deleteInactiveUsers(int $id, int $channel)
    {
        $time = new \DateTime('now');
        $time->modify('-'.$this->config->getInactiveTime().'sec');

        $this->em->getRepository('AppBundle:UserOnline')
                ->deleteInactiveUsers($time, $id, $channel);
    }
}