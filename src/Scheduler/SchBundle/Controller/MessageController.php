<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Message;
use Scheduler\SchBundle\Form\MessageType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller
{
  /**
   * Lists all Message entities.
   *
   * @Route("/", name="message")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:Message')->findAll();

    return array(
      'title' => 'Messages',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a Message entity.
   *
   * @Route("/{id}/show", name="message_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Message')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Message entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show Message',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new Message entity.
   *
   * @Route("/new", name="message_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Message();
    $form = $this->createForm(MessageType::class, $entity);

    return array(
      'title' => 'New Message',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Message entity.
   *
   * @Route("/create", name="message_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Message:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Message();
    $form = $this->createForm(MessageType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('message_show', array('id' => $entity->getId())));
    }

    return array(
      'title' => 'Create Message',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Message entity.
   *
   * @Route("/{id}/edit", name="message_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Message')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Message entity.');
    }

    $editForm = $this->createForm(MessageType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit Message',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Message entity.
   *
   * @Route("/{id}/update", name="message_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Message:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Message')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Message entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(MessageType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('message_edit', array('id' => $id)));
    }

    return array(
      'title' => 'Update Message',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Message entity.
   *
   * @Route("/{id}/delete", name="message_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Message')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Message entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('message'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(['id' => $id])
      ->add('id', HiddenType::class)
      ->getForm();
  }
}
