<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    /**
     * @Route("/chat/", name="chat_index")
     *
     * @return Response Return view with last messages
     */
    public function showAction(): Response
    {
        $userId = $this->getUser()->getId();

        $messages = $this->get('app.Message')
                    ->getMessagesInIndex();

        $online = $this->get('app.OnlineUsers');
        $usersOnline = $online->getOnlineUsers($userId);
        $online->updateUserOnline($userId);

        return $this->render('chat/index.html.twig',[
            'messages' => $messages,
            'usersOnline' => $usersOnline
        ]);
    }

    /**
     * @Route("/chat/add/", name="chat_add")
     *
     * Method to handle adding new message to database
     *
     * @return JsonResponse returning status success or failure with description why
     */
    public function addAction(): JsonResponse
    {
        $message = $this->get('app.Message');
        return new JsonResponse('0');
    }

    /**
     * Delete User's info from online users in database
     *
     * @Route("/chat/logout", name="chat_logout")
     */
    public function logoutAction()
    {
        $online = $this->get('app.OnlineUsers');
        $online->deleteUserWhenLogout($this->getUser()->getId());

        return $this->redirectToRoute('fos_user_security_logout');
    }
}