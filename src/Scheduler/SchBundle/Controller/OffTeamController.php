<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\OffTeam;
use Scheduler\SchBundle\Form\OffTeamType;

/**
 * OffTeam controller.
 *
 * @Route("/offteam")
 */
class OffTeamController extends Controller
{
  /**
   * Lists all OffTeam entities.
   *
   * @Route("/", name="offteam")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:OffTeam')->findAll();

    return array(
      'title' => 'Official Teams',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a OffTeam entity.
   *
   * @Route("/{id}/show", name="offteam_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffTeam')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffTeam entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show Official Team',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new OffTeam entity.
   *
   * @Route("/new", name="offteam_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new OffTeam();
    $form = $this->createForm(OffTeamType::class, $entity);

    return array(
      'title' => 'Create Official Team',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new OffTeam entity.
   *
   * @Route("/create", name="offteam_create")
   * @Method("POST")
   * @Template("SchedulerBundle:OffTeam:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new OffTeam();
    $form = $this->createForm(OffTeamType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offteam_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing OffTeam entity.
   *
   * @Route("/{id}/edit", name="offteam_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffTeam')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffTeam entity.');
    }

    $editForm = $this->createForm(OffTeamType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit Official Team',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing OffTeam entity.
   *
   * @Route("/{id}/update", name="offteam_update")
   * @Method("POST")
   * @Template("SchedulerBundle:OffTeam:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:OffTeam')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find OffTeam entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(OffTeamType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('offteam_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a OffTeam entity.
   *
   * @Route("/{id}/delete", name="offteam_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:OffTeam')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find OffTeam entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('offteam'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', 'hidden')
      ->getForm();
  }
}
