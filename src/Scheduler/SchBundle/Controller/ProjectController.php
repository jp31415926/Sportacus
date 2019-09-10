<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Project;
use Scheduler\SchBundle\Form\ProjectType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Project controller.
 *
 * @Route("/project")
 */
class ProjectController extends Controller
{
  /**
   * Lists all Project entities.
   *
   * @Route("/", name="project")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('SchedulerBundle:Project')
						->findBy([], ['archived' => 'ASC', 'start_date' => 'DESC', 'end_date' => 'DESC']);

    return array(
      'title' => 'Projects',
      'entities' => $entities,
    );
  }

  /**
   * Finds and displays a Project entity.
   *
   * @Route("/{id}/show", name="project_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Project')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Projects',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new Project entity.
   *
   * @Route("/new", name="project_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Project();
    $form = $this->createForm(ProjectType::class, $entity);

    return array(
      'title' => 'Projects',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Project entity.
   *
   * @Route("/create", name="project_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Project:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Project();
    $form = $this->createForm(ProjectType::class, $entity);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('project_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Project entity.
   *
   * @Route("/{id}/edit", name="project_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Project')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $editForm = $this->createForm(ProjectType::class, $entity);
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Projects',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Project entity.
   *
   * @Route("/{id}/update", name="project_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Project:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Project')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createForm(ProjectType::class, $entity);
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('project_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Project entity.
   *
   * @Route("/{id}/delete", name="project_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Project')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Project entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('project'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }

  /**
   * Exports Project entities.
   *
   * @Route("/export.csv", name="project_export_csv")
   * @Template("SchedulerBundle:Project:export.csv.twig")
   */
  public function exportCsvAction()
  {
    $repository = $this->getDoctrine()->getRepository('SchedulerBundle:Project');
    $query = $repository->createQueryBuilder('s');
    $query->orderBy('s.start_date', 'ASC'); // TODO: order by end date

    $data = $query->getQuery()->getResult();
    $filename = "projects-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:Project:export.csv.twig', array('data' => $data));
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }
}
