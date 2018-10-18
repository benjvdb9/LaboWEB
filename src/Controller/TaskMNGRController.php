<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class TaskMNGRController extends Controller
{
    /**
     * @Route("/task", name="task")
     */
    public function index()
    {
        return $this->render('task_mngr/MyPage.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }
}
