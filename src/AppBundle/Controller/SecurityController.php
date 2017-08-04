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
     * Method adding info about online user to database
     */
    public function addOnlineUserAction()
    {
        $online = $this->get('app.OnlineUsers');
        $online->addUserOnline($this->getUser());
        return $this->redirectToRoute('chat_index');
    }
}