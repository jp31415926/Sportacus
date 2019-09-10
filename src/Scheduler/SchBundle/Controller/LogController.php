<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Log;
use Scheduler\SchBundle\Form\LogType;

/**
 * Log controller.
 *
 * @Route("/log")
 */
class LogController extends Controller
{
  /**
   * Lists all Log entities.
   *
   * @Route("/", name="log")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:Log')->findAll();

    return array(
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a Log entity.
   *
   * @Route("/{id}/show", name="log_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Log')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Log entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new Log entity.
   *
   * @Route("/new", name="log_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Log();
    $form = $this->createForm(LogType::class, $entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Log entity.
   *
   * @Route("/create", name="log_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Log:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Log();
    $form = $this->createForm(LogType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('log_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Log entity.
   *
   * @Route("/{id}/edit", name="log_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Log')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Log entity.');
    }

    $editForm = $this->createForm(LogType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Log entity.
   *
   * @Route("/{id}/update", name="log_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Log:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Log')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Log entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(LogType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('log_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Log entity.
   *
   * @Route("/{id}/delete", name="log_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Log')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Log entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('log'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', 'hidden')
      ->getForm();
  }
}
