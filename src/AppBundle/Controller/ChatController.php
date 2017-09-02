<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChatController
 * @package AppBundle\Controller
 */
class ChatController extends Controller
{
    /**
     * @Route("/chat/", name="chat_index")
     *
     * Show main window
     *
     * Get messages from last 24h and users online then show chat's main window,
     * last messages and send variables to twig to configure var to jQuery
     *
     * @param Request $request A Request instance
     *
     * @return Response Return main page with all start information
     */
    public function showAction(Request $request): Response
    {
        $user = $this->getUser();
        $channel = $this->get('session')->get('channel');
        $locale = $request->getLocale();

        $messages = $this->get('chat.Message')
                    ->getMessagesInIndex();

        $usersOnlineService = $this->get('chat.OnlineUsers');
        $usersOnlineService->updateUserOnline($user, $channel);
        $usersOnline = $usersOnlineService->getOnlineUsers($user->getId(), $channel);

        $channels = $this->get('chat.ChatConfig')->getChannels();

        return $this->render('chat/index.html.twig',[
            'messages' => $messages,
            'usersOnline' => $usersOnline,
            'user' => $user,
            'user_channel' => $channel,
            'channels' => $channels,
            'locale' => $locale
        ]);
    }

    /**
     * @Route("/chat/add/", name="chat_add")
     *
     * Add new message
     *
     * Check if message can be added to database and get messages that was wrote between
     * last refresh and calling this method
     *
     * @param Request $request A Request instance
     *
     * @return JsonResponse returns status success or failure and new messages
     */
    public function addAction(Request $request): JsonResponse
    {
        $messageText = $request->get('text');
        $user = $this->getUser();

        $messageService = $this->get('chat.Message');
        $status = $messageService->addMessageToDatabase($user, $messageText);

        return $this->json($status);
    }

    /**
     * @Route("/chat/refresh", name="chat_refresh")
     *
     * Refresh chat
     *
     * Get new messages from last refresh and get users online
     *
     * @return JsonResponse returns messages and users online
     */
    public function refreshAction(): JsonResponse
    {
        $messageService = $this->get('chat.Message');
        $messages = $messageService->getMessagesFromLastId();

        $usersOnlineService = $this->get('chat.OnlineUsers');
        $usersOnlineService->updateUserOnline($this->getUser(), $this->get('session')->get('channel'));
        $usersOnline = $usersOnlineService->getOnlineUsers($this->getUser()->getId(), $this->get('session')->get('channel'));

        $return = [
            'messages' => $messages,
            'usersOnline' => $usersOnline
        ];
        return new JsonResponse($return);
    }

    /**
     * @Route("/chat/delete", name="chat_delete")
     * @Security("has_role('ROLE_MODERATOR')")
     *
     * Delete message from database
     *
     * Checking if message exists in database and then delete it from database,
     * add message to database that message was deleted and by whom
     *
     * @param Request $request A Request instance
     *
     * @return JsonResponse status true or false
     */
    public function deleteAction(Request $request): JsonResponse
    {
        $id = $request->get('messageId');
        $user = $this->getUser();
        if (!$id) {
            return $this->json(['status' => 0]);
        }

        $status = $this->get('chat.Message')->deleteMessage($id, $user);

        return $this->json(['status' => $status]);
    }

    /**
     * @Route("/chat/logout", name="chat_logout")
     *
     * Logout from chat
     *
     * Delete User's info from online users in database and then redirect to logout in fosuserbundle
     *
     * @return RedirectResponse Redirect to fos logout
     */
    public function logoutAction(): RedirectResponse
    {
        $usersOnlineService = $this->get('chat.OnlineUsers');
        $usersOnlineService->deleteUserWhenLogout($this->getUser()->getId());

        return $this->redirectToRoute('fos_user_security_logout');
    }

    /**
     * @Route("/chat/channel", name="change_channel_chat")
     *
     * Change channel on chat
     *
     * Checking if channel exists and change user's channel in session
     *
     * @param Request $request A Request instance
     *
     * @return JsonResponse returns status of changing channel
     */
    public function changeChannelAction(Request $request): JsonResponse
    {
        $channelService = $this->get('chat.Channel');
        $channel = $request->get('channel');
        if (!$channel) {
            return $this->json('false');
        }
        $return = $channelService->changeChannelOnChat($this->getUser(), $channel);

        return $this->json($return);
    }
}