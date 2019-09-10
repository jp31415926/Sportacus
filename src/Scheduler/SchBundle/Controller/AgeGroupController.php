<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\AgeGroup;
use Scheduler\SchBundle\Form\AgeGroupType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

//use Avro\CsvBundle\Form\Type\CsvFormType;
//use Avro\CsvBundle\Form\Handler\CsvFormHandler;

/**
 * AgeGroup controller.
 *
 * @Route("/agegroup")
 */
class AgeGroupController extends Controller
{
  /**
   * Lists all AgeGroup entities.
   *
   * @Route("/", name="agegroup")
   * @Template()
   */
  public function indexAction()
  {
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $em = $this->getDoctrine()->getManager();

    //$entities = $em->getRepository('SchedulerBundle:AgeGroup')->findAllOrderedByName();
    $entities = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    return array(
      'title' => 'Age Groups',
      'entities' => $entities,
    );
  }

  /**
   * Lists all AgeGroup entities. (for debugging purposes)
   *
   * @Route("/all", name="agegroup_all")
   * @Template("SchedulerBundle:AgeGroup:index.html.twig")
   */
  public function index2Action()
  {
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:AgeGroup')->findAllOrderedByName();
    //$entities = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    return array(
      'title' => 'Age Groups',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a AgeGroup entity.
   *
   * @Route("/{id}/show", name="agegroup_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:AgeGroup')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find AgeGroup entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Age Groups',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new AgeGroup entity.
   *
   * @Route("/new", name="agegroup_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new AgeGroup();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $region = $user->getRegion();
    $entity->setProject($project);
    $entity->setRegion($region);
    $form = $this->createForm(AgeGroupType::class, $entity);

    return array(
      'title' => 'Age Groups',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new AgeGroup entity.
   *
   * @Route("/create", name="agegroup_create")
   * @Method("POST")
   * @Template("SchedulerBundle:AgeGroup:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new AgeGroup();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);
    $form = $this->createForm(AgeGroupType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('agegroup_show', array('id' => $entity->getId())));
    }

    return array(
      'title' => 'Age Groups',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing AgeGroup entity.
   *
   * @Route("/{id}/edit", name="agegroup_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:AgeGroup')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find AgeGroup entity.');
    }

    $editForm = $this->createForm(AgeGroupType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Age Groups',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing AgeGroup entity.
   *
   * @Route("/{id}/update", name="agegroup_update")
   * @Method("POST")
   * @Template("SchedulerBundle:AgeGroup:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:AgeGroup')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find AgeGroup entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(AgeGroupType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('agegroup_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a AgeGroup entity.
   *
   * @Route("/{id}/delete", name="agegroup_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:AgeGroup')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find AgeGroup entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('agegroup'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }

  /**
   * Exports AgeGroup entities.
   *
   * @Route("/export.csv", name="agegroup_export_csv")
   * @Template("SchedulerBundle:AgeGroup:export.csv.twig")
   */
  public function exportCsvAction()
  {
    $repository = $this->getDoctrine()->getRepository('SchedulerBundle:AgeGroup');
    $query = $repository->createQueryBuilder('s');
    $query->orderBy('s.difficulty', 'ASC');

    $data = $query->getQuery()->getResult();
    $filename = "agegroups-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:AgeGroup:export.csv.twig', array('data' => $data));
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }
}
