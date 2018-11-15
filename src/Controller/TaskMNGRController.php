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
        $request = Request::createFromGlobals();
        $form = $this->new();
        //$this->addAction($request);
        $name = 'Benjamin';
        return $this->render('task_mngr/MyPage.html.twig', [
            'form' => $form->createView(),
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
        //$test = new Test();
        $test->setTest('First_Test');
        $test->setBertrand('First_Bertrand');
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($test);
        $em->flush();
    }

    public function new()
    {
        $form = $this->createForm(UserType::class, new Test());

        return $form;
    }

    public function addAction(Request $request)
    {
        $form = $this->createForm(UserType::class, new Test());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->redirect($this->generateUrl('add_succes'));
        }

        //var_dump(array('form' => $form->createView()));
        return $this->render('task_mng/MyPage.html.twig', array('form' => "HELLO"));
    }
}