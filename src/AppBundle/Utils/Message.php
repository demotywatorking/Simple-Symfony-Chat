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
        $channel = $this->session->get('channel');

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastDay($this->config->getMessageLimit(), $channel);

        if ($messages) {
            $this->session->set('lastId', $messages[0]->getId());
        } else {
            $this->session->set(
                'lastId',
                $this->em->getRepository('AppBundle:Message')
                ->getIdFromLastMessage()
            );
        }

        return  $this->checkIfMessagesCanBeDisplayed($messages);
    }

    public function getMessagesFromLastId()
    {
        $lastId = $this->session->get('lastId');
        $channel = $this->session->get('channel');
        //only when channel was changed
        if($this->session->get('changedChannel')) {
            $this->session->remove('changedChannel');
            return $this->getMessagesAfterChangingChannel($channel);
        }

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId($lastId, $this->config->getMessageLimit(), $channel);

        //if get new messages, update var lastId in session
        if (end($messages)) {
            $this->session->set('lastId', end($messages)->getId());
        }

        $messagesSerialized = $this->checkIfMessagesCanBeDisplayed($messages);
        $this->serializeMessages($messagesSerialized);

        return $messagesSerialized;
    }

    private function getMessagesAfterChangingChannel(int $channel)
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastIdAfterChangingChannel($this->config->getMessageLimit(), $channel);

        $lastId = $this->em->getRepository('AppBundle:Message')
            ->getIdFromLastMessage();
        $this->session->set('lastId', $lastId);

        $messagesSerialized = $this->checkIfMessagesCanBeDisplayed($messages);
        $this->serializeMessages($messagesSerialized);

        return  $messagesSerialized;
    }

    public function addMessageToDatabase(User $user, int $channel, string $text):array
    {
        if (false === $this->validateMessage($user, $channel, $text)) {
            return ['status' => 'false'];
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
            return ['status' => 'false'];
        }
        //check if there was new messages between last message and send message
        if (($this->session->get('lastId') + 1) != $message->getId()) {
            $messages = $this->em->getRepository('AppBundle:Message')
                ->getMessagesBetweenIds(
                    $this->session->get('lastId'),
                    $message->getId(),
                    $channel
                );
            $messagesSerialized = $this->checkIfMessagesCanBeDisplayed($messages);
            $this->serializeMessages($messagesSerialized);
        }

        $this->session->set('lastId', $message->getId());

        return ['status' => 'true', 'messages' => $messagesSerialized ?? ''];
    }

    private function checkIfMessagesCanBeDisplayed(array $messages)
    {
        //check if message is not priv message or something
        return $messages;
    }

    private function validateMessage(User $user, int $channel, string $text):bool
    {
        if (!(strlen(trim($text)) > 0)) {
            return false;
        }
        if ($user->getId() <= 0) {
            return false;
        }
        if (!array_key_exists($channel, $this->config->getChannels())) {
            return false;
        }
        return true;
    }

    private function serializeMessages(&$messagesSerialized)
    {
        foreach ($messagesSerialized as &$message) {
            $message = $message->createArrayToJson();
        }
    }
}