<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserOnline;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * @Route("/add/", name="add_online")
     *
     * Adds info about user to users online in database.
     */
    public function addOnlineUserAction()
    {
        $this->get('session')->set('channel', 1);
        $online = $this->get('chat.OnlineUsers');
        $online->addUserOnline($this->getUser(), 1);

        return $this->redirectToRoute('chat_index');
    }
}