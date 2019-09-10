<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Location;
use Scheduler\SchBundle\Form\LocationType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Location controller.
 *
 * @Route("/location")
 */
class LocationController extends Controller
{
  /**
   * Lists all Location entities.
   *
   * @Route("/", name="location")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:Location')->findAllOrderedByName();

    return array(
      'title' => 'Locations',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a Location entity.
   *
   * @Route("/{id}/show", name="location_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show Location',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Redirects to google maps based on Location entity.
   *
   * @Route("/redirect/{id}", name="location_redirect")
   * @Template()
   */
  public function mapRedirectAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }

    return $this->redirect('https://maps.google.com/maps?q=' . urlencode($entity->getLatitude() . ',' . $entity->getLongitude() . ' (' . $entity->getLongname() . ')') . '&hl=en&t=h&z=19');
  }

  /**
   * Displays a form to create a new Location entity.
   *
   * @Route("/new", name="location_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Location();
    $form = $this->createForm(LocationType::class, $entity);

    return array(
      'title' => 'New Location',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Clone one entity to make a new Location entity.
   *
   * @Route("/{id}/clone", name="location_clone")
   * @Template("SchedulerBundle:Location:new.html.twig")
   */
  public function cloneAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }
    // reset id so persist will create a new instance.
    $entity->resetForClone();

    $form = $this->createForm(LocationType::class, $entity);

    return array(
      'title' => 'Clone Location',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Location entity.
   *
   * @Route("/create", name="location_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Location:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Location();
    $form = $this->createForm(LocationType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('location_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Location entity.
   *
   * @Route("/{id}/edit", name="location_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }

    $editForm = $this->createForm(LocationType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit Location',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Location entity.
   *
   * @Route("/{id}/update", name="location_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Location:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(LocationType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('location_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Location entity.
   *
   * @Route("/{id}/delete", name="location_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Location')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Location entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('location'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }

  /**
   * Exports Location entities.
   *
   * @Route("/export.csv", name="location_export_csv")
   * @Template("SchedulerBundle:Location:export.csv.twig")
   */
  public function exportCsvAction()
  {
    $repository = $this->getDoctrine()->getRepository('SchedulerBundle:Location');
    $query = $repository->createQueryBuilder('s');
    $query->orderBy('s.name', 'ASC');

    $data = $query->getQuery()->getResult();
    $filename = "locations-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:Location:export.csv.twig', array('data' => $data));
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }
}
