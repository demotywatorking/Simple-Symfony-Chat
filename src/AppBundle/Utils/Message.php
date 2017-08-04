<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;

class Message
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}