<?php
namespace AppBundle\Utils;

use AppBundle\Entity\User;
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

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->locale = $translator->getLocale();
    }

    public function specialMessagesDisplay(string $text, User $user):array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->rollShow($textSplitted);
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

}