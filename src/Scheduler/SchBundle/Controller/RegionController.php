<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Region;
use Scheduler\SchBundle\Form\RegionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Region controller.
 *
 * @Route("/region")
 */
class RegionController extends Controller
{
  /**
   * Lists all Region entities.
   *
   * @Route("/", name="region")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:Region')->findAllOrderedByName();

    return array(
      'title' => 'Regions',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a Region entity.
   *
   * @Route("/{id}/show", name="region_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Region')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Region entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Regions',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new Region entity.
   *
   * @Route("/new", name="region_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Region();
    $form = $this->createForm(RegionType::class, $entity);

    return array(
      'title' => 'Regions',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Region entity.
   *
   * @Route("/create", name="region_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Region:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Region();
    $form = $this->createForm(RegionType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('region_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Region entity.
   *
   * @Route("/{id}/edit", name="region_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Region')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Region entity.');
    }

    $editForm = $this->createForm(RegionType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Regions',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Region entity.
   *
   * @Route("/{id}/update", name="region_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Region:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Region')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Region entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(RegionType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('region_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Region entity.
   *
   * @Route("/{id}/delete", name="region_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Region')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Region entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('region'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }
}
