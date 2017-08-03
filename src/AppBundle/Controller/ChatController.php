<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    public function showAction(int $lastid = 0): Response
    {
        return new Response('Tu będ')
    }
}