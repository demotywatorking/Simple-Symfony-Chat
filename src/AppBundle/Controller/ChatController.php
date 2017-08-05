<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $user = $this->getUser();
        $channel = $this->get('session')->get('channel');

        $messages = $this->get('app.Message')
                    ->getMessagesInIndex();

        $online = $this->get('app.OnlineUsers');
        $usersOnline = $online->getOnlineUsers($user->getId(), $channel);

        try {
            $online->updateUserOnline($user->getId(), $channel);
        } catch (\Throwable $e) {
            return $this->redirectToRoute('fos_user_security_logout');
        }

        return $this->render('chat/index.html.twig',[
            'messages' => $messages,
            'usersOnline' => $usersOnline,
            'user' => $user
        ]);
    }

    /**
     * @Route("/chat/add/", name="chat_add")
     *
     * Method to handle adding new message to database
     *
     * @return JsonResponse returning status success or failure with description why
     */
    public function addAction(Request $request): JsonResponse
    {
        $messageText = $request->get('text');
        $user = $this->getUser();
        $channel = $this->get('session')->get('channel');

        $message = $this->get('app.Message');
        $status = $message->addMessageToDatabase($user, $channel, $messageText);

        return new JsonResponse($status);
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