<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service to preparing messages from database to array or check if new message can be add to database
 *
 * Class Message
 * @package AppBundle\Utils
 */
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
     * @var SpecialMessages
     */
    private $specialMessages;

    public function __construct(EntityManagerInterface $em, SessionInterface $session, ChatConfig $config, SpecialMessages $special)
    {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
        $this->specialMessages = $special;
    }

    /**
     * Gets messages from last 24h limited by chat limit, than set id of last message to session
     * than change messages from entitys to array
     *
     * @return array Array of messages changed to array
     */
    public function getMessagesInIndex(User $user)
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
        $this->changeMessagesToArray($messages, $user);

        return  $this->checkIfMessagesCanBeDisplayed($messages);
    }

    /**
     * Gets messages from database from last id read from session, then set id of last message to session if any message exists,
     * than change messages from entitys to array and checking if messages can be displayed
     *
     * @return array Array of messages changed to array
     */
    public function getMessagesFromLastId(User $user)
    {
        $lastId = $this->session->get('lastId');
        $channel = $this->session->get('channel');
        //only when channel was changed
        if($this->session->get('changedChannel')) {
            $this->session->remove('changedChannel');
            return $this->getMessagesAfterChangingChannel($channel, $user);
        }

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId($lastId, $this->config->getMessageLimit(), $channel);

        //if get new messages, update var lastId in session
        if (end($messages)) {
            $this->session->set('lastId', end($messages)->getId());
        }
        $this->changeMessagesToArray($messages, $user);

        $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages);

        return $messagesToDisplay;
    }

    /**
     * Gets messages from last 24h from new channel, then set id of last message to session if any message exists,
     * than change messages from entitys to array and checking if messages can be displayed
     *
     * @param int $channel Channel's Id
     *
     * @return array Array of messages changed to array
     */
    private function getMessagesAfterChangingChannel(int $channel, User $user)
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastIdAfterChangingChannel($this->config->getMessageLimit(), $channel);

        $lastId = $this->em->getRepository('AppBundle:Message')
            ->getIdFromLastMessage();
        $this->session->set('lastId', $lastId);

        $this->changeMessagesToArray($messages, $user);
        $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages);

        return  $messagesToDisplay;
    }

    /**
     * Validates messages and adds message to database, checks if there are new messages from last refresh,
     * save sent message's id to session as lastid
     *
     * @param User $user User instance, who is sending message
     *
     * @param string $text Message's text
     *
     * @return array status of adding messages, and new messages from last refresh
     */
    public function addMessageToDatabase(User $user, string $text):array
    {
        $channel = $this->session->get('channel');
        if (false === $this->validateMessage($user, $channel, $text)) {
            return ['status' => 'false'];
        }

        $special = $this->specialMessages->specialMessages($text, $user);

        if ($special['userId'] == 1000000) {
            $originalUser = $user;
            $user = $this->em->find('AppBundle:User', 1000000);
            $text = $special['text'];
        }
        $text = htmlentities($text);

        $message = new \AppBundle\Entity\Message();
        $message->setUserInfo($user);
        $message->setChannel($channel);
        $message->setText($text);
        $message->setDate(new \DateTime());

        try {
            $this->em->persist($message);
            $this->em->flush();
        } catch(\Throwable $e) {
            return ['status' => 'false'];
        }
        if (isset($originalUser)) {
            $user = $originalUser;
        }
        $id = $message->getId();
        //check if there was new messages between last message and send message
        if (($this->session->get('lastId') + 1) != $message->getId()) {
            $messages = $this->em->getRepository('AppBundle:Message')
                ->getMessagesBetweenIds(
                    $this->session->get('lastId'),
                    $message->getId(),
                    $channel
            );

            $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages);
            $this->changeMessagesToArray($messagesToDisplay, $user);
        }

        $this->session->set('lastId', $message->getId());

        return [
            'id' => $id,
            'userName' => $special['userId'] ? 'BOT' : $user->getUsername(),
            'text' => $special['showText'] ?? $text,
            'status' => 'true',
            'messages' => $messagesToDisplay ?? ''
        ];
    }

    /**
     * Deleting message from database
     *
     * @param int $id Message's id
     *
     * @param User $user User instance
     *
     * @return array status of deleting messages
     */
    public function deleteMessage(int $id, User $user)
    {
        $channel = $this->session->get('channel');
        $status =  $this->em->getRepository('AppBundle:Message')
                        ->deleteMessage($id);

        $message = new \AppBundle\Entity\Message();
        $message->setUserInfo($user);
        $message->setChannel($channel);
        $message->setText('/delete '.$id);
        $message->setDate(new \DateTime());
        $this->em->getRepository('AppBundle:Message');

        $this->em->persist($message);
        $this->em->flush();

        return $status;
    }

    /**
     * Checking if message can be displayed on chat, unsetting messages that cannot be displayed
     *
     * @param array $messages messages as array
     *
     * @return array checked messages
     */
    private function checkIfMessagesCanBeDisplayed(array $messages)
    {
        $count = count($messages);
        for ($i = 0 ; $i < $count ; $i++) {
            $textSplitted = explode(' ', $messages[$i]['text']);
            if ($textSplitted[0] == '/delete') {
                unset($messages[$i]);
            }
        }

        return $messages;
    }

    /**
     * Validating if message is valid (not empty etc.) or User and Channel exists
     *
     * @param User $user User instance
     *
     * @param int $channel Channel's id
     *
     * @param string $text message text
     *
     * @return bool status
     */
    private function validateMessage(User $user, int $channel, string $text): bool
    {
        if (!(strlen(trim($text)) > 0)) {
            return false;
        }
        if ($user->getId() <= 0) {
            return false;
        }
        if (!array_key_exists($channel, $this->config->getChannels($user))) {
            return false;
        }
        return true;
    }

    /**
     * Changing mesages from entity to array
     *
     * @param $messages messages Messages to changed
     */
    private function changeMessagesToArray(&$messages, User $user)
    {
        foreach ($messages as &$message) {
            $message = $this->createArrayToJson($message, $user);
        }
    }

    private function createArrayToJson(\AppBundle\Entity\Message $message, User $user)
    {
        $text = $this->specialMessages->specialMessagesDisplay($message->getText(), $user);

        $returnedArray = [
            'id' => $message->getId(),
            'user_id' => $message->getUserId(),
            'date' => $message->getDate(),
            'text' => $text['showText'] ?? $message->getText(),
            'channel' => $message->getChannel(),
            'username' => $message->getUsername(),
            'user_role' => $message->getRole(),
        ];

        $textSplitted = explode(' ', $message->getText());
        if ($textSplitted[0] == '/delete') {
            $returnedArray['id'] = $textSplitted[1];
            $returnedArray['text'] = 'delete';
        }
        return $returnedArray;
    }
}