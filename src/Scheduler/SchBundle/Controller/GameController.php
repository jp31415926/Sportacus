<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Game;
use Scheduler\SchBundle\Entity\LogGame;
use Scheduler\SchBundle\Entity\GameListCriteria;
use Scheduler\SchBundle\Entity\User;
use Scheduler\SchBundle\Entity\OffPos;
use Scheduler\SchBundle\Entity\Team;
use Scheduler\SchBundle\Entity\AgeGroup;
use Scheduler\SchBundle\Entity\GameRepository;
use Scheduler\SchBundle\Form\GameType;
use Scheduler\SchBundle\Form\GameAssignType;
use Scheduler\SchBundle\Form\GameScorecardType;
use Scheduler\SchBundle\Form\GameListCriteriaType;
use Scheduler\SchBundle\Form\GameImportType;

//use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Game controller.
 *
 * @Route("/game")
 */
class GameController extends Controller {

  public function parseSearchParameters(Request $request) {
    $session = $request->getSession();
    // first get search terms from session cookies
    $criteria = new GameListCriteria();
    if ($session->has('game_search_criteria')) {
      $criteria->setCriteriaIfNotBlank($session->get('game_search_criteria'));
    }

    // next check for URL variables.
    $team = $request->query->get('team');
    if (!empty($team)) {
      $criteria->setTeam(urldecode($team));
		}
    $sdate = $request->query->get('sdate');
    if (!empty($sdate)) {
      $criteria->setStartDate(new \DateTime($sdate));
		}
    $edate = $request->query->get('edate');
    if (!empty($edate)) {
      $criteria->setEndDate(new \DateTime($edate));
		}
    $loc = $request->query->get('loc');
    if (!empty($loc)) {
      $criteria->setLocation(urldecode($loc));
		}
    $official = $request->query->get('official');
    if (!empty($official)) {
      $criteria->setOfficial(urldecode($official));
		}

    // check for team schedule parameter (teamSchId=<team id>)
    $teamid = $request->query->get('teamSchId');
    if (!empty($teamid)) {
      // reset dates to project start and end dates
      $em = $this->getDoctrine()->getManager();
      $teamRepo = $em->getRepository('SchedulerBundle:Team');
      $team = $teamRepo->findOneById($teamid);
      if (isset($team)) {
        $project = $team->getProject();
        $criteria->setTeam($team);
        $criteria->setStartDate($project->getStartDate());
        $criteria->setEndDate($project->getEndDate());
      }
    }
    // check for team schedule parameter (teamSchName=<team name>)
    //$teamname = $request->query->get('teamSchName');
    //if (!empty($teamname)) {
    //  // reset dates to project start and end dates
    //  $em = $this->getDoctrine()->getManager();
    //  $teamRepo = $em->getRepository('SchedulerBundle:Team');
    //  $team = $teamRepo->findOneByName($teamname);
    //  if (isset($team)) {
    //    $project = $team->getProject();
    //    $criteria->setTeam($team);
    //    $criteria->setStartDate($project->getStartDate());
    //    $criteria->setEndDate($project->getEndDate());
    //  }
    //}
    return $criteria;
  }

  /**
   * Lists all Game entities.
   *
   * @Route("/", name="game")
   * @Template()
   * @param Request $request
   * @return array
   */
  public function indexAction(Request $request) {
    ini_set('memory_limit', '256M');
    $session = $request->getSession();
    $criteria = $this->parseSearchParameters($request);

    $form = $this->createForm(GameListCriteriaType::class, $criteria);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $session->set('game_search_criteria', $criteria->getCriteria());
    }

    //echo "<br><br><br>team = ".$criteria->getTeam()."<br>\n";

    $em = $this->getDoctrine()->getManager();

    // if user is scheduler, get all games... else only published games
    $onlyPublished = $this->isGranted('ROLE_SCHEDULER') ? false : true;
    /** @var GameRepository $GameRepo */
    $GameRepo = $em->getRepository('SchedulerBundle:Game');
    $games = $GameRepo->findByCriteria($onlyPublished, $criteria);

    $totalGameConflicts = 0;
    $totalTeamConflicts = 0;

    if ($criteria->getCheckForConflicts()) {
      foreach ($games as $game) {
        /** @var Game $game */
        if (!$onlyPublished || $game->isActive()) {
          $GameConflicts = $GameRepo->findConflictsWithOtherGames($game);
          $Team1Conflicts = $GameRepo->findTeamGameConflicts($game, $game->getTeam1());
          $Team2Conflicts = $GameRepo->findTeamGameConflicts($game, $game->getTeam2());

          $totalGameConflicts += count($GameConflicts);
          $totalTeamConflicts += count($Team1Conflicts) + count($Team2Conflicts);

          $game->setGameConflicts($GameConflicts);
          $game->setTeam1Conflicts($Team1Conflicts);
          $game->setTeam2Conflicts($Team2Conflicts);
        }
      }
    }
    $stats = ['gameConflicts' => $totalGameConflicts, 'teamConflicts' => $totalTeamConflicts];

    return [
        'title' => 'Game Schedule',
        'games' => $games,
        'form' => $form->createView(),
        'stats' => $stats,
    ];
  }

  /**
   * Reset session variables for search form.
   *
   * @Route("/reset", name="reset_critiera")
   * @param Request $request
   * @return array
   */
  public function resetAction(Request $request) {
    $session = $request->getSession();

    // reset to default criteria
    $criteria = new GameListCriteria();
    $session->set('game_search_criteria', $criteria->getCriteria());
    return $this->redirectToRoute('game');
  }

  /**
   * Reset session variables for search form.
   *
   * @Route("/offsch/reset", name="reset_off_critiera")
   * @param Request $request
   * @return array
   */
  public function resetOffAction(Request $request) {
    $session = $request->getSession();

    // reset to default criteria
    $criteria = new GameListCriteria();
    $session->set('game_search_criteria', $criteria->getCriteria());
    return $this->redirectToRoute('official_schedule');
  }

  /**
   * Lists all Game entities.
   *
   * @Route("/offsch/", name="official_schedule")
   * @Template()
   * @param Request $request
   * @return array
   */
  public function offschAction(Request $request) {
    $session = $request->getSession();

    $criteria = $this->parseSearchParameters($request);
    /*
      $criteria = new GameListCriteria();
      if ($session->has('game_search_criteria')) {
      $criteria->setCriteria($session->get('game_search_criteria'));
      }
     */
    $form = $this->createForm(GameListCriteriaType::class, $criteria);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $session->set('game_search_criteria', $criteria->getCriteria());
    }

    $em = $this->getDoctrine()->getManager();

    // if user is scheduler, get all games... else only published games
    $onlyPublished = $this->isGranted('ROLE_SCHEDULER') ? false : true;

    /** @var GameRepository $GameRepo */
    $GameRepo = $em->getRepository('SchedulerBundle:Game');
    $games = $GameRepo->findByCriteria($onlyPublished, $criteria);

    $totalGameConflicts = 0;
    $totalTeamConflicts = 0;
    $totalRefConflicts = 0;

    if ($criteria->getCheckForConflicts()) {
      foreach ($games as $game) {
        /** @var Game $game */
        if (!$onlyPublished || $game->isActive()) {
          $GameConflicts = $GameRepo->findConflictsWithOtherGames($game);
          $Team1Conflicts = $GameRepo->findTeamGameConflicts($game, $game->getTeam1());
          $Team2Conflicts = $GameRepo->findTeamGameConflicts($game, $game->getTeam2());
          $Ref1Conflicts = $GameRepo->findOfficialGameConflicts($game, $game->getRef1());
          $Ref2Conflicts = $GameRepo->findOfficialGameConflicts($game, $game->getRef2());
          $Ref3Conflicts = $GameRepo->findOfficialGameConflicts($game, $game->getRef3());
          $Ref4Conflicts = $GameRepo->findOfficialGameConflicts($game, $game->getRef4());
          $Ref5Conflicts = $GameRepo->findOfficialGameConflicts($game, $game->getRef5());

          $totalGameConflicts += count($GameConflicts);
          $totalTeamConflicts += count($Team1Conflicts) + count($Team2Conflicts);
          $totalRefConflicts += count($Ref1Conflicts) + count($Ref2Conflicts) + count($Ref3Conflicts) + count($Ref4Conflicts) + count($Ref5Conflicts);

          $game->setGameConflicts($GameConflicts);
          $game->setTeam1Conflicts($Team1Conflicts);
          $game->setTeam2Conflicts($Team2Conflicts);
          $game->setRef1Conflicts($Ref1Conflicts);
          $game->setRef2Conflicts($Ref2Conflicts);
          $game->setRef3Conflicts($Ref3Conflicts);
          $game->setRef4Conflicts($Ref4Conflicts);
          $game->setRef5Conflicts($Ref5Conflicts);
        }
      }
    }

    $stats = [
        'gameConflicts' => $totalGameConflicts,
        'teamConflicts' => $totalTeamConflicts,
        'refConflicts' => $totalRefConflicts
    ];

    return [
        'title' => 'Game Schedule (with Officials)',
        'games' => $games,
        'form' => $form->createView(),
        'stats' => $stats,
    ];
  }

  /**
   * Show a report about coverage
   *
   * @Route("/coverage", name="game_coverage")
   * @Template()
   * @param Request $request
   * @return array
   */
  public function coverageAction(Request $request) {
    $session = $this->get('session');
    $user = $this->getUser();
    if ($user) {
      $region = $user->getRegion();
    } else {
      $region = false;
    }

    $criteria = new GameListCriteria();
    if ($session->has('game_search_criteria')) {
      $criteria->setCriteria($session->get('game_search_criteria'));
    }

    $team = $request->query->get('team');

    if (!empty($team)) {
      $criteria->setTeam($team);
    }

    $form = $this->createForm(GameListCriteriaType::class, $criteria);
    $form->handleRequest($request);
    if ($form->isValid()) {
      $session->set('game_search_criteria', $criteria->getCriteria());
    }

    $em = $this->getDoctrine()->getManager();

    // always get all games (including unpublished)
    /** @var GameRepository $GameRepo */
    $GameRepo = $em->getRepository('SchedulerBundle:Game');
    $games = $GameRepo->findByCriteria(false, $criteria);

    $slots_assigned = [];
    $slots_total = [];
    $slots_percent = [];
    $total_assigned = [0, 0, 0];
    $total_slots = [0, 0, 0];
    $total_percent = [0, 0, 0];
    $slots_assigned2 = [];
    $slots_total2 = [];
    $slots_percent2 = [];
    $total_assigned2 = [0, 0, 0];
    $total_slots2 = [0, 0, 0];
    $total_percent2 = [0, 0, 0];

    foreach ($games as $game) {
      /** @var Game $game */
      $status = $game->getStatus();
      $agegroup = $game->getAgegroup();
      //$ishome = ($game->getRegion() == $region);
      if ($game->getPublished() &&
              (isset($agegroup)) &&
              ($agegroup->getDifficulty() > 60) && // FIXME
              ($status == Game::STATUS_NORMAL) ||
              ($status == Game::STATUS_COMPLETE)
      ) {
        $refs = $game->getOfficials();
        $agegroup_name = $agegroup->getName();
        $refs_required = ($agegroup->getDifficulty() > 80) ? 3 : 1; //FIXME
        if ($game->getRegion() == $region) {
          for ($ref = 0; $ref < $refs_required; ++$ref) {
            if (!array_key_exists($agegroup_name, $slots_total)) {
              $slots_total[$agegroup_name] = [];
              $slots_assigned[$agegroup_name] = [];
              $slots_percent[$agegroup_name] = [];
            }
            if (!array_key_exists($ref, $slots_total[$agegroup_name])) {
              $slots_total[$agegroup_name][$ref] = 0;
              $slots_assigned[$agegroup_name][$ref] = 0;
              $slots_percent[$agegroup_name][$ref] = 0.0;
            }
            ++$slots_total[$agegroup_name][$ref];
            ++$total_slots[$ref];
            if ($refs[$ref]) { // is assigned
              ++$slots_assigned[$agegroup_name][$ref];
              ++$total_assigned[$ref];
            }
            $slots_percent[$agegroup_name][$ref] = round($slots_assigned[$agegroup_name][$ref] / $slots_total[$agegroup_name][$ref] * 100, 0);
            $total_percent[$ref] = round($total_assigned[$ref] / $total_slots[$ref] * 100, 0);
          }
        } else { // away game
          for ($ref = 0; $ref < $refs_required; ++$ref) {
            if (!array_key_exists($agegroup_name, $slots_total2)) {
              $slots_total2[$agegroup_name] = [];
              $slots_assigned2[$agegroup_name] = [];
              $slots_percent2[$agegroup_name] = [];
            }
            if (!array_key_exists($ref, $slots_total2[$agegroup_name])) {
              $slots_total2[$agegroup_name][$ref] = 0;
              $slots_assigned2[$agegroup_name][$ref] = 0;
              $slots_percent2[$agegroup_name][$ref] = 0.0;
            }
            ++$slots_total2[$agegroup_name][$ref];
            ++$total_slots2[$ref];
            if ($refs[$ref]) { // is assigned
              ++$slots_assigned2[$agegroup_name][$ref];
              ++$total_assigned2[$ref];
            }
            $slots_percent2[$agegroup_name][$ref] = round($slots_assigned2[$agegroup_name][$ref] / $slots_total2[$agegroup_name][$ref] * 100, 0);
            $total_percent2[$ref] = round($total_assigned2[$ref] / $total_slots2[$ref] * 100, 0);
          }
        }
      }
    }
    ksort($slots_total);
    ksort($slots_assigned);
    ksort($slots_percent);
    ksort($total_assigned);
    ksort($total_slots);
    ksort($total_percent);
    ksort($slots_total2);
    ksort($slots_assigned2);
    ksort($slots_percent2);
    ksort($total_assigned2);
    ksort($total_slots2);
    ksort($total_percent2);
    return [
        'title' => 'Game Coverage',
        'games' => $games,
        'region' => ($region ? $region->getLongName(): null),
        'form' => $form->createView(),
        'slots_assigned' => $slots_assigned,
        'slots_total' => $slots_total,
        'slots_percent' => $slots_percent,
        'total_assigned' => $total_assigned,
        'total_slots' => $total_slots,
        'total_percent' => $total_percent,
        'slots_assigned2' => $slots_assigned2,
        'slots_total2' => $slots_total2,
        'slots_percent2' => $slots_percent2,
        'total_assigned2' => $total_assigned2,
        'total_slots2' => $total_slots2,
        'total_percent2' => $total_percent2,
    ];
  }

  /**
   * Finds and displays a Game entity.
   *
   * @Route("/{id}/show", name="game_show")
   * @Template()
   * @param $id
   * @return array
   */
  public function showAction($id) {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return [
        'title' => 'Show Game',
        'entity' => $entity,
        'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Displays a form to create a new Game entity.
   *
   * @Route("/new", name="game_new")
   * @Template()
   */
  public function newAction() {
    $entity = new Game();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);
    $region = $user->getRegion();
    $entity->setRegion($region);

    $em = $this->getDoctrine()->getManager();
    $teams = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $form = $this->createForm(GameType::class, $entity, ['teams' => $teams, 'agegroups' => $agegroups]);

    return [
        'title' => 'New Game',
        'entity' => $entity,
        'form' => $form->createView(),
    ];
  }

  /**
   * Creates a new Game entity.
   *
   * @Route("/create", name="game_create")
   * @Method("POST")
   * @Template("SchedulerBundle:Game:new.html.twig")
   * @param Request $request
   * @return array|RedirectResponse
   */
  public function createAction(Request $request) {
    $entity = new Game();
    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);

    $em = $this->getDoctrine()->getManager();
    $teams = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $form = $this->createForm(GameType::class, $entity, ['teams' => $teams, 'agegroups' => $agegroups]);

    $form->handleRequest($request);

    if ($form->isValid()) {
      $user = $this->getUser();
      $entity->setUpdatedBy($user);
      if (empty($entity->getNumber())) {
        $entity->setNumber($em->getRepository('SchedulerBundle:Game')->maxGameNumber($project->getId()) + 1);
      }
      $em->persist($entity);
      $em->flush();
      $em->persist(new LogGame($entity->getId(), "Created by " . $user->getFullName(), $user->getId()));
      $em->flush();

      //return $this->redirect($this->generateUrl('game', ['id' => $entity->getId()]));
      return $this->redirect($this->generateUrl('game'));
    }

    return [
        'title' => 'Create Game',
        'entity' => $entity,
        'form' => $form->createView(),
    ];
  }

  /**
   * Clone one entity to make a new Game entity.
   *
   * @Route("/{id}/clone", name="game_clone")
   * @Template("SchedulerBundle:Game:new.html.twig")
   * @param $id
   * @return array
   */
  public function cloneAction($id) {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }
    // reset id so persist will create a new instance.
    $entity->resetForClone();

    $user = $this->getUser();
    $project = $user->getCurrentproject();
    $entity->setProject($project);

    $teams = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $form = $this->createForm(GameType::class, $entity, ['teams' => $teams, 'agegroups' => $agegroups]);

    return [
        'title' => 'Clone Game',
        'entity' => $entity,
        'form' => $form->createView(),
    ];
  }

  /**
   * Displays a form to edit an existing Game entity.
   *
   * @Route("/{id}/edit", name="game_edit")
   * @Template()
   * @param Request $request
   * @param $id
   * @return array
   */
  public function editAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }

    $user = $this->getUser();
    $project = $user->getCurrentproject();
    if (($entity->getProject() != NULL) && ($project != $entity->getProject())) {
      $user->setCurrentproject($entity->getProject());
      $em->persist($user);
      $project = $entity->getProject();
    }

    $teams = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $editForm = $this->createForm(GameType::class, $entity, ['teams' => $teams, 'agegroups' => $agegroups]);
    $deleteForm = $this->createDeleteForm($id);

    return [
        'title' => 'Edit Game',
        'entity' => $entity,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
    ];
  }

  // FIXME: this needs to be a separate operation
  /**
   * @param User $user
   * @param $msg
   */
  function sendText($num, $msg) {
    if (empty($num) || empty($msg)) {
      return;
    }
    $mytime = microtime(true);
    $testing = $this->container->getParameter('test_email');
    if ($testing) {
      $tropo_key = $this->container->getParameter('tropo_dev_key');
    } else {
      $tropo_key = $this->container->getParameter('tropo_prod_key');
    }
    $TROPO_URL = 'https://api.tropo.com/1.0/sessions?action=create&token=' . $tropo_key;

    //$num = $user->getPhoneMobile();
    $url = $TROPO_URL . "&num=" . urlencode($num) . "&msg=" . urlencode($msg);
    //echo "msg len = ".strlen($msg)."\n";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //echo "Fetching $url\n";
    $xml = curl_exec($curl);
    curl_close($curl);
    // supposed to wait 1 second between text messages.
    // TODO: make text message sending (and email) a separate process
    $mytime = microtime(true) - $mytime;
    if ($mytime < 1.0) {
      usleep((1.0 - $mytime) * 1000000);
    }
  }

  // TODO: this needs to move to a separate bundle or file
  /**
   * @param $emailTo
   * @param $subject
   * @param $msg
   */
  public function sendEmailMessage($emailTo, $subject, $msg) {
    $testing = $this->container->getParameter('test_email');

    if ($testing) {
      $m = '';
      foreach ($emailTo as $email => $name) {
        $m .= "To: $name <$email>\n";
      }
      $msg = $m . "\n" . $msg;
      $emailTo = 'john.price@ayso894.net'; // FIXME: get this from parameters!!!
    }
    // send an email
    $message = \Swift_Message::newInstance()
            ->setSubject('[Sportac.us] ' . $subject)
            ->setFrom(['notification@sportac.us' => 'Sportac.us Scheduling System'])
            ->setCc(['john.price@ayso894.net' => 'John Price']) // FIXME
            ->setTo($emailTo)
            //->setTo('john.price@ayso894.net')
            ->setBody($msg);
    $this->get('mailer')->send($message);
  }

  /**
   * @param Game $old_game
   * @param Game $new_game
   * @return bool
   */
  public function sendGameChangeNotification(Game $old_game, Game $new_game = null) {
    $user = $this->getUser();
    if (
            ($old_game->getPublished() == true) &&
            ($old_game->getStatus() != Game::STATUS_INACTIVE)
    ) {
      $msg = '';
      $subject = 'Game change';
      $textmsg = '';
      if ($new_game == NULL) { // game was deleted
        $msg .= "- The game has been deleted.\r\n";
        $subject = 'Game deleted';
      } else {
        // if went from published to unpublished, just report that and not other stuff
        if ($new_game->getPublished() == false) {
          $msg .= "- The game has been unpublished.\r\n";
          $subject = 'Game unpublished';
        } else {
          if ($old_game->getDate()->format('Ymd') != $new_game->getDate()->format('Ymd')) {
            $msg .= "- The date changed from " . $old_game->getDate()->format('l, F j') . " to " . $new_game->getDate()->format('l, F j') . ".\r\n";
            $textmsg .= ', date ' . $new_game->getDate()->format('M j');
          }
          if ($old_game->getTime()->format('Hi') != $new_game->getTime()->format('Hi')) {
            $msg .= "- The time changed from " . $old_game->getTime()->format('g:i A') . " to " . $new_game->getTime()->format('g:i A') . ".\r\n";
            $textmsg .= ', time ' . $new_game->getTime()->format('g:i A');
          }
          if ($old_game->getLocation() != $new_game->getLocation()) {
            $msg .= "- The location changed from " . $old_game->getLocation() . " to " . $new_game->getLocation() . ".\r\n";
            $textmsg .= ', location ' . $new_game->getLocation();
          }
          if ($old_game->getTeam1() != $new_game->getTeam1()) {
            $msg .= "- The home team changed from " . $old_game->getTeam1() . " to " . $new_game->getTeam1() . ".\r\n";
            $textmsg .= ', home team ' . $new_game->getTeam1();
          }
          if ($old_game->getTeam2() != $new_game->getTeam2()) {
            $msg .= "- The away team changed from " . $old_game->getTeam2() . " to " . $new_game->getTeam2() . ".\r\n";
            $textmsg .= ', away team ' . $new_game->getTeam2();
          }
          if ($old_game->getStatus() != $new_game->getStatus()) {
            $msg .= "- The game status changed from " . $old_game->getStatusString() . " to " . $new_game->getStatusString() . ".\r\n";
            switch ($new_game->getStatus()) {
              case Game::STATUS_CANCELLED:
                $subject = 'Game cancelled';
                break;
              case Game::STATUS_RAINOUT:
                $subject = 'Game rained out';
                break;
              case Game::STATUS_SUSPENDED:
                $subject = 'Game suspended';
                break;
              case Game::STATUS_FORFEIT:
                $subject = 'Game forfeited';
                break;
              case Game::STATUS_POSTPONED:
                $subject = 'Game postponed';
                break;
            }
          }
        }
      }
      //echo "msg=$msg<br>\n";
      if (!empty($msg)) {
        $em = $this->getDoctrine()->getManager();
        $em->persist(new LogGame($old_game->getId(), $msg, $user->getId()));
        //$em->flush();
        $body = $this->renderView('SchedulerBundle:Game:scheduleChangeEmail.txt.twig', [
            'msg' => $msg,
            'oldgame' => $old_game,
            'newgame' => $new_game,
            'user' => $user,
                ]
        );
        if ($new_game == NULL) {
          $textmsg = "Sportac.us Game {$old_game->getNumber()}: $subject$textmsg";
        } else {
          $textmsg = "Sportac.us Game {$old_game->getNumber()}: $subject$textmsg - http://sportac.us/game/{$old_game->getId()}/show for details";
        }
        $to = []; // array of email addresses
        $texts = [];

        $games = [$old_game];
        if (!empty($new_game))
          $games[] = $new_game;

        foreach ($games as $game) {
          /** @var Game $game */
          // notify referees
          foreach ($game->getOfficials() as $official) {
            /** @var User $official */
            if (!empty($official) && !array_key_exists($official->getEmail(), $to)) {
              $email = $official->getEmail();
              $mobile = $official->getPhoneMobile();
              if ($official->getOptionChangeEmail() && !array_key_exists($email, $to)) {
                $to[$email] = $official->getFirstName() . ' ' . $official->getLastName();
              }
              if ($official->getOptionChangeText() && !array_key_exists($mobile, $texts)) {
                $texts[$mobile] = $mobile;
                $this->sendText($mobile, $textmsg);
              }
            }
          }
          foreach ($game->getTeams() as $team) {
            /** @var Team $team */
            if (!empty($team)) {
              // notify coaches
              $coach = $team->getCoach();
              $coach_email = $team->getCoachEmail();
              $coach_phone = $team->getCoachPhone();
              if (!empty($coach_email) && !array_key_exists($coach_email, $to)) {
                $to[$coach_email] = $coach;
              }
              // send text to coach
              if (!empty($coach_phone) && !array_key_exists($coach_phone, $texts)) {
                $texts[$coach_phone] = $coach_phone;
                $this->sendText($coach_phone, $textmsg);
              }
              $poc_email = $team->getPocEmail();
              if (!empty($poc_email) && !array_key_exists($poc_email, $to)) {
                $to[$poc_email] = null;
              }
              // notify Team Region POC (if it exists)
              $region = $team->getRegion();
              if (!empty($region)) {
                $region_name = $region->getPocName();
                $region_email = $region->getPocEmail();
                if (!empty($region_email) && !array_key_exists($region_email, $to)) {
                  $to[$region_email] = $region_name;
                }
                $refadm_name = $region->getRefAdminName();
                $refadm_email = $region->getRefAdminEmail();
                if (!empty($refadm_email) && !array_key_exists($refadm_email, $to)) {
                  $to[$refadm_email] = $refadm_name;
                }
              }
            }
          }
          // notify Game Region POC (if it exists)
          $region = $game->getRegion();
          if (!empty($region)) {
            $region_name = $region->getPocName();
            $region_email = $region->getPocEmail();
            if (!empty($region_email) && !array_key_exists($region_email, $to)) {
              $to[$region_email] = $region_name;
            }
          }
        }
        $this->sendEmailMessage($to, $subject, $body);
      }
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Edits an existing Game entity.
   *
   * @Route("/{id}/update", name="game_update")
   * @Method("POST")
   * @Template("SchedulerBundle:Game:edit.html.twig")
   * @param Request $request
   * @param $id
   * @return array|RedirectResponse
   */
  public function updateAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();

    /** @var Game $entity */
    $entity = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }

    /** @var Game $orig */
    $orig = clone $entity;
    $deleteForm = $this->createDeleteForm($id);
    $user = $this->getUser();
    $project = $user->getCurrentproject();

    /** @var array|Team $teams */
    $teams = $em->getRepository('SchedulerBundle:Team')->findAllByProject($project);
    /** @var array|AgeGroup $agegroups */
    $agegroups = $em->getRepository('SchedulerBundle:AgeGroup')->findAllByProject($project);

    $editForm = $this->createForm(GameType::class, $entity, ['teams' => $teams, 'agegroups' => $agegroups]);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $user = $this->getUser();
      $entity->setUpdatedBy($user);
      if (!$this->sendGameChangeNotification($orig, $entity)) {
        $em->persist(new LogGame($entity->getId(), "Changed by " . $user->getFullName(), $user->getId()));
      }
      $em->persist($entity);
      $em->flush();
      //return $this->redirectToRoute('game', ['id' => $id]); // game_edt ???
      return $this->redirect($this->generateUrl('game'));
    }

    return [
        'title' => 'Edit Game',
        'entity' => $entity,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Assign referees to Game entity.
   *
   * @Route("/assign/{id}", name="game_assign")
   * @Template("SchedulerBundle:Game:assign.html.twig")
   * @param Request $request
   * @param $id
   * @return array|RedirectResponse
   */
  public function assignAction(Request $request, $id) {
    $user = $this->getUser();
    $em = $this->getDoctrine()->getManager();
    $refs = [];

    /** @var Game $game */
    $game = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$game) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }
    $gameOriginal = clone $game;

    $form = $this->createForm(GameAssignType::class, $game, ['user' => $user, 'security' => $this->get('security.authorization_checker')]);
    $form->handleRequest($request);

    $allow_any_assignment = ($this->isGranted('ROLE_REF_ADMIN') || $this->isGranted('ROLE_ASSIGNER'));
    $is_referee = $user->getRoleReferee();

    if ($form->isSubmitted() && $form->isValid()) {
      // if user is now allowed to change anything, send them back to the schedule without changing anything
      if (!$is_referee && !$allow_any_assignment) {
        $this->get('session')->getFlashBag()->add('contact-error', 'Sorry. You don\'t have access to change referee assignments.');
        return $this->redirect($this->generateUrl('official_schedule'));
      }
      //$user = $this->getUser();
      $game->setUpdatedBy($user);
      //$positions = ['CR','AR1','AR2'];

      $offpositions = $game->getProject()->getOffpositions()->toArray();
      $difficulty = $game->getAgegroup()->getDifficulty();
      $positions = [];

      foreach ($offpositions as $index => $pos) {
        /** @var OffPos $pos */
        if ($difficulty >= $pos->getDiffavail()) {
          $positions[] = $pos->getName();
        }
      }
      $officials = $game->getOfficials();
      $officialsOriginal = $gameOriginal->getOfficials();

      $note = $form->get('assignment_change_note')->getData();
      //unused $email_msg = '';
      $email_ref = [];
      $email_msgs = [];
      $email_assigner = [];
      //unused $text_ref = [];
      $text_assigner = [];
      $UserRepo = $em->getRepository('SchedulerBundle:User');
      $assigners = $UserRepo->findBy(['role_assigner' => true, 'option_assigner_email' => true]);

      // see what ref assignments changed
      foreach ($officials as $index => $ref) {
        /** @var User $ref */
        /** @var User $old_ref */
        $old_ref = $officialsOriginal[$index]; //echo "{$ref} {$old_ref} <br>";
        $refs[] = ($ref) ? ($ref->getFullName()) : '';
        // for each change, notify if required
        if ($old_ref != $ref) {
          $old_name = ($old_ref) ? ($old_ref->getFullName()) : 'Unassigned';
          $new_name = ($ref) ? ($ref->getFullName()) : 'Unassigned';
          $msg = "Official assignment from $old_name to $new_name changed by " . $user->getFullName();
          $em->persist(new LogGame($game->getId(), $msg, $user->getId()));

          if ($old_ref) { // if was assigned before
            if ($old_ref == $user) {
              // user unassigned themselves
              //$email_ref[$old_ref->getId()] = $old_ref;
              //$email_msgs[$old_ref->getId()][] = "You unassigned yourself from position ".$positions[$index];
              $email_assigner[] = "Unassigned from position " . $positions[$index];
              $text_assigner[] = "Unassign " . $positions[$index];
            } else {
              // someone else unassigned a referee
              $email_ref[$old_ref->getId()] = $old_ref;
              $email_msgs[$old_ref->getId()][] = $user->getFullName() . " unassigned you from position " . $positions[$index];
              $email_assigner[] = "Unassigned " . $old_ref->getFullName() . " from position " . $positions[$index];
            }
          }
          if ($ref) {
            $text_assigner[] = "Assign " . $positions[$index];
            if ($ref == $user) {
              // user assigned themselves
              //$email_ref[$ref->getId()] = $ref;
              //$email_msgs[$ref->getId()][] = "You assigned yourself to position ".$positions[$index];
              $email_assigner[] = "Assigned to position " . $positions[$index];
              $text_assigner[] = "Unassign " . $positions[$index];
            } else {
              // someone else assigned a referee
              $email_ref[$ref->getId()] = $ref;
              $email_msgs[$ref->getId()][] = $user->getFullName() . " assigned you to position " . $positions[$index];
              $email_assigner[] = "Assigned " . $ref->getFullName() . " to position " . $positions[$index];
            }
          }
        }
      }
      if ($game->getPublished() && ($game->getStatus() != Game::STATUS_INACTIVE)) {
        if (count($email_ref)) {
          foreach ($email_ref as $refid => $ref) {
            if ($ref->getOptionAssignmentEmail()) {
              //$email_msg = "The following official assignments changed for Game ".$game->getNumber().":\n\n";
              $email_msg = '';
              foreach ($email_msgs[$refid] as $msg) {
                $email_msg .= "- $msg\n";
              }
              //if (!empty($note)) {
              //  $email_msg .= "\nThe following information was provided for this assignment change:\n$note\n";
              //}
              // create message body
              $body = $this->renderView('SchedulerBundle:Game:assignmentChangeEmail.txt.twig', [
                  'msg' => $email_msg,
                  'oldgame' => $game,
                  'note' => $note,
                  'user' => $ref,
                  'positions' => $positions,
                  'refs' => $refs,
                      ]
              );

              $this->sendEmailMessage([$ref->getEmail() => $ref->getFullName()], 'Assignment Change Game ' . $game->getNumber(), $body);
            }
          }
        }
        if (count($assigners)) {
          if (count($email_assigner)) {
            /** @var array|string $to */
            $to = [];
            foreach ($assigners as $assignor) {
              /** @var User $assignor */
              $to[$assignor->getEmail()] = $assignor->getFullName();
            }
            $email_msg = '';
            foreach ($email_assigner as $msg) {
              $email_msg .= "- $msg\n";
            }

            // create message body
            $body = $this->renderView('SchedulerBundle:Game:assignmentChangeAssignerEmail.txt.twig', [
                'msg' => $email_msg,
                'oldgame' => $game,
                'note' => $note,
                'user' => $user,
                'positions' => $positions,
                'refs' => $refs,
                    ]
            );

            $this->sendEmailMessage($to, 'Assignment Change Game ' . $game->getNumber(), $body);
          }
        }
      }

      $em->persist($game);
      $em->flush();

      return $this->redirect($this->generateUrl('official_schedule'));
    }

    return [
        'title' => 'Assign Referees to Game',
        'user' => $user,
        'game' => $game,
        'edit_form' => $form->createView(),
    ];
  }

  /**
   * Update scorecard for Game entity.
   *
   * @Route("/scorecard/{id}", name="game_scorecard")
   * @Template("SchedulerBundle:Game:scorecard.html.twig")
   * @param Request $request
   * @param $id
   * @return array|RedirectResponse
   */
  public function scorecardAction(Request $request, $id) {
    $user = $this->getUser();
    $em = $this->getDoctrine()->getManager();

    /* @var $game Scheduler\SchBundle\Entity\Game */
    $game = $em->getRepository('SchedulerBundle:Game')->find($id);

    if (!$game) {
      throw $this->createNotFoundException('Unable to find Game entity.');
    }
    $orig = clone $game;

    // only allowed to change scorecard if this is the CR, and it is published and normal.
    $readOnly = !($game->getPublished() && ($game->getStatus() == Game::STATUS_NORMAL) && ($game->getRef1() == $user));
    if ($this->isGranted('ROLE_REF_ADMIN')) {
      $readOnly = false;
		}
    $form = $this->createForm(GameScorecardType::class, $game, ['readonly' => $readOnly]);
    if ($request->isMethod('POST')) {
      if (!$readOnly) {
        //  $this->get('session')->getFlashBag()->add('contact-error', 'Scorecard is read only if status is not Normal');
        //} else {
        $form->handleRequest($request);

        if ($form->isValid()) {
          //$user = $this->getUser();
          $alert_admin = $form->get('alert_admin')->getData();
          $msg = $form->get('ref_notes')->getData();
          if ($alert_admin && ($alert_admin != $orig->getAlertAdmin())) {
            // if checkbox is checked now and not before, send an email to the region admins of both regions involved
            $teams = $game->getTeams();
            $to = [];
            foreach ($teams as $team) {
              if ($team && $team->getRegion()) {
                $region = $team->getRegion();
                if ($region->getPocEmail()) {
                  $to[$region->getPocEmail()] = $region->getPocName();
                }
                if ($region->getRefAdminEmail()) {
                  $to[$region->getRefAdminEmail()] = $region->getRefAdminName();
                }
              }
            }
            $offpositions = $game->getProject()->getOffpositions()->toArray();
            $difficulty = $game->getAgegroup()->getDifficulty();
            $positions = [];

            foreach ($offpositions as $index => $pos) {
              /** @var OffPos $pos */
              if ($difficulty >= $pos->getDiffavail()) {
                $positions[] = $pos->getName();
              }
            }
            $refs = $game->getOfficials();

            // create message body
            $body = $this->renderView('SchedulerBundle:Game:adminAlertEmail.txt.twig', [
                'msg' => $msg,
                'game' => $game,
                'user' => $user,
                'positions' => $positions,
                'refs' => $refs,
                    ]
            );
            $this->sendEmailMessage($to, 'Referee Admin Alert for Game ' . $game->getNumber(), $body);
          }
          $game->setUpdatedBy($user);
          $em->persist(new LogGame($game->getId(), "Official record changed by " . $user->getFullName(), $user->getId()));
          $em->persist($game);
          $em->flush();

          //return $this->redirect($this->generateUrl('official_schedule', ['id' => $id]));
          return $this->redirect($this->generateUrl('official_schedule'));
        }
      }
    }

    return [
        'title' => 'Scorecard for Game',
        'user' => $user,
        'game' => $game,
        'readonly' => $readOnly,
        'edit_form' => $form->createView(),
    ];
  }

  /**
   * Import Games
   *
   * @Route("/import", name="game_import")
   * @Template("SchedulerBundle:Game:import.html.twig")
   * @param Request $request
   * @param $id
   * @return array|RedirectResponse
   */
  public function importAction(Request $request) {
    $user = $this->getUser();
    //$em = $this->getDoctrine()->getManager();
    $project = $user->getCurrentproject();
    $result = [];

    $form = $this->createForm(GameImportType::class);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isValid()) {
        // Create a variable data directory based on where AppKernel lives
        $binDir = $this->get('kernel')->getRootDir() . '/../bin';

        $dataDir = $this->container->getParameter('kernel.cache_dir') . '/uploads';

        if (!file_exists($dataDir)) {
          @mkdir($dataDir, 0777, true);
        }
        // FIXME: randomize filename after this is all working
        $file = $form['attachment']->getData();
        $dryrun = $form['dryrun']->getData();
        $inputFN = $file->getClientOriginalName();
        $file->move($dataDir, $inputFN);

        $cmd = $binDir . '/console cerad_project_game_import -p' . $project->getId();
        if (!$dryrun) {
          $cmd .= ' -u';
        }
        $cmd .= ' ' . escapeshellarg($dataDir . '/' . $inputFN);
        $result[] = 'Command: ' . $cmd;
        exec($cmd, $output, $return_var);
        foreach ($output as &$line) {
          $a = strpos($line, ' ');
          $color = '';
          if ($a > 0) {
            if (substr($line, 0, 4) == 'ERR_') {
              $color = 'red';
            } else if (substr($line, 0, $a) == 'ERR_DAT') {
              $color = 'blue';
            }
            if ($color != '') {
              $line = '<span style="color:' . $color . '">' . $line . '</span>';
            }
          }
        }
        //exec('set', $output, $return_var);
        $result[] = "Command return code was $return_var";
        $result[] = '';
        $result[] = 'Command output follows:';
        $result = array_merge($result, $output);
      }
    } else {
      $result = [
          "Required columns (case-insensitive):",
          "'Date' - date of game in format YYYY-MM-DD",
          "'Time' - time of game in format HH:MM",
          "'Region' - region short name (i.e. R0894)",
          "'Division' - division (i.e. U08)",
          "'Location' - location short name (i.e. JH4)",
          "'Home' - home team name",
          "'Away' - away team name",
          "",
          "Optional columns:",
          "'TSLength' - time slot length (minutes)",
          "'Length' - game length (minutes)",
          "'Short Note' - short note",
      ];
    }

    return [
        'title' => 'Game Import',
        'user' => $user,
        'output' => $result,
        'edit_form' => $form->createView(),
    ];
  }

  /**
   * Deletes a Game entity.
   *
   * @Route("/{id}/delete", name="game_delete")
   * @Method("POST")
   * @param Request $request
   * @param $id
   * @return RedirectResponse
   */
  public function deleteAction(Request $request, $id) {
    $form = $this->createDeleteForm($id);
    $form->handleRequest($request);

    if ($form->isValid() && $this->isGranted('ROLE_REF_ADMIN')) {
      $em = $this->getDoctrine()->getManager();
      /** @var Game $entity */
      $entity = $em->getRepository('SchedulerBundle:Game')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Game entity.');
      }
      if (!$this->sendGameChangeNotification($entity, NULL)) {
        $user = $this->getUser();
        $em->persist(new LogGame($entity->getId(), "Deleted by " . $user->getFullName(), $user->getId()));
      }
      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('game'));
  }

  private function createDeleteForm($id) {
    return $this->createFormBuilder(['id' => $id])
                    ->add('id', HiddenType::class)
                    ->getForm();
  }

  /**
   * Exports entities.
   *
   * @Route("/export.csv", name="game_export_csv")
   * @Template("SchedulerBundle:Games:export.csv.twig")
   */
  public function exportCsvAction() {
    $session = $this->get('session');
    $criteria = new GameListCriteria();
    if ($session->has('game_search_criteria')) {
      $criteria->setCriteria($session->get('game_search_criteria'));
    }

    $onlyPublished = $this->isGranted('ROLE_SCHEDULER') ? false : true;

    $em = $this->getDoctrine()->getManager();
    $games = $em->getRepository('SchedulerBundle:Game')->findByCriteria($onlyPublished, $criteria);

    //$repository = $this->getDoctrine()->getRepository('SchedulerBundle:Game');
    //$query = $repository->createQueryBuilder('s')
    //  ->orderBy('s.date', 'ASC')
    //  ->addOrderBy('s.time', 'ASC');
    //$games = $query->getQuery()->getResult();
    $filename = "games-" . date("Ymd_His") . ".csv";

    $response = $this->render('SchedulerBundle:Game:export.csv.twig', ['data' => $games]);
    $response->headers->set('Content-Type', 'text/csv');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    return $response;
  }

}
