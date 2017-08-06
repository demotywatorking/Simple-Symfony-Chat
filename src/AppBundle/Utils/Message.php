<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Message
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var ChatConfig
     */
    private $config;

    /**
     * Message constructor.
     *
     * @param EntityManagerInterface $em
     * @param SessionInterface $session
     * @param ChatConfig $config
     */
    public function __construct(EntityManagerInterface $em, SessionInterface $session, ChatConfig $config)
    {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
    }

    public function getMessagesInIndex()
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastDay($this->config->getMessageLimit());

        if ($messages) {
            $this->session->set('lastId', $messages[0]->getId());
        } else {
            $this->session->set('lastId', 0);
        }

        return  $this->checkIfMessagesCanBeDisplayed($messages);
    }

    public function getMessagesFromLastId(int $lastId)
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId($lastId, $this->config->getMessageLimit());

        if (end($messages)) {
            $this->session->set('lastId', end($messages)->getId());
        }

        $messagesSerialized = $this->checkIfMessagesCanBeDisplayed($messages);
        foreach ($messagesSerialized as &$message) {
            $message = $message->createArrayToJson();
        }

        return $messagesSerialized;
    }

    public function addMessageToDatabase(User $user, int $channel, string $text):bool
    {
        if (false === $this->validateMessage($text)) {
            return false;
        }

        $message = new \AppBundle\Entity\Message();
        $message->setUserInfo($user);
        $message->setChannel($channel);
        $message->setText($text);
        $message->setDate(new \DateTime());
        $this->em->getRepository('AppBundle:Message');

        try {
            $this->em->persist($message);
            $this->em->flush();
        } catch(\Throwable $e) {
            return false;
        }

        $this->session->set('lastId', $message->getId());
        return true;
    }

    private function checkIfMessagesCanBeDisplayed(array $messages)
    {
        return $messages;
    }

    private function validateMessage(string $text):bool
    {
        if ('' == $text) {
            return false;
        }
        return true;
    }
}