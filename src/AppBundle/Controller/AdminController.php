<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    /**
     * @Route("/chat/admin/", name="chat_admin")
     */
    public function adminAction()
    {
        $adminPanel = $this->get('app.AdminPanel');
        $users = $adminPanel->getAllUsers();

        return $this->render('admin/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/chat/admin/change/{id}/{role}", name="chat_admin_change")
     */
    public function adminPromoteAction(int $id, string $role)
    {
        $adminPanel = $this->get('app.AdminPanel');
        $adminPanel->changeUsersRole($id, $role);

        return $this->redirectToRoute('chat_admin');
    }
}