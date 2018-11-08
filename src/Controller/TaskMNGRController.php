<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Test;
use App\Form\UserType;

class TaskMNGRController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        $this->new();
        $this->getDB_Entry(1);
        return $this->render('task_mngr/MyPage.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    public function getDB_Entry($id)
    {
        $em = $this->getDoctrine()->getManager();
        $test = $em->getRepository('App\Entity\Test')->find($id);

        if (!$test) {
            throw $this->createNotFoundException(
                'id: '.$id
            );
        }
        dump($test);
        $this->addFlash('notice', 'this was succesful');
    }

    public function createDB_Entry()
    {
        $test = new Test();
        $test->setTest('First_Test');
        $test->setBertrand('First_Bertrand');
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($test);
        $em->flush();
    }

    public function new()
    {
        $form = $this->createForm(UserType::class, new Test());

        return $this->render('task_mngr/MyPage.html.twig',
                             array('form' => $form->createView()));
    }

    /*public function addAction(Request $request)
    {
        $form = $this->createForm(UserType::class, new Test());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect($this->generateUrl('add_succes'));
        }

        return $this->render('add.html.twig', array('form' => $form->createView()));
    }*/
}