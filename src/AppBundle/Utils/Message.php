<?php

namespace AppBundle\Utils;


use Doctrine\ORM\EntityManager;

class Message
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}