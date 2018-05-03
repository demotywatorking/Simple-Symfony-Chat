<?php
namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SpecialMessages
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string user's locale
     */
    private $locale;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $em)
    {
        $this->translator = $translator;
        $this->locale = $translator->getLocale();
        $this->em = $em;
    }

    public function specialMessagesDisplay(string $text, User $user):array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->rollShow($textSplitted);
            case '/privTo':
                return $this->privToShow($textSplitted);
            case '/privMsg':
                return $this->privFromShow($textSplitted);
            default:
                return ['userId' => false];
        }
    }

    public function specialMessages(string $text, User $user):array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->roll($textSplitted, $user);
            case '/priv':
                return $this->priv($textSplitted, $user);
            default:
                return ['userId' => false];
        }
    }

    private function roll(array $text, User $user):array
    {
        if (!isset($text[1])) {
            $dice = [0 => 2, 1 => 6];
        } else {
            $dice = explode('d', $text[1]);
        }
        if (count($dice) < 2) {
            $dice = [0 => 2, 1 => 6];
        } else {
            if (!(is_numeric($dice[0])) || $dice[0] <= 0 || $dice[0] > 100) {
                $dice[0] = 2;
            }
            if (!(is_numeric($dice[1])) || $dice[1] <= 0 || $dice[1] > 100) {
                $dice[1] = 6;
            }
        }
        $text = "/roll {$dice[0]}d{$dice[1]} {$user->getUsername()} ";
        $textSpecial = $user->getUsername().' '.$this->translator->trans('chat.roll', ['chat.dice' => "{$dice[0]}d{$dice[1]}"], 'chat', $this->locale).' ';
        for ( $i = 0 ; $i < $dice[0] ; $i++) {
            $result = $this->rollDice($dice[1]);
            $textSpecial .= $result.', ';
            $text .= $result.', ';
        }

        return [
            'showText' => rtrim($textSpecial, ', ').'.',
            'text' => rtrim($text, ', ').'.',
            'userId' => 1000000
            ];
    }

    private function rollDice(int $max):int
    {
        return mt_rand(1, $max);
    }

    private function rollShow(array $text):array
    {
        $textSplitted = explode(' ', $text[1], 3);
        $text = $textSplitted[1].' '.$this->translator->trans('chat.roll', ['chat.dice' => $textSplitted[0]], 'chat', $this->locale).' '.$textSplitted[2];

        return [
            'showText' => $text,
            'userId' => 1000000
        ];
    }

    private function priv(array $text, User $user):array
    {
        if (!isset($text[1])) {
            $this->insertErrorMessage($user, $text, 'chat.wrongUsername');
            return ['userId' => false, 'message' => false, 'count' => 1];
        }
        $textSplitted = explode(' ', $text[1], 2);
        $secondUser = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $textSplitted[0]]);
        if (!$secondUser) {
            $this->insertErrorMessage($user, $textSplitted, 'error.userNotFound');
            return ['userId' => false, 'message' => false, 'count' => 1];
        }

        $message1 = $this->insertPw($user, $secondUser, $textSplitted);
        $showText = $this->translator->trans('chat.privTo', ['chat.user' => $secondUser->getUsername()], 'chat', $this->locale) . ' ' . $textSplitted[1];

        return ['userId' => false, 'message' => $message1, 'showText' => $showText, 'count' => 2];
    }

    private function insertPw(User $user, User $secondUser, array $textSplitted)
    {
        $message = new \AppBundle\Entity\Message();
        $message->setUserId($secondUser->getId())
            ->setUserInfo($user)
            ->setChannel(ChatConfig::getUserPrivateChannelId($secondUser))
            ->setDate(new \DateTime())
            ->setText('/privMsg ' . $textSplitted[1]);
        $this->em->persist($message);

        $message1 = new \AppBundle\Entity\Message();
        $message1->setUserId($user->getId())
            ->setUserInfo($user)
            ->setChannel(ChatConfig::getUserPrivateChannelId($user))
            ->setDate(new \DateTime())
            ->setText('/privTo ' . $textSplitted[0] . ' ' . $textSplitted[1]);
        $this->em->persist($message1);

        return $message1;
    }

    private function insertErrorMessage(User $user, array $text, string $error)
    {
        $message = new \AppBundle\Entity\Message();
        $message->setUserId($user->getId())
            ->setUserInfo($user)
            ->setChannel($this->chatConfig->getUserPrivateChannelId($user))
            ->setDate(new \DateTime())
            ->setText($error . ' ' . $text[0]);
        $this->em->persist($message);
    }

    private function privToShow(array $text)
    {
        $textSplitted = explode(' ', $text[1]);
        $text = $this->translator->trans('chat.privTo', ['chat.user' =>  $textSplitted[0]], 'chat', $this->locale) . ' ' . $textSplitted[1];

        return [
            'showText' => $text,
            'userId' => false
        ];
    }

    private function privFromShow(array $text)
    {
        $text = $this->translator->trans('chat.privFrom', [], 'chat', $this->locale) . ' ' . $text[1];

        return [
            'showText' => $text,
            'userId' => false
        ];
    }

}