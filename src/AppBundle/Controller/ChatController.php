<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $online->updateUserOnline($user, $channel);
        $usersOnline = $online->getOnlineUsers($user->getId(), $channel);

        $channels = $this->get('app.ChatConfig')->getChannels();

        return $this->render('chat/index.html.twig',[
            'messages' => $messages,
            'usersOnline' => $usersOnline,
            'user' => $user,
            'user_channel' => $channel,
            'channels' => $channels
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

        return $this->json($status);
    }
    /**
     * @Route("/chat/refresh", name="chat_refresh")
     *
     * @return JsonResponse
     */
    public function refreshAction()
    {
        $message = $this->get('app.Message');
        $messages = $message->getMessagesFromLastId();

        $online = $this->get('app.OnlineUsers');
        $online->updateUserOnline($this->getUser(), $this->get('session')->get('channel'));
        $usersOnline = $online->getOnlineUsers($this->getUser()->getId(), $this->get('session')->get('channel'));

        $return = [
            'messages' => $messages,
            'usersOnline' => $usersOnline
        ];
        return new JsonResponse($return);
    }

    /**
     * Delete User's info from online users in database
     *
     * @Route("/chat/logout", name="chat_logout")
     */
    public function logoutAction(): RedirectResponse
    {
        $online = $this->get('app.OnlineUsers');
        $online->deleteUserWhenLogout($this->getUser()->getId());

        return $this->redirectToRoute('fos_user_security_logout');
    }

    /**
     * @Route("/chat/channel", name="change_channel_chat")
     *
     * @param int $channel
     */
    public function changeChannelAction(Request $request): JsonResponse
    {
        $channelService = $this->get('app.Channel');
        $channel = $request->get('channel');
        if (!$channel) {
            return $this->json('false');
        }
        $return = $channelService->changeChannelOnChat($this->getUser(), $channel);

        return $this->json($return);
    }

    /**
     * @Route("/chat/admin/", name="chat_admin")
     */
    public function adminAction()
    {
        return new Response('witaj adminie :)');
    }
}