<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\RefPntsMap;
use Scheduler\SchBundle\Form\RefPntsMapType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * RefPntsMap controller.
 *
 * @Route("/refpntsmap")
 */
class RefPntsMapController extends Controller
{
  /**
   * Lists all RefPntsMap entities.
   *
   * @Route("/", name="refpntsmap")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:RefPntsMap')->findAll();

    return array(
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a RefPntsMap entity.
   *
   * @Route("/{id}/show", name="refpntsmap_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:RefPntsMap')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find RefPntsMap entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new RefPntsMap entity.
   *
   * @Route("/new", name="refpntsmap_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new RefPntsMap();
    $form = $this->createForm(RefPntsMapType::class, $entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new RefPntsMap entity.
   *
   * @Route("/create", name="refpntsmap_create")
   * @Method("POST")
   * @Template("SchedulerBundle:RefPntsMap:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new RefPntsMap();
    $form = $this->createForm(RefPntsMapType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('refpntsmap_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing RefPntsMap entity.
   *
   * @Route("/{id}/edit", name="refpntsmap_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:RefPntsMap')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find RefPntsMap entity.');
    }

    $editForm = $this->createForm(RefPntsMapType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing RefPntsMap entity.
   *
   * @Route("/{id}/update", name="refpntsmap_update")
   * @Method("POST")
   * @Template("SchedulerBundle:RefPntsMap:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:RefPntsMap')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find RefPntsMap entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(RefPntsMapType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('refpntsmap_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a RefPntsMap entity.
   *
   * @Route("/{id}/delete", name="refpntsmap_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:RefPntsMap')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find RefPntsMap entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('refpntsmap'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }
}
