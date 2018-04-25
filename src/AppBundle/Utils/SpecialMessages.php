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

    public function specialMessages(string $text, User $user):array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->roll($textSplitted, $user);
            default:
                return ['text' => false, 'userId' => false];
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
        $textSpecial = $user->getUsername().' '.$this->translator->trans('chat.roll', ['dice' => "{$dice[0]}d{$dice[1]}"], 'chat', $this->locale).' ';
//        $textSpecial .= "{$dice[0]}d{$dice[1]} ";
        for ( $i = 0 ; $i < $dice[0] ; $i++) {
            $textSpecial .= $this->rollDice($dice[1]).', ';
        }

        return [
            'text' => rtrim($textSpecial, ', '),
            'userId' => 1000000
            ];
    }

    private function rollDice(int $max):int
    {
        return mt_rand(1, $max);
    }

}