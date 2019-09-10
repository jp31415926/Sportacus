<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\OffPos;
use Scheduler\SchBundle\Form\OffPosType;

/**
 * OffPos controller.
 *
 * @Route("/offpos")
 */
class OffPosController extends Controller
{
  /**
   * Lists all OffPos entities.
   *
   * @Route("/", name="offpos")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:OffPos')->findAll();

    return array(
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a OffPos entity.
   *
   * @Route("/{id}/show", name="offpos_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffPos')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffPos entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new OffPos entity.
   *
   * @Route("/new", name="offpos_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new OffPos();
    $form = $this->createForm(OffPosType::class, $entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new OffPos entity.
   *
   * @Route("/create", name="offpos_create")
   * @Method("POST")
   * @Template("SchedulerBundle:OffPos:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new OffPos();
    $form = $this->createForm(OffPosType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offpos_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing OffPos entity.
   *
   * @Route("/{id}/edit", name="offpos_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffPos')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffPos entity.');
    }

    $editForm = $this->createForm(OffPosType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing OffPos entity.
   *
   * @Route("/{id}/update", name="offpos_update")
   * @Method("POST")
   * @Template("SchedulerBundle:OffPos:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffPos')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffPos entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(OffPosType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offpos_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a OffPos entity.
   *
   * @Route("/{id}/delete", name="offpos_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:OffPos')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find OffPos entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('offpos'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', 'hidden')
      ->getForm();
  }
}
