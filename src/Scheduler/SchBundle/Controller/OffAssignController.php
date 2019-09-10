<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\OffAssign;
use Scheduler\SchBundle\Form\OffAssignType;

/**
 * OffAssign controller.
 *
 * @Route("/offassign")
 */
class OffAssignController extends Controller
{
  /**
   * Lists all OffAssign entities.
   *
   * @Route("/", name="offassign")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:OffAssign')->findAll();

    return array(
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a OffAssign entity.
   *
   * @Route("/{id}/show", name="offassign_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffAssign')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffAssign entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new OffAssign entity.
   *
   * @Route("/new", name="offassign_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new OffAssign();
    $form = $this->createForm(OffAssignType::class, $entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new OffAssign entity.
   *
   * @Route("/create", name="offassign_create")
   * @Method("POST")
   * @Template("SchedulerBundle:OffAssign:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new OffAssign();
    $form = $this->createForm(OffAssignType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offassign_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing OffAssign entity.
   *
   * @Route("/{id}/edit", name="offassign_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffAssign')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffAssign entity.');
    }

    $editForm = $this->createForm(OffAssignType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing OffAssign entity.
   *
   * @Route("/{id}/update", name="offassign_update")
   * @Method("POST")
   * @Template("SchedulerBundle:OffAssign:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffAssign')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffAssign entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(OffAssignType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offassign_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a OffAssign entity.
   *
   * @Route("/{id}/delete", name="offassign_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:OffAssign')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find OffAssign entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('offassign'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', 'hidden')
      ->getForm();
  }
}
