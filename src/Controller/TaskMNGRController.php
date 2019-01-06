<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projects;
use App\Entity\Tasks;
use App\Form\AddProjectType;
use App\Form\AddTaskType;
use App\Form\CompleteType;
use App\Form\TaskOptionsType;

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
     * @Route("/api/projects", name="db-projects", methods={"GET", "PUT", "OPTIONS"})
     */
    public function DB_projects()
    {
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = $this->convertToJson_Projects();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
    }

    /**
     * @Route("/api/tasks", name="db-tasks", methods={"GET", "PUT", "OPTIONS"})
     */
    public function DB_Tasks()
    {
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $response = $this->convertToJson_Tasks();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
    }

    /**
     * @Route("/api/post/project/{title}", name="dbp-tasks", methods={"GET", "PUT", "POST", "OPTIONS"})
     */
    public function DB_PostP($title)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $proj = new Projects();
            $proj->setTitle($title);
            $proj->setCompletion(0);
            $proj->setImage('https://i.imgur.com/0430aeq.jpg');
            $this->postDB_Project($proj);

            $response = $this->convertToJson_Tasks();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
    }

    /**
     * @Route("/api/del/project/{title}", name="dbd-tasks", methods={"GET", "PUT", "OPTIONS"})
     */
    public function DB_DelP($title)
    {
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            str_replace("&§$", " ", $title);
            $this->delProject($title);

            $response = $this->convertToJson_Tasks();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
    }

    /**
     * @Route("/api/update/task/{id}", name="dbu-task", methods={"GET", "PUT", "POST", "OPTIONS"})
     */
    public function DB_updateT($id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->changeStatus($id);

            $response = $this->convertToJson_Projects();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
    }

    /**
     * @Route("/api/update/project/{title}/completion/{per}", name="dbuc-project", methods={"GET", "PUT", "POST", "OPTIONS"})
     */
    public function DB_updatePC($title, $per)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $this->getProjectId($title);
            $this->changeCompletion($id, $per);

            $response = $this->convertToJson_Projects();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type', true);

            return $response;
        }
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
            'type' => 'Project',
            'form' => $form->createView(),
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/{project_title}/del", name="delproj")
     */
    public function delProjectPage($project_title)
    {
        $this->delProject($project_title);

        return $this->redirectToRoute('Projects');
    }

    /**
     * @Route("/{project_title}/tasks", name="Tasks")
     */
    public function tasksPage($project_title)
    {
        $tasks = $this->getDB_AllTasks($project_title);
        $not_empty = !empty($tasks);

        if ($not_empty){
            $count = count($tasks);
            $completed = 0;
            foreach($tasks as $task){
                if($task->getStatus()){
                    $completed += 1;
                }
            }
            $percentage = round($completed * 100 / $count);
        } else {
            $percentage = 0;
        }

        $id = $this->getProjectId($project_title);
        $this->changeCompletion($id, $percentage);

        return $this->render('task_mngr/Tasks.html.twig', [
            'percentage' => $percentage,
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
            'type' => 'Task',
            'form' => $form->createView(),
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    /**
     * @Route("/{project_title}/tasks/{task_id}/del", name="deltask")
     */
    public function delTasksPage($project_title, $task_id)
    {
        $this->delTask($task_id);

        return $this->redirectToRoute('Tasks', array('project_title' => $project_title));
    }

    /**
     * @Route("{project_title}/tasks/{task_id}/change/status", name="chngstat")
     */
    public function statusTasksPage($project_title, $task_id)
    {
        $this->changeStatus($task_id);

        return $this->redirectToRoute('Tasks', array('project_title' => $project_title));
    }

    /**
     * @Route("{project_title}/tasks/{task_id}/options", name="taskoptions")
     */
    public function taskOptions($project_title, $task_id, Request $request)
    {
        $form = $this->createForm(TaskOptionsType::class, new Tasks());
        $task = $this->getDB_Task($task_id);
        $form->get('image')->setData($task->getImage());
        $form->get('description')->setData($task->getDescription());
        $form->get('link')->setData($task->getLink());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $options = $form->getData();

            $task->setImage($options->getImage());
            $task->setDescription($options->getDescription());
            $task->setLink($options->getLink());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Tasks', array('project_title' => $project_title));
        }

        return $this->render('task_mngr/taskOptions.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'TaskMNGRController',
        ]);
    }

    public function delProject($title)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $this->getProjectId($title);
        $proj = $this->getDB_Project($id);
        $em->remove($proj);
        $em->flush();
    }

    public function delTask($id)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $this->getDB_Task($id);
        $em->remove($task);
        $em->flush();
    }

    public function changeStatus($id)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $this->getDB_Task($id);
        $task->setStatus(!$task->getStatus());
        $em->flush();
    }

    public function changeCompletion($id, $completion)
    {
        $em = $this->getDoctrine()->getManager();
        $proj = $this->getDB_Project($id);
        $proj->setCompletion($completion);
        $em->flush();
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
        $task = $em->getRepository(Tasks::class)->find($id);

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

    public function convertToJson_Projects()
    {
        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(2);
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        
        $projects = $this->getDoctrine()->getManager()->getRepository(Projects::class)->findAll();
        $projects = $serializer->serialize($projects, 'json');

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($projects);

        return $response;
    }

    public function convertToJson_Tasks()
    {
        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(2);
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        
        $tasks = $this->getDoctrine()->getManager()->getRepository(Tasks::class)->findAll();
        $tasks = $serializer->serialize($tasks, 'json');

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($tasks);

        return $response;
    }
}