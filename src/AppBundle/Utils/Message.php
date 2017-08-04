<?php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Message
{
    private const MAX_MESSAGES = 32;
    private $em;
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function getNewMessages()
    {
        $lastId = $this->session->get('lastid');

        if (!$lastId) {
            return $this->getMessagesFromLastDay();
        }
        return $this->getMessagesFromLastId($lastId);
    }

    private function getMessagesFromLastDay()
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastDay(self::MAX_MESSAGES);

        $lastId = end($messages);
        $this->session->set('lastid', $lastId->getId());

        return  $this->checkIfMessagesCanBeDisplayed($messages);
    }

    private function getMessagesFromLastId(int $lastId)
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId($lastId, self::MAX_MESSAGES);

        return  $this->checkIfMessagesCanBeDisplayed($messages);
    }

    private function checkIfMessagesCanBeDisplayed(array $messages)
    {
        return $messages;
    }
}