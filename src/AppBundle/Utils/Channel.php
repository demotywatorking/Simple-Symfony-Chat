<?php

namespace AppBundle\Utils;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Channel
{
    /**
     * @var ChatConfig
     */
    private $config;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var UserOnline
     */
    private $userOnline;

    public function __construct(ChatConfig $config, SessionInterface $session, UserOnline $userOnline)
    {
        $this->config = $config;
        $this->session = $session;
        $this->userOnline = $userOnline;
    }

    public function changeChannelOnChat($user, int $channel): bool
    {
        if (!array_key_exists($channel, $this->config->getChannels())) {
            return false;
        }
        $this->userOnline->updateUserOnline($user, $channel);
        $this->session->set('channel', $channel);
        $lastId = $this->session->get('lastId') - 64;
        $this->session->set('lastId', $lastId);
        return true;
    }
}