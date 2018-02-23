<?php

namespace Demos\TaskBundle\Controller;

use Demos\TaskBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Demos\TaskBundle\Entity\Task;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

use Demos\TaskBundle\Form\TaskType;
use Demos\TaskBundle\Form\CommentType;

class TaskController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $tasks = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->findBy(array('isCompleted' => '0'));

        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        return $this->render('DemosTaskBundle:Task:index.html.twig', array(
            'tasks' => $tasks,
            'calendar' => $calendar,
            'month_name' => $month_name,
        ));
    }

    /**
     * @return Response
     */
    public function isCompletedAction()
    {
        $tasks = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->findBy(array('isCompleted' => '1'));

        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        return $this->render('DemosTaskBundle:Task:iscompleted.html.twig', array(
            'tasks' => $tasks,
            'calendar' => $calendar,
            'month_name' => $month_name,
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(Request $request) {

        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        $task = new Task();
        $task->setCreatedDate(new \DateTime());
        $task->setUpdatedDate(new \DateTime());
        $task->setIsCompleted(0);

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $task = $form->getData();

            $file = $task->getAttachment();
            if (!empty($file)) {
                // Generate a unique name
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where attachment are stored
                $file->move(
                    $this->getParameter('task_attachment_directory'),
                    $fileName
                );
                $task->setAttachment($fileName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('demos_task_all');
        }

        return $this->render('DemosTaskBundle:Task:create.html.twig', array(
            'form' => $form->createView(),
            'calendar' => $calendar,
            'month_name' => $month_name,
        ));
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $id) {

        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        $task = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->find($id);
        if (!$task) {
            throw $this->createNotFoundException('Страница не найдена!');
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $task = $form->getData();

            $file = $task->getAttachment();
            if (!empty($file)) {
                // Generate a unique name
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                // Move the file to the directory where attachment are stored
                $file->move(
                    $this->getParameter('task_attachment_directory'),
                    $fileName
                );
                $task->setAttachment($fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('demos_task_all');
        }

        return $this->render('DemosTaskBundle:Task:create.html.twig', array(
            'form' => $form->createView(),
            'calendar' => $calendar,
            'month_name' => $month_name,
        ));
    }

    /**
     * @return array
     */
    public function calendar()
    {
        $m = date('m');
        $y = date('Y');

        $number = cal_days_in_month(CAL_GREGORIAN, $m, $y);

        $em = $this->getDoctrine()->getRepository('DemosTaskBundle:Task');

        $calendar = [];
        for($i=1; $i<=$number; $i++)
        {
            $d_week = date('N', mktime(0,0,0,$m,$i,$y));

            /*if( $d_week !== 1 && $i == 1) {
                $time = strtotime($i.'-'.$m.'-'.$y);
                while (date('N', $time) != 1) {
                    $time -=  86400;

                    $d = date('j', $time);
                    $pre_m = date('m', $time);
                    $pre_d_week = date('N', $time);

                    $calendar[$pre_m.$d]['number'] = $d;
                    $calendar[$pre_m.$d]['d_week'] = $pre_d_week;
                    $calendar[$pre_m.$d]['month'] = $pre_m;
                    $calendar[$pre_m.$d]['year'] = $y;

                    // tasks
                    $tasks = $em->findBy(array('updated_date' => new \DateTime(date('Y-m-d', $time))));
                    if (!empty($tasks)) {
                        $calendar[$pre_m.$d]['class'] = 'ctask';
                    } else {
                        $calendar[$pre_m.$d]['class'] = '';
                    }
                }
            }*/

            //if (1 <= $i && $i <= 9) $i = '0'.$i;

            $calendar[$m.$i]['number'] = $i;
            $calendar[$m.$i]['d_week'] = $d_week;

            // tasks
            $time = strtotime($i.'-'.$m.'-'.$y);
            $d = new \DateTime(date('Y-m-d', $time));
            $tasks = $em->findBy(array('created_date' => $d));

            /*echo '<pre>';
            print_r($tasks);
            echo '</pre>';*/

            if ($i == date('d')) {
                if (!empty($tasks)) {
                    $calendar[$m.$i]['class'] = 'cmonth cday ctask';
                } else {
                    $calendar[$m.$i]['class'] = 'cmonth cday';
                }
            } else {
                if (!empty($tasks)) {
                    $calendar[$m.$i]['class'] = 'cmonth ctask';
                } else {
                    $calendar[$m.$i]['class'] = 'cmonth';
                }
            }

            $calendar[$m.$i]['month'] = $m;
            $calendar[$m.$i]['year'] = $y;

            /*if( $d_week !== 7 && $i == $number) {
                $time = strtotime($number.'-'.$m.'-'.$y);
                while (date('N', $time) != 7) {
                    $time +=  86400;

                    $d = date('j', $time);
                    $next_m = date('m', $time);
                    $next_d_week = date('N', $time);
                    $calendar[$next_m.$d]['number'] = $d;
                    $calendar[$next_m.$d]['d_week'] = $next_d_week;
                    $calendar[$next_m.$d]['class'] = '';
                    $calendar[$next_m.$d]['month'] = $next_m;
                    $calendar[$next_m.$d]['year'] = $y;

                    // tasks
                    $tasks = $em->findBy(array('updated_date' => new \DateTime(date('Y-m-d', $time))));
                    if (!empty($tasks)) {
                        $calendar[$next_m.$d]['class'] = 'ctask';
                    } else {
                        $calendar[$next_m.$d]['class'] = '';
                    }
                }
            }*/
        }

        return $calendar;
    }

    /**
     * @return false|string
     */
    public function getMonthName($m = null, $y = null)
    {
        if (is_null($y)) $y = date('Y');
        if (is_null($m)) $m = date('m');

        $month/*['name']*/ = date('F', strtotime('1-'.$m.'-'.$y));
        //$month['p_m'] = date('m', strtotime('1-'.$m.'-'.$y) - );
        //$month['p_m'] = date('m', strtotime('1-'.$m.'-'.$y) - );

        return $month;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewAction(Request $request, $id)
    {
        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        $task = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->find($id);
        if (!$task) {
            throw $this->createNotFoundException('Страница не найдена!');
        }

        $comment = new Comment();
        $comment->setCreatedDate(new \DateTime());
        $comment->setTaskid($id);
        $comment->setUid($id);

        $form_comment = $this->createForm(CommentType::class, $comment);

        $form_comment->handleRequest($request);

        if ($form_comment->isSubmitted() && $form_comment->isValid())
        {
            $comment = $form_comment->getData();

            //$file = new File();
            $file = $comment->getAttachment();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where attachment are stored
            $file->move(
                $this->getParameter('comment_attachment_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $comment->setAttachment($fileName);


            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('demos_task_view', array('id'=>$id));
        }

        $comments = $this->getDoctrine()->getRepository('DemosTaskBundle:Comment')->findBy(array('taskid' => $id));

        /*echo '<pre>';
        print_r($comments);
        echo '</pre>';*/

        return $this->render('DemosTaskBundle:Task:view.html.twig', array(
            'task' => $task,
            'calendar' => $calendar,
            'month_name' => $month_name,
            'form_comment' => $form_comment->createView(),
            'comments' => $comments,
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $task = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('demos_task_all');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completedAction($id)
    {
        $task = $this->getDoctrine()->getRepository('DemosTaskBundle:Task')->find($id);
        $task->setIsCompleted(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        return $this->redirectToRoute('demos_task_all');
    }

    /**
     * @return Response
     */
    public function imageAction()
    {
        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        return $this->render('DemosTaskBundle:Task:image.html.twig', array(
            'calendar' => $calendar,
            'month_name' => $month_name,
        ));
    }

    /**
     * @param $y
     * @param $m
     * @param $d
     * @return Response
     */
    public function calDayAction($y, $m, $d)
    {
        $calendar = \Demos\TaskBundle\Controller\TaskController::calendar();
        $month_name = \Demos\TaskBundle\Controller\TaskController::getMonthName();

        //echo $y .' '. $m .' '. $d ;

        $time = strtotime($d.'-'.$m.'-'.$y);

        $day = date('j F Y', $time);

        $d = new \DateTime(date('Y-m-d', $time));

        $em = $this->getDoctrine()->getRepository('DemosTaskBundle:Task');
        $tasks = $em->findBy(array('created_date' => $d));

        return $this->render('DemosTaskBundle:Task:calday.html.twig', array(
            'calendar' => $calendar,
            'month_name' => $month_name,
            'day' => $day,
            'tasks' => $tasks,
        ));
    }

    /*public function calMonth($y, $m)
    {
        return
    }*/
}
