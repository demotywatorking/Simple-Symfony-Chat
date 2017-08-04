<?php

namespace AppBundle\Utils;


class ChatConfig
{
    private $inactiveTime = 180;

    /**
     * @return int
     */
    public function getInactiveTime(): int
    {
        return $this->inactiveTime;
    }

}