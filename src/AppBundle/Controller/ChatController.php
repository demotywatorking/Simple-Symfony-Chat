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
        $message = $this->get('app.Message')
                    ->getNewMessages();

        return $this->render('chat/index.html.twig',[
            'messages' => $message
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
}