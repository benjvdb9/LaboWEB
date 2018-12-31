<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projects;
use App\Form\AddProjectType;

class TaskMNGRController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $request = Request::createFromGlobals();
        return $this->render('task_mngr/MyPage.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/projects", name="Projects")
     */
    public function projectsPage()
    {
        $projects = $this->getDB_AllProjects(1);
        $not_empty = !empty($projects);

        return $this->render('task_mngr/Projects.html.twig', [
            'projects' => $projects,
            'not_empty' => $not_empty,
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
            return $this->redirectToRoute('Projects');
        }

        return $this->render('task_mngr/addProj.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/{project_title}/tasks", name="Tasks")
     */
    public function projectsTasks($project_title)
    {
        return $this->render('task_mngr/Projects.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    public function getDB_AllProjects()
    {
        $rep = $this->getDoctrine()->getManager()->getRepository(Projects::class);
        return $rep->findAll();
    }

    public function getDB_Project($id)
    {
        $em = $this->getDoctrine()->getManager();
        $proj = $em->getRepository(Projects::class)->find($id);

        if (!$proj) {
            throw $this->createNotFoundException(
                'id: '.$id
            );
        }

        return $proj;
    }

    public function postDB_Project(Projects $proj)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($proj);
        $em->flush();
    }
}