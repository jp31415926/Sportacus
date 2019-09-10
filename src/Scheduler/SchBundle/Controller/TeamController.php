<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Team;
use Scheduler\SchBundle\Form\TeamType;
use Scheduler\SchBundle\Entity\RefPntsMap;
use Doctrine\ORM\EntityRepository;
use Scheduler\SchBundle\Form\TeamImportType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Team controller.
 *
 * @Route("/team")
 */
class TeamController extends Controller
{
  /**
   * Lists all Team entities.
   *
   * @Route("/", name="team")
   * @Template()
   */
//    public function indexAction(Request $request)
  public function indexAction()
  {
    /*        $form = $this->createFormBuilder()
                ->add('project', 'entity', array(
                    'class' => 'Scheduler\SchBundle\Entity\Project',
                    'empty_value' => 'All',
                    'required' => false,
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                              ->orderBy('p.start_date', 'ASC');
                     },)
                )
                ->getForm();

            $projectid = -1;
            if ($request->isMethod('POST')) {
                $form->bind($request);
                // data is an array with "project" key
                $data = $form->getData();
                if ($data['project'])
                    $projectid = $data['project']->getId();
            }
      */
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $em = $this->getDoctrine()->getManager();

    //$entities = $em->getRepository('SchedulerBundle:Team')->findAllOrderedByName();
    $entities = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);

    return array(
      'title' => 'Teams',
      'entities' => $entities,
      //'form'   => $form->createView(),
    );
  }

  /**
   * Finds and displays a Team entity.
   *
   * @Route("/{id}/show", name="team_show")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Team')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Team entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Show Team',
      'entity' => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to create a new Team entity.
   *
   * @Route("/new", name="team_new")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Team();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $region = $user->getRegion();
    $entity->setProject($project);
    $entity->setRegion($region);

    $em = $this->getDoctrine()->getManager();
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $form = $this->createForm(TeamType::class, $entity, array('agegroups' => $agegroups));

    return array(
      'title' => 'New Team',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a new Team entity.
   *
   * @Route("/create", name="team_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Team:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Team();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);
    $em = $this->getDoctrine()->getManager();

    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);
    $form = $this->createForm(TeamType::class, $entity, array('agegroups' => $agegroups));
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('team', array('id' => $entity->getId())));
    }

    return array(
      'title' => 'Create Team',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Clone one entity to make a new Team entity.
   *
   * @Route("/{id}/clone", name="team_clone")
   * @Template("SchedulerBundle:Team:new.html.twig")
   */
  public function cloneAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Team')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Team entity.');
    }
    // reset id so persist will create a new instance.
    $entity->resetForClone();

    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);

    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);
    $form = $this->createForm(TeamType::class, $entity, array('agegroups' => $agegroups));

    return array(
      'title' => 'Clone Team',
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing Team entity.
   *
   * @Route("/{id}/edit", name="team_edit")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Team')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Team entity.');
    }

    $user = $this->getUser();
    $project = $user->getCurrentproject();

    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $editForm = $this->createForm(TeamType::class, $entity, array('agegroups' => $agegroups));
    $deleteForm = $this->createDeleteForm($id);

    return array(
      'title' => 'Edit Team',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Edits an existing Team entity.
   *
   * @Route("/{id}/update", name="team_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Team:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Team')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Team entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $user = $this->getUser();
    $project = $user->getCurrentproject();

    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $editForm = $this->createForm(TeamType::class, $entity, array('agegroups' => $agegroups));
    //$editForm->bind($request);
    $editForm->handleRequest($request);

    if ($editForm->isSubmitted() && $editForm->isValid()) {
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('team_edit', array('id' => $id)));
    }

    return array(
      'title' => 'Edit Team',
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Deletes a Team entity.
   *
   * @Route("/{id}/delete", name="team_delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    //$form->bind($request);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SchedulerBundle:Team')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Team entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('team'));
  }

  private function createDeleteForm($id)
  {
    return $this->createFormBuilder(array('id' => $id))
      ->add('id', HiddenType::class)
      ->getForm();
  }

  /**
   * Import Teams
   *
   * @Route("/import", name="team_import")
   * @Template("SchedulerBundle:Team:import.html.twig")
   * @param Request $request
   * @param $id
   * @return array|RedirectResponse
   */
  public function importAction(Request $request)
  {
    $user = $this->getUser();
    //$em = $this->getDoctrine()->getManager();
    $project = $user->getCurrentproject();
    $result = array();

    $form = $this->createForm(TeamImportType::class);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        // Create a variable data directory based on where AppKernel lives
        $scriptDir = $this->get('kernel')->getRootDir().'/../src/Scheduler/SchBundle/Scripts';
        
        $dataDir = $this->container->getParameter('kernel.cache_dir').'/uploads';

        if (!file_exists($dataDir)) {
          mkdir($dataDir, 0777, true);
        }
        // FIXME: randomize filename after this is all working
        $file = $form['attachment']->getData();
        $dryrun = $form['dryrun']->getData() ? 1 : 0;
        $allowupdate = $form['allowupdate']->getData() ? 1 : 0;
        $inputFN = $file->getClientOriginalName();
        $file->move($dataDir, $inputFN);

        $cmd = '/usr/bin/php '.$scriptDir."/ImportTeams.php $dryrun $allowupdate ".$project->getId().' '.escapeshellarg($dataDir.'/'.$inputFN);
        $result[] = 'Command: '.$cmd;
        exec($cmd, $output, $return_var);
        foreach ($output as &$line) {
          $a = strpos($line, ' ');
          $color = '';
          if ($a > 0) {
            if (substr($line, 0, 4) == 'ERR_') {
              $color = 'red';
            }
            else if (substr($line, 0, $a) == 'ERR_DAT') {
              $color = 'blue';
            }
            if ($color != '') {
              $line = '<span style="color:'.$color.'">'.$line.'</span>';
            }
          }
        }
        $result[] = "Command return code was $return_var";
        $result[] = '';
        $result[] = 'Command output follows:';
        $result = array_merge($result, $output);
      } else {
        $result[] = 'form is not valid';
      }
    } else {
      $result = array("Required columns (case-sensitive):",
        "'Name' - name of team",
        "'Region' - region short name (i.e. R0894)",
        "'Division' - division (i.e. U08)",
        "",
        "Optional columns:",
        "'Coach Name' - coach name",
        "'Coach Email' - coach email to receive notifications",
        "'Coach Phone' - coach cell number to receive text notifications",
        "'POC Email' - additional email to receive notifications",
        "'Colors Home' - team home colors (one or two colors, separated by '/')",
        "'Colors Away' - team away colors (one or two colors, separated by '/')",
        );
    }

    return array(
      'title' => 'Team Import',
      'user' => $user,
      'output' => $result,
      'edit_form' => $form->createView(),
    );
  }

  /**
   * Exports Team entities.
   *
   * @Route("/export.csv", name="team_export_csv")
   * @Template("SchedulerBundle:Team:export.csv.twig")
   */
  public function exportCsvAction()
  {
    $user = $this->getUser();
    $project = $user->getCurrentproject();

    $em = $this->getDoctrine()->getManager();
    $entities = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    //$repository = $this->getDoctrine()->getRepository('SchedulerBundle:Team');
    //$query = $repository->createQueryBuilder('s');
    //$query->orderBy('s.name', 'ASC');

    //$data = $query->getQuery()->getResult();
    $filename = "teams-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:Team:export.csv.twig', array('data' => $entities));
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }

  /* calcUserRefPoints
   * This function calculates ref points for one user.
   * Returns an array of various data
   */

  public function calcUserRefPoints($project, $user)
  {
    $useTeamRegionRules = $project->getUseTeamRefpntRules();
    $region = $user->getRegion();

    $em = $this->getDoctrine()->getManager();
    //$repoRefPntsMap = $em->getRepository('SchedulerBundle:RefPntsMap');
    $repoAgeGroup = $em->getRepository('SchedulerBundle:AgeGroup');
    $repoTeam = $em->getRepository('SchedulerBundle:Team');

    $agegroups = $repoAgeGroup->findBy(array('project' => $project));
    $games = $em->getRepository('SchedulerBundle:Game')->findAllByOffcialAndProject($user->getId(), $project->getId());
    $gameinfo = array();
    $totalrefpoints = 0;

    foreach ($games as $game) {
      if ($useTeamRegionRules) {
        // get agegroup of team (region rules)
        $agegroup = $game->getAgegroup();
      } else {
        // get matching agegroup of referee's region
        $ageName = $game->getAgegroup()->getName();
        $agegroup = NULL;
        foreach ($agegroups as $ag) {
          if (($ag->getRegion() == $region) && ($ag->getName() == $ageName)) {
            $agegroup = $ag;
            break;
          }
        }
      }
      if ($agegroup && $agegroup->pointsNonZero()) {
        $refpoints = 0;
        //if ($game->getStatus() == 2) { // only count games that are "Complete"
        if ($game->getStatus() > 1) { // Count any game that is not Inactive or Normal (i.e. Cancelled, Terminated, etc.)
          if ($game->getRef1() == $user) {
            if ($user->getIsYouth()) {
              $refpoints = $agegroup->getPointsYouthRef1();
            } else {
              $refpoints = $agegroup->getPointsRef1();
            }
          } else if ($game->getRef2() == $user) {
            if ($user->getIsYouth()) {
              $refpoints = $agegroup->getPointsYouthRef2();
            } else {
              $refpoints = $agegroup->getPointsRef2();
            }
          } else if ($game->getRef3() == $user) {
            if ($user->getIsYouth()) {
              $refpoints = $agegroup->getPointsYouthRef3();
            } else {
              $refpoints = $agegroup->getPointsRef3();
            }
          }
        }
        $gameinfo[] = array(
          'game' => $game,
          'points' => $refpoints,
        );
        $totalrefpoints += $refpoints;
      }
    }

    return array(
      'games' => $gameinfo,
      'numgames' => count($gameinfo),
      'points' => $totalrefpoints,
    );
  }

  /**
   * Show Ref points breakdown page
   *
   * @Route("/refpnts/breakdown", name="ref_pnts_breakdown")
   * @Template("SchedulerBundle:Team:refpoints-breakdown.html.twig")
   */
  public function refPntsBreakdownAction(Request $request)
  {
    $user = $this->getUser();
    //$userid = $this->get('request')->get('id');
    $userid = $request->get('id');
    $em = $this->getDoctrine()->getManager();
    if ($userid) {
      $ref = $em->getRepository('SchedulerBundle:User')->find($userid);
    } else {
      $ref = $user;
    }
    
    $project = $user->getCurrentproject();
    $region = $user->getRegion();
    if (!$project) {
      return $this->redirect($this->generateUrl('user_currentproject'));
    }
    $useTeamRegionRules = $project->getUseTeamRefpntRules();
    if ($useTeamRegionRules) {
      // Can't list agegroup info if we are using team settings.
      $agegroups = array();
    } else {
      // Get agegroup info for the referee's region.
      $repoAgeGroup = $em->getRepository('SchedulerBundle:AgeGroup');
      $agegroups = $repoAgeGroup->findBy(array('project' => $project, 'region' => $region));
    }
    
    // TODO: only return agegroups that are used for the calculation, depending on $useTeamRegionRules
    return array(
      'title' => 'Official Points Breakdown',
      'gameinfo' => $this->calcUserRefPoints($project, $ref),
      'ref' => $ref,
      'project' => $project,
      'useTeamRegionRules' => $useTeamRegionRules,
      'agegroups' => $agegroups,
    );
  }

  /**
   * Show All Ref points page
   *
   * @Route("/refpnts/allrefs", name="ref_pnts_all")
   * @Template("SchedulerBundle:Team:refpoints-all.html.twig")
   */
  public function refPntsAllAction()
  {
    $user = $this->getUser();
    $region = $user->getRegion();
    $project = $user->getCurrentproject();
    if (!$project) {
      return $this->redirect($this->generateUrl('user_currentproject'));
    }
    $em = $this->getDoctrine()->getManager();
    $repoUser = $em->getRepository('SchedulerBundle:User');
    if ($this->isGranted('ROLE_ADMIN')) {
      $refs = $repoUser->findBy(array('role_referee' => 1));
    } else {
      $refs = $repoUser->findBy(array('role_referee' => 1, 'region' => $region));
    }
    $data = array();

    // TODO: probably need to write another function that calculates
    // all the ref points for all the refs in a region.  I think it would be faster.
    foreach ($refs as $ref) {
      $gameinfo = $this->calcUserRefPoints($project, $ref);
      $data[] = array(
        'ref' => $ref,
        'numgames' => $gameinfo['numgames'],
        'points' => $gameinfo['points'],
      );
    }

    uasort($data,
      function ($a, $b) {
        $d = $b['points'] - $a['points'];
        if ($d == 0)
          return $b['numgames'] - $a['numgames'];
        else
          return $d;
      }
    );

    return array(
      'title' => 'Official Point Totals',
      'refs' => $data,
    );
  }


  /**
   * Allocate ref points to teams
   *
   * expects array of referees with the following format:
   * array( keys = integer: user id of ref
   *   'ref' => key = user entity
   *   'points' => array( keys = none
   *     'points' => integer: total points that referee has earned
   *     'numgames' => integer: number of games refereed [unused in this function]
   *   'leftover' => integer: will be updated with points left over, if any
   *   'teams' => array( keys = integer priority
   *     'team' => Team entity
   *     'points' => integer: will be updated to number of points allocated to this team by this referee
   *   )
   * )
   *
   * returns teaminfo format:
   * array( key = integer: team id
   *   'name' => string: team name
   *   'need' => integer: how many points this team still needs (this plus points is total points this team's goal)
   *   'points' => integer: how many points this team has received
   * )
   */
  public function allocateRefPoints(&$reflist)
  {
    $done = false;
    $teaminfo = array();
    $debug = '';
    $debug2 = '';
    // copy point total to "leftover" then use that as a countdown counter
    foreach ($reflist as $refid => $ref) {
      $reflist[$refid]['leftover'] = $ref['points']['points'];
      $debug .= $ref['ref']->getFullName() . ' has ' . $ref['points']['points'] . " points\n";
      foreach ($ref['teams'] as $priority => $team) {
        $teamid = $team['team']->getId();
        if (!array_key_exists($teamid, $teaminfo)) {
          $teaminfo[$teamid] = array('name' => $team['team']->getName(), 'need' => $team['team']->getAgegroup()->getPointsTeamGoal(), 'points' => 0);
          $debug2 .= $teaminfo[$teamid]['name'] . ' needs ' . $teaminfo[$teamid]['need'] . " points\n";
        }
      }
    }

    $debug .= "\n" . $debug2;
    $round = 1;
    while (!$done) {
      $debug .= "\nRound $round\n";
      ++$round;
      $done = true;
      // loop through all referees, one at a time, allocating points
      foreach ($reflist as $refid => $ref) {
        //echo $ref['ref']->getFullName().' has '.$reflist[$refid]['leftover']." points\n";
        if ($reflist[$refid]['leftover'] > 0) {
          // if this referee has points, find a team to give one to
          for ($priority = 1; array_key_exists($priority, $ref['teams']); ++$priority) {
            // foreach ($ref['teams'] as $priority => $team) {
            $team = $ref['teams'][$priority];
            $teamid = $team['team']->getId();
            if ($teaminfo[$teamid]['need'] > 0) {
              --$teaminfo[$teamid]['need'];
              ++$teaminfo[$teamid]['points'];
              --$reflist[$refid]['leftover'];
              ++$reflist[$refid]['teams'][$priority]['points'];
              $done = false;
              $debug .= $ref['ref']->getFullName() . ' gave one point (' . $reflist[$refid]['leftover'] . ' left) to ' . $teaminfo[$teamid]['name'] . ' ' . $teaminfo[$teamid]['points'] . "\n";
              break; // no more points from this ref
            }
          }
        }
      }
    }

    $debug .= "\nFinal point count:\n";
    foreach ($reflist as $refid => $ref) {
      $debug .= $ref['ref']->getFullName() . ' has ' . $ref['points']['points'] . " points, " . $ref['leftover'] . " points unallocated\n";
      foreach ($ref['teams'] as $priority => $team) {
        $debug .= '  ' . $team['points'] . ' allocated to ' . $team['team']->getName() . "\n";
      }
    }

    //$f = fopen("/tmp/refpoints.txt", 'w');
    //fwrite($f, $debug);
    //fclose($f);
    //echo $debug;
    return $teaminfo;
  }


  /**
   * Show Ref points page, with ref priorities and teams
   *
   * @Route("/refpnts/assign", name="ref_pnts_assign")
   * @Template("SchedulerBundle:Team:refpoints-assign.html.twig")
   */
  public function refPntsAssignAction(Request $request)
  {
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    if (!$project) {
      return $this->redirect($this->generateUrl('user_currentproject'));
    }

    $useTeamRegionRules = $project->getUseTeamRefpntRules();

    $region = $user->getRegion();

    $em = $this->getDoctrine()->getManager();
    $repoRefPntsMap = $em->getRepository('SchedulerBundle:RefPntsMap');
    $repoAgeGroup = $em->getRepository('SchedulerBundle:AgeGroup');
    $repoTeam = $em->getRepository('SchedulerBundle:Team');

    $agegroups = $repoAgeGroup->findBy(array('project' => $project));

    // if we are using team region rules, then all teams are used, else only user's region
    if ($useTeamRegionRules) {
      $teams = $repoTeam->findBy(array('project' => $project));
    } else {
      $teams = $repoTeam->findBy(array('project' => $project, 'region' => $region));
      //$teams = $repoTeam->findBy(array('project' => $project));
    }
    if ($request->getMethod() == 'POST') {
      // delete all empty values, then sort and renumber (don't assume user knows how to count)
      $priorities = $request->get('priority');
      if (is_array($priorities)) {
        $rank = array();
        foreach ($priorities as $team => $priority) {
          if ((int)$priority > 0) {
            $rank[$team] = $priority;
          }
        }

        // delete all previous team assignments
        $entities = $repoRefPntsMap->findBy(
          array('project' => $project->getId(),
            'user' => $user->getId()));
        foreach ($entities as $entity) {
          $em->remove($entity);
        }

        if (count($rank)) {
          asort($rank);
          $priority = 1; // counter

          foreach ($rank as $teamid => $dontcare) {
            $team = $repoTeam->find($teamid);
            $entity = new RefPntsMap();
            $entity->setUser($user);
            $entity->setTeam($team);
            $entity->setProject($project);
            $entity->setPriority($priority);
            $em->persist($entity);
            ++$priority;
            // allow referee to assign to up to 3 teams
            if ($priority > 3)
              break;
          }
        }
        $em->flush();
      }
    }

    $teamarray = array();
    $myteams = array();
    $reflist = array();
    // build array of teams with refs and points per team
    foreach ($teams as $team) {
      // use team's region point rules or referee's region point rules depending on project settings
      if ($useTeamRegionRules || ($team->getRegion() == $region)) {
        // get agegroup of team (region rules)
        $agegroup = $team->getAgegroup();
      } else {
        // get matching agegroup of referee's region
        $ageName = $team->getAgegroup()->getName();
        $agegroup = NULL;
        foreach ($agegroups as $ag) {
          if (($ag->getRegion() == $region) && ($ag->getName() == $ageName)) {
            $agegroup = $ag;
            break;
          }
        }
      }
      if ($agegroup && $agegroup->getPointsTeamGoal()) {
        $refpnts = $repoRefPntsMap->findBy(array('project' => $project, 'team' => $team));
        $refs = array(); // holds all refs that picked this team
        $priority = 0;
        foreach ($refpnts as $refpnt) {
          $ref = $refpnt->getUser();
          $refid = $ref->getId();
          if (!array_key_exists($refid, $reflist)) {
            $points = $this->calcUserRefPoints($project, $ref);
            $reflist[$ref->getId()] = array('ref' => $ref, 'points' => $points, 'leftover' => 0, 'teams' => array());
          }
          // add this team to the ref team list
          $reflist[$refid]['teams'][$refpnt->getPriority()] = array('team' => $team, 'points' => 0);
          $refs[] = array('user' => $ref, 'priority' => $refpnt->getPriority(), 'points' => 0);
          if ($ref == $user) {
            $priority = $refpnt->getPriority();
          }
        }
        $t = array('name' => $team->getName(),
          'refs' => $refs,
          'points' => 0,
          'short' => $agegroup->getPointsTeamGoal(),
          'priority' => $priority,
        );
        if ($priority > 0) {
          $myteams[$team->getId()] = $t;
        } else {
          $teamarray[$team->getId()] = $t;
        }
      }
    }
    ksort($teamarray);
    uasort($myteams,
      function ($a, $b) {
        return $a['priority'] - $b['priority'];
      }
    );

    $teaminfo = $this->allocateRefPoints($reflist);
    /*
    echo "\nFinal point count:\n";
    foreach ($reflist as $refid => $ref) {
      echo $ref['ref']->getFullName().' has '.$ref['points']." points, ".$ref['leftover']." points unallocated\n";
      foreach ($ref['teams'] as $priority => $team) {
        echo '  '.$team['points'].' allocated to '.$team['team']->getName()."\n";
      }
    }
    */

    $teamlist = $myteams + $teamarray;

    foreach ($teamlist as $teamid => &$t) {
      if (array_key_exists($teamid, $teaminfo)) {
        $t['points'] = $teaminfo[$teamid]['points'];
        $t['short'] = $teaminfo[$teamid]['need'];
      }
      foreach ($t['refs'] as &$refs) {
        if (array_key_exists($refs['user']->getId(), $reflist)) {
          $ref = $reflist[$refs['user']->getId()];
          foreach ($ref['teams'] as $priority => $team) {
            if ($team['team']->getId() == $teamid) {
              $refs['points'] = $reflist[$refs['user']->getId()]['teams'][$priority]['points'];
            }
          }
        }
      }
    }

    if (array_key_exists($user->getId(), $reflist)) {
      $refinfo = $reflist[$user->getId()];
    } else {
      $points = $this->calcUserRefPoints($project, $user);
      $refinfo = array(
        'ref' => $user,
        'points' => $points,
        'leftover' => $points['points'],
      );
    }

    return array(
      'title' => 'Official Team Allocations',
      'teams' => $teamlist,
      //'refinfo' => $this->calcUserRefPoints($project, $user), // info for current user
      'refinfo' => $refinfo, // info for current user
    );
  }


  /**
   * Show Ref points page, for everyone
   *
   * @Route("/refpnts", name="ref_pnts_summary")
   * @Template("SchedulerBundle:Team:refpoints.html.twig")
   */
  public function refPntsAction()
  {
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    if (!$project) {
      return $this->redirect($this->generateUrl('user_currentproject'));
    }

    $useTeamRegionRules = $project->getUseTeamRefpntRules();

    $region = $user->getRegion();

    $em = $this->getDoctrine()->getManager();
    $repoRefPntsMap = $em->getRepository('SchedulerBundle:RefPntsMap');
    $repoAgeGroup = $em->getRepository('SchedulerBundle:AgeGroup');
    $repoTeam = $em->getRepository('SchedulerBundle:Team');

    $agegroups = $repoAgeGroup->findBy(array('project' => $project));

    // if we are using team region rules, then all teams are used, else only user's region
    if ($useTeamRegionRules) {
      $teams = $repoTeam->findBy(array('project' => $project));
    } else {
      $teams = $repoTeam->findBy(array('project' => $project, 'region' => $region));
      //$teams = $repoTeam->findBy(array('project' => $project));
    }

    $teamarray = array();
    $myteams = array();
    $reflist = array();
    // build array of teams with refs and points per team
    foreach ($teams as $team) {
      // use team's region point rules or referee's region point rules depending on project settings
      if ($useTeamRegionRules || ($team->getRegion() == $region)) {
        // get agegroup of team (region rules)
        $agegroup = $team->getAgegroup();
      } else {
        // get matching agegroup of referee's region
        $ageName = $team->getAgegroup()->getName();
        $agegroup = NULL;
        foreach ($agegroups as $ag) {
          if (($ag->getRegion() == $region) && ($ag->getName() == $ageName)) {
            $agegroup = $ag;
            break;
          }
        }
      }
      if ($agegroup && $agegroup->getPointsTeamGoal()) {
        $refpnts = $repoRefPntsMap->findBy(array('project' => $project, 'team' => $team));
        $refs = array(); // holds all refs that picked this team
        $priority = 0;
        foreach ($refpnts as $refpnt) {
          $ref = $refpnt->getUser();
          $refid = $ref->getId();
          if (!array_key_exists($refid, $reflist)) {
            $points = $this->calcUserRefPoints($project, $ref);
            $reflist[$ref->getId()] = array('ref' => $ref, 'points' => $points, 'leftover' => 0, 'teams' => array());
          }
          // add this team to the ref team list
          $reflist[$refid]['teams'][$refpnt->getPriority()] = array('team' => $team, 'points' => 0);
          $refs[] = array('user' => $ref, 'priority' => $refpnt->getPriority(), 'points' => 0);
          if ($ref == $user) {
            $priority = $refpnt->getPriority();
          }
        }
        $t = array('name' => $team->getName(),
          'refs' => $refs,
          'points' => 0,
          'short' => $agegroup->getPointsTeamGoal(),
        );
        $teamarray[$team->getId()] = $t;
      }
    }
    ksort($teamarray);

    $teaminfo = $this->allocateRefPoints($reflist);
    /*
    echo "\nFinal point count:\n";
    foreach ($reflist as $refid => $ref) {
      echo $ref['ref']->getFullName().' has '.$ref['points']." points, ".$ref['leftover']." points unallocated\n";
      foreach ($ref['teams'] as $priority => $team) {
        echo '  '.$team['points'].' allocated to '.$team['team']->getName()."\n";
      }
    }
    */

    $teamlist = $teamarray;

    foreach ($teamlist as $teamid => &$t) {
      if (array_key_exists($teamid, $teaminfo)) {
        $t['points'] = $teaminfo[$teamid]['points'];
        $t['short'] = $teaminfo[$teamid]['need'];
      }
      foreach ($t['refs'] as &$refs) {
        if (array_key_exists($refs['user']->getId(), $reflist)) {
          $ref = $reflist[$refs['user']->getId()];
          foreach ($ref['teams'] as $priority => $team) {
            if ($team['team']->getId() == $teamid) {
              $refs['points'] = $reflist[$refs['user']->getId()]['teams'][$priority]['points'];
            }
          }
        }
      }
    }

    return array(
      'title' => 'Team Official Points Summary',
      'teams' => $teamlist,
    );
  }

  /**
   * Show Ref points page, for everyone
   *
   * @Route("/refpntspublic", name="ref_pnts_public_summary")
   * @Template("SchedulerBundle:Team:refpoints.public.html.twig")
   */
  public function refPntsPublicAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $defaultData = [];
    // get project and region from URL if it is there
    $projectid = $request->get('project');
    $regionid = $request->get('region');
    $project = NULL;
    $region = NULL;

    // if a user is logged in, user their current project and region as a default
    $user = $this->getUser();
    if ($user) {
      if (!$projectid) {
        $projectid = $user->getCurrentproject()->getId();
      }
      if (!$regionid) {
        $regionid = $user->getRegion()->getId();
      }
    }

    // if we have a project id from URL or user, load it up and set the pulldown default    
    if ($projectid) {
      $project = $em->getRepository('SchedulerBundle:Project')->find($projectid);
      $defaultData['project'] = $project;
    }
    // if we have a region id from URL or user, load it up and set the pulldown default
    if ($regionid) {
      $region = $em->getRepository('SchedulerBundle:Region')->find($regionid);
      $defaultData['region'] = $region;
    }

    $form = $this->createFormBuilder($defaultData)
      ->add('project', EntityType::class, [
        'class' => 'Scheduler\SchBundle\Entity\Project',
        'placeholder' => 'Select Project',
        'required' => false,
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('p')
            ->orderBy('p.start_date', 'ASC');
        }
      ])
      ->add('region', EntityType::class, [
        'class' => 'Scheduler\SchBundle\Entity\Region',
        'placeholder' => 'Select Region',
        'required' => false,
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC');
        }
      ])
      ->getForm();

    // if the user makes a change to the form, redirect to the page with the URL variables updated so they can cut and paste the URL to get the same page.
    // TODO: perhaps it would be better to just have a link to the page on the page instead of redirecting.
    if ($request->isMethod('POST')) {
      //$form->bind($request);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
      // data is an array with "project" key
      $data = $form->getData();
      if ($data['project']) {
        $projectid_post = $data['project']->getId();
        if ($projectid_post != $projectid) {
          $project_post = $em->getRepository('SchedulerBundle:Project')->find($projectid_post);
        }
      }
      if ($data['region']) {
        $regionid_post = $data['region']->getId();
        if ($regionid_post != $regionid) {
          $region_post = $em->getRepository('SchedulerBundle:Region')->find($regionid_post);
        }
      }
      if (isset($project_post) && isset($region_post))
        return $this->redirect($this->generateUrl('ref_pnts_public_summary', ['project' => $projectid_post, 'region' => $regionid_post]));
      }
    }

    if (!$project || !$region) {
      return array(
        'title' => 'Team Official Points',
        'project' => $project,
        'form' => $form->createView(),
      );
    }

    $useTeamRegionRules = $project->getUseTeamRefpntRules();

    $repoRefPntsMap = $em->getRepository('SchedulerBundle:RefPntsMap');
    $repoAgeGroup = $em->getRepository('SchedulerBundle:AgeGroup');
    $repoTeam = $em->getRepository('SchedulerBundle:Team');

    $agegroups = $repoAgeGroup->findBy(array('project' => $project));

    // if we are using team region rules, then all teams are used, else only user's region
    if ($useTeamRegionRules) {
      $teams = $repoTeam->findBy(['project' => $project]);
    } else {
      $teams = $repoTeam->findBy(['project' => $project, 'region' => $region]);
    }

    $teamarray = [];
    $myteams = [];
    $reflist = [];
    // build array of teams with refs and points per team
    foreach ($teams as $team) {
      // use team's region point rules or referee's region point rules depending on project settings
      if ($useTeamRegionRules) {
        // get agegroup of team (region rules)
        $agegroup = $team->getAgegroup();
      } else {
        // get matching agegroup of referee's region
        $ageName = $team->getAgegroup()->getName();
        $agegroup = NULL;
        foreach ($agegroups as $ag) {
          if (($ag->getRegion() == $region) && ($ag->getName() == $ageName)) {
            $agegroup = $ag;
            break;
          }
        }
      }
      if ($agegroup && $agegroup->getPointsTeamGoal()) {
        $refpnts = $repoRefPntsMap->findBy(['project' => $project, 'team' => $team]);
        $refs = []; // holds all refs that picked this team
        $priority = 0;
        foreach ($refpnts as $refpnt) {
          $ref = $refpnt->getUser();
          $refid = $ref->getId();
          if (!array_key_exists($refid, $reflist)) {
            $points = $this->calcUserRefPoints($project, $ref);
            $reflist[$ref->getId()] = ['ref' => $ref, 'points' => $points, 'leftover' => 0, 'teams' => array()];
          }
          // add this team to the ref team list
          $reflist[$refid]['teams'][$refpnt->getPriority()] = ['team' => $team, 'points' => 0];
          $refs[] = ['user' => $ref, 'priority' => $refpnt->getPriority(), 'points' => 0];
        }
        $t = ['name' => $team->getName(),
          'refs' => $refs,
          'points' => 0,
          'short' => $agegroup->getPointsTeamGoal(),
        ];
        $teamarray[$team->getId()] = $t;
      }
    }
    ksort($teamarray);

    $teaminfo = $this->allocateRefPoints($reflist);
    /*
    echo "\nFinal point count:\n";
    foreach ($reflist as $refid => $ref) {
      echo $ref['ref']->getFullName().' has '.$ref['points']." points, ".$ref['leftover']." points unallocated\n";
      foreach ($ref['teams'] as $priority => $team) {
        echo '  '.$team['points'].' allocated to '.$team['team']->getName()."\n";
      }
    }
    */

    $teamlist = $teamarray;

    foreach ($teamlist as $teamid => &$t) {
      if (array_key_exists($teamid, $teaminfo)) {
        $t['points'] = $teaminfo[$teamid]['points'];
        $t['short'] = $teaminfo[$teamid]['need'];
      }
      foreach ($t['refs'] as &$refs) {
        if (array_key_exists($refs['user']->getId(), $reflist)) {
          $ref = $reflist[$refs['user']->getId()];
          foreach ($ref['teams'] as $priority => $team) {
            if ($team['team']->getId() == $teamid) {
              $refs['points'] = $reflist[$refs['user']->getId()]['teams'][$priority]['points'];
            }
          }
        }
      }
    }

    return array(
      'title' => 'Team Official Points Summary',
      'teams' => $teamlist,
      'project' => $project,
      'form' => $form->createView(),
    );
  }
}
