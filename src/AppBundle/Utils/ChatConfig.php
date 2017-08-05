<?php

namespace AppBundle\Utils;


class ChatConfig
{
    /**
     * @var int time when user is logout from chat when he is inactivity
     */
    private $inactiveTime = 180;
    /**
     * @var array array of channels
     */
    private $channels = [
        '1' => 'Default',
        '2' => 'Channel 2'
    ];

    /**
     * @return array
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @return int
     */
    public function getInactiveTime(): int
    {
        return $this->inactiveTime;
    }

}