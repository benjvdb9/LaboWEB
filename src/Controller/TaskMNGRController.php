<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\DBmanager;
use App\Entity\Projects;
use App\Entity\Tasks;
use App\Form\AddProjectType;
use App\Form\AddTaskType;
use App\Form\CompleteType;

class TaskMNGRController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('task_mngr/MyPage.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/projects", name="Projects")
     */
    public function projectsPage()
    {
        $projects = $this->getDB_AllProjects();
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
    public function tasksPage($project_title, Request $request)
    {
        $tasks = $this->getDB_AllTasks($project_title);
        $not_empty = !empty($tasks);

        return $this->render('task_mngr/Tasks.html.twig', [
            'project_title' => $project_title,
            'tasks' => $tasks,
            'not_empty' => $not_empty,
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/{project_title}/add/task", name="addtask")
     */
    public function addTasksPage($project_title, Request $request)
    {
        $form = $this->createForm(AddTaskType::class, new Tasks());
        $form->get('title')->setData('Add tasks');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setStatus(False);
            $task->setImage('https://i.imgur.com/0430aeq.jpg');
            $project_id = $this->getProjectId($project_title);
            $task->setProjects($this->getDB_Project($project_id));

            $this->postDB_Task($task);
            return $this->redirectToRoute('Tasks', array('project_title' => $project_title));
        }

        return $this->render('task_mngr/addProj.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/task/{task_id}/change/status", name="chngstat")
     */
    public function statusTasksPage($task_id)
    {
        $DB = new DBmanager();
        var_dump($DB);
        //$DB->changeStatus($task_id);

        return $this->render('task_mngr/addProj.html.twig', [
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    public function getProjectId($title)
    {
        $em = $this->getDoctrine()->getManager();
        $proj = $em->getRepository(Projects::class)->findOneBy(['title' => $title]);

        if (!$proj) {
            throw $this->createNotFoundException(
                'id: '.$id
            );
        }

        return $proj->getId();
    }

    public function changeStatus($id)
    {
        $em = $this->getDoctrine()->getManager();
        $proj = $this->getDB_Project($id);
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

    public function getDB_Task($id)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException(
                'id: '.$id
            );
        }

        return $task;
    }

    public function getDB_AllProjects()
    {
        $rep = $this->getDoctrine()->getManager()->getRepository(Projects::class);
        return $rep->findAll();
    }

    public function getDB_AllTasks($title)
    {
        $rep = $this->getDoctrine()->getManager()->getRepository(Tasks::class);
        $id  = $this->getProjectId($title); 
        return $rep->findBy(['projects' => $id]);
    }

    public function postDB_Project(Projects $proj)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($proj);
        $em->flush();
    }

    public function postDB_Task(Tasks $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();
    }
}