<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Test;
use App\Entity\Projects;
use App\Form\UserType;
use App\Form\AddProjectType;

class TaskMNGRController extends Controller
{
    /**
     * @Route("/", name="home")
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

    /**
     * @Route("/projects", name="Projects")
     */
    public function projectsPage()
    {
        return $this->render('task_mngr/Projects.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/add/project", name="addproj")
     */
    public function addProjectPage(Request $request)
    {
        $form = $this->createForm(AddProjectType::class, new Projects());
        $form->get('title')->setData('MyProj');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proj = $form->getData();
            $proj->setCompletion(0);
            $proj->setImage('https://i.imgur.com/0430aeq.jpg');

            $this->postDB_Project($proj);
            return $this->redirectToRoute('projects');
        }

        return $this->render('task_mngr/addProj.html.twig', [
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

    public function addProject()
    {
        $proj = new Projects();
        $proj->setTitle("test");
        $proj->setCompletion(70);
        $proj->setImage("htttp://www.google.com");

        $em = $this->getDoctrine()->getManager();
        $em->persist($test);
        $em->flush();
    }

    public function postDB_Project(Projects $proj)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($proj);
        $em->flush();
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