<?php

namespace AppBundle\Utils;


use Doctrine\ORM\EntityManagerInterface;

class AdminPanel
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAllUsers()
    {
        return $this->em->getRepository('AppBundle:User')->findAll();
    }

    public function changeUsersRole(int $id, string $role)
    {
        $user = $this->em->getRepository('AppBundle:User')->findOneBy([
            'id' => $id
        ]);
        if (!$user) {
            return;
        }

        $user->changeRole($role);

        $this->em->persist($user);
        $this->em->flush();
    }
}