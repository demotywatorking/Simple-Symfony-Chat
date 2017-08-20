<?php

namespace AppBundle\Utils;


class ChatConfig
{
    /**
     * @var int time in second when user is logout from chat when he is inactivity
     */
    const INACTIVE_TIME = 180;

    /**
     * @var int Messages limit that can be displayed on first refreshing chat
     */
    const MESSAGE_LIMIT = 64;

    /**
     * @var array array of channels
     */
    const CHANNELS = [
        1 => 'Default',
        2 => 'Channel 2'
    ];

    /**
     * @return array Array of channels
     */
    public function getChannels(): array
    {
        return self::CHANNELS;
    }

    /**
     * @return int Inactive time
     */
    public function getInactiveTime(): int
    {
        return self::INACTIVE_TIME;
    }

    /**
     * @return int messages limit
     */
    public function getMessageLimit(): int
    {
        return self::MESSAGE_LIMIT;
    }

}