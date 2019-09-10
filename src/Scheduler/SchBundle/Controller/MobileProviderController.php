<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\MobileProvider;
use Scheduler\SchBundle\Form\MobileProviderType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * MobileProvider controller.
 *
 * @Route("/mobileprovider")
 */
class MobileProviderController extends Controller
{
  /**
   * Lists all MobileProvider entities.
   *
   * @Route("/", name="mobileprovider")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:MobileProvider')->findAll();

    return array(
      'title' => 'Mobile Providers',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a MobileProvider entity.
   *
   * @Route("/{id}/show", name="mobileprovider_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:MobileProvider')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find MobileProvider entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show Mobile Provider',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new MobileProvider entity.
   *
   * @Route("/new", name="mobileprovider_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new MobileProvider();
    $form = $this->createForm(MobileProviderType::class, $entity);

    return array(
      'title' => 'New Mobile Provider',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new MobileProvider entity.
   *
   * @Route("/create", name="mobileprovider_create")
   * @Method("POST")
   * @Template("SchedulerBundle:MobileProvider:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new MobileProvider();
    $form = $this->createForm(MobileProviderType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('mobileprovider_show', array('id' => $entity->getId())));
    }

    return array(
      'title' => 'New Mobile Provider',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing MobileProvider entity.
   *
   * @Route("/{id}/edit", name="mobileprovider_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:MobileProvider')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find MobileProvider entity.');
    }

    $editForm = $this->createForm(MobileProviderType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit Mobile Provider',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing MobileProvider entity.
   *
   * @Route("/{id}/update", name="mobileprovider_update")
   * @Method("POST")
   * @Template("SchedulerBundle:MobileProvider:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:MobileProvider')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find MobileProvider entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(MobileProviderType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('mobileprovider_edit', array('id' => $id)));
    }

    return array(
      'title' => 'Edit Mobile Provider',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a MobileProvider entity.
   *
   * @Route("/{id}/delete", name="mobileprovider_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:MobileProvider')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find MobileProvider entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('mobileprovider'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(['id' => $id])
      ->add('id', HiddenType::class)
      ->getForm();
  }
}
