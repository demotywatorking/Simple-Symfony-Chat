<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ChatConfig
{
    /**
     * @var int time in second when user is logout from chat when he is inactivity
     */
    private const INACTIVE_TIME = 180;

    /**
     * @var int Messages limit that can be displayed on first refreshing chat
     */
    private const MESSAGE_LIMIT = 64;

    /**
     * @var array array of channels
     */
    private const DEFAULT_CHANNELS = [
        1 => 'Default',
        2 => 'Channel 2'
    ];

    /**
     * var bool Login by MyBB forum user
     */
    private const MYBB = 0;

    /**
     * var bool Login by phpBB forum user
     */
    private const PHPBB = 0;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return array Array of channels
     */
    public function getChannels(User $user): array
    {
        return self::DEFAULT_CHANNELS + $this->specialChannels() + $this->getUserPrivateChannel($user);
    }

    public static function getMyBB()
    {
        return self::MYBB;
    }

    public static function getPhpBB()
    {
        return self::PHPBB;
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

    public function getUserPrivateChannel(User $user):array
    {
        $channelId = 1000000 + $user->getId();
        return [
            $channelId => 'Private'
        ];
    }

    public function getUserPrivateChannelId(User $user):int
    {
        return 1000000 + $user->getId();
    }

    private function specialChannels():array
    {
        $array = [];
        if ($this->auth->isGranted('ROLE_ADMIN')) {
            $array[4] = 'Admin';
        }
        if ($this->auth->isGranted('ROLE_MODERATOR')) {
            $array[3] = 'Moderator';
        }
        return $array;
    }

}