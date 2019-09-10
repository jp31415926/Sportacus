<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\User;
use Scheduler\SchBundle\Entity\Region;
use Scheduler\SchBundle\Form\UserType;
use Scheduler\SchBundle\Form\UserCurrentProjectType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
  /**
   * Lists all User entities.
   *
   * @Route("/", name="user")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

//    $entities = $em->getRepository('SchedulerBundle:User')->findAll();
    $entities = $em->getRepository('SchedulerBundle:User')->findAllOrderedByIdDesc();

    return [
      'title' => 'User',
      'entities' => $entities,
    ];
  }

  /**
   * Finds and displays a User entity.
   *
   * @Route("/{id}/show", name="user_show")
   * @Template()
   * @param $id
   * @return array
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:User')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find User entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show User',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new User entity.
   *
   * @Route("/new", name="user_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new User();
    $form = $this->createForm(UserType::class, $entity);

    return array(
      'title' => 'New User',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new User entity.
   *
   * @Route("/create", name="user_create")
   * @Method("POST")
   * @Template("SchedulerBundle:User:new.html.twig")
   * @param Request $request
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function createAction(Request $request)
  {
    $entity = new User();
    $form = $this->createForm(UserType::class, $entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
    }

    return [
      'title' => 'Create User',
      'entity' => $entity,
      'form' => $form->createView(),
    ];
  }

  /**
   * Displays a form to edit an existing User entity.
   *
   * @Route("/{id}/edit", name="user_edit")
   * @Template()
   * @param $id
   * @return array
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:User')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find User entity.');
    }

    $editForm = $this->createForm(UserType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit User',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  // TODO: this needs to move to a separate bundle or file
  /**
   * @param array|string $emailTo
   * @param string $subject
   * @param string $msg
   * @param array $cc
   */
  public function sendEmailMessage($emailTo, $subject, $msg, $cc = array())
  {
    if (!array_key_exists('john.price@ayso894.net', $cc)) {
      $cc['john.price@ayso894.net'] = 'John Price';// FIXME
    }
    // send an email
    $message = \Swift_Message::newInstance()
      ->setSubject('[Sportac.us] ' . $subject)
      ->setFrom(array('notification@sportac.us' => 'Sportac.us Scheduling System'))
      //->setCc('john.price@ayso894.net')
      ->setCc($cc)
      ->setTo($emailTo)
      ->setBody($msg);
    $this->get('mailer')->send($message);
    //echo '<pre>';
    //print_r($emailTo);
    //print_r($msg);
    //print_r($message);
    //echo '</pre>';
  }

  /**
   * Edits an existing User entity.
   *
   * @Route("/{id}/update", name="user_update")
   * @Method("POST")
   * @Template("SchedulerBundle:User:edit.html.twig")
   * @param Request $request
   * @param $id
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:User')->find($id);
    $orig = clone $entity;

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find User entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(UserType::class, $entity);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      // handle if referee role changed
      if ($entity->getRoleReferee() != $orig->getRoleReferee()) {
        if ($entity->getRoleReferee()) {
          /** @var Region $region */
          $region = $entity->getRegion();

          $msg = "You are now allowed to request referee assignment to games on Sportacus (you may have to logout and log back in for this change to take effect).\n\n" .
            "After you login, click \"Ref Schedule\" at top, then you will be able to click on the \"CR\", \"AR1\" or \"AR2\" of the schedule and assign yourself to games.\n\n" .
            "I'm still working out the bugs, so feel free to use the Contact link or email me to report problems.\n\n" .
            "Thanks,\n" .
            "John Price\n" .
            "(256) 213-1969";
          //$name = $entity->getFirstname().' '. $entity->getLastname();
          $to = array($entity->getEmail() => $entity->getFullName());
          $cc = array();
          if ($region->getRefadminEmail()) {
            $cc = array($region->getRefAdminEmail() => $region->getRefAdminName());
          }
          $this->sendEmailMessage($to, "You now have referee permissions", $msg, $cc);
          $entity->addRole('ROLE_REF');
        } else {
          $entity->removeRole('ROLE_REF');
        }
      }

      // handle if scheduler role changed
      if ($entity->getRoleScheduler() != $orig->getRoleScheduler()) {
        if ($entity->getRoleScheduler()) {
          $msg = "You are now allowed to edit teams, locations and games for your region on Sportacus (you may have to logout and log back in for this change to take effect).\n\n" .
            "Please be careful.  You are allowed to delete content and do other destructive actions.\n\n" .
            "I'm still working out the bugs, so feel free to use the Contact link or email me to report problems.\n\n" .
            "Thanks,\n" .
            "John Price\n" .
            "(256) 213-1969";
          $to = array($entity->getEmail() => $entity->getFullName());
          $this->sendEmailMessage($to, "You now have scheduler permissions", $msg);
          $entity->addRole('ROLE_SCHEDULER');
        } else {
          $entity->removeRole('ROLE_SCHEDULER');
        }
      }

      // handle if referee admin role changed
      if ($entity->getRoleRefereeAdmin() != $orig->getRoleRefereeAdmin()) {
        if ($entity->getRoleRefereeAdmin()) {
          $msg = "You are now allowed to edit scorecards, referee assignments and other referee info for your region on Sportacus (you may have to logout and log back in for this change to take effect).\n\n" .
            "I'm still working out the bugs, so feel free to use the Contact link or email me to report problems.\n\n" .
            "Thanks,\n" .
            "John Price\n" .
            "(256) 513-9530";
          $to = array($entity->getEmail() => $entity->getFullName());
          $this->sendEmailMessage($to, "You now have referee admin permissions", $msg);
          $entity->addRole('ROLE_REF_ADMIN');
        } else {
          $entity->removeRole('ROLE_REF_ADMIN');
        }
      }

      // handle if assigner role changed
      if ($entity->getRoleAssigner() != $orig->getRoleAssigner()) {
        if ($entity->getRoleAssigner()) {
          $msg = "You are now allowed to edit referee assignments on Sportacus. You will also get notified when someone changes a referee assignment (you may have to logout and log back in for this change to take effect).\n\n" .
            "I'm still working out the bugs, so feel free to use the Contact link or email me to report problems.\n\n" .
            "Thanks,\n" .
            "John Price\n" .
            "(256) 513-9530";
          $to = array($entity->getEmail() => $entity->getFullName());
          $this->sendEmailMessage($to, "You now have assigner permissions", $msg);
          $entity->addRole('ROLE_ASSIGNER');
        } else {
          $entity->removeRole('ROLE_ASSIGNER');
        }
      }

      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('user', array('id' => $id)));
    }

    return [
      'title' => 'Edit User',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Deletes a User entity.
   *
   * @Route("/{id}/delete", name="user_delete")
   * @Method("POST")
   * @param Request $request
   * @param $id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:User')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find User entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('user'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }

  /**
   * Update User's current project.
   *
   * @Route("/currentproject", name="user_currentproject")
   * @Template("SchedulerBundle:User:currentproject.html.twig")
   * @param Request $request
   * @return array
   */
  public function currentprojectAction(Request $request)
  {
    $user = $this->getUser();
    $em = $this->getDoctrine()->getManager();

    $form = $this->createForm(UserCurrentProjectType::class, $user);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isValid()) {
        $em->persist($user);
        $em->flush();
        $this->get('session')->getFlashBag()->add('contact-notice', 'Current project set to ' . $user->getCurrentproject()->getLongName());

        //return $this->redirect($this->generateUrl('official_schedule'));
      }
    }

    return array(
      'title' => 'Current Project',
      'user' => $user,
      'form' => $form->createView(),
    );
  }

  /**
   * Exports entities.
   *
   * @Route("/export.csv", name="user_export_csv")
   * @Template("SchedulerBundle:User:export.csv.twig")
   */
  public function exportCsvAction()
  {
    $em = $this->getDoctrine()->getManager();
    $users = $em->getRepository('SchedulerBundle:User')->findAll();

    //$games = $query->getQuery()->getResult();
    $filename = "users-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:User:export.csv.twig', array('data' => $users));
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }

}
