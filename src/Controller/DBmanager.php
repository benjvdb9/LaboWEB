<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\TaskMNGRController;
use App\Entity\Projects;
use App\Entity\Tasks;
use App\Form\AddProjectType;
use App\Form\AddTaskType;
use App\Form\CompleteType;

class DBmanager extends TaskMNGRController
{
    public function __construct()
    {
        $em = $this->getDoctrine()->getManager();
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

    public function getTaskId($title)
    {
        $task = $this->$em->getRepository(Tasks::class)->findOneBy(['title' => $title]);

        if (!$task) {
            throw $this->createNotFoundException(
                'id: '.$id
            );
        }

        return $task->getId();
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