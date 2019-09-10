<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\LogGame;
use Scheduler\SchBundle\Form\LogGameType;

/**
 * LogGame controller.
 *
 * @Route("/loggame")
 */
class LogGameController extends Controller
{
  /**
   * Lists all LogGame entities.
   *
   * @Route("/", name="loggame")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:LogGame')->findAll();

    return array(
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a LogGame entity.
   *
   * @Route("/{id}/show", name="loggame_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:LogGame')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find LogGame entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new LogGame entity.
   *
   * @Route("/new", name="loggame_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new LogGame();
    $form = $this->createForm(LogGameType::class, $entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new LogGame entity.
   *
   * @Route("/create", name="loggame_create")
   * @Method("POST")
   * @Template("SchedulerBundle:LogGame:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new LogGame();
    $form = $this->createForm(LogGameType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('loggame_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing LogGame entity.
   *
   * @Route("/{id}/edit", name="loggame_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:LogGame')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find LogGame entity.');
    }

    $editForm = $this->createForm(LogGameType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing LogGame entity.
   *
   * @Route("/{id}/update", name="loggame_update")
   * @Method("POST")
   * @Template("SchedulerBundle:LogGame:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:LogGame')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find LogGame entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(LogGameType::class, $entity);
    $editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('loggame_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a LogGame entity.
   *
   * @Route("/{id}/delete", name="loggame_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:LogGame')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find LogGame entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('loggame'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', 'hidden')
      ->getForm();
  }
}
