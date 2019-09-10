<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\User;
use Scheduler\SchBundle\Entity\Game;
use Scheduler\SchBundle\Entity\GameListCriteria;

/**
 * User iCalendar controller.
 *
 * @Route("/ical")
 */
class UserCalendarController extends Controller
{
  /**
   * Finds games a user is scheduled for.
   *
   * @Route("/user/{id}", name="user_ical")
   */
  public function userCalendarAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    if ($id != 0) {
      $user = $em->getRepository('SchedulerBundle:User')->find($id);

      if (!$user) {
        throw $this->createNotFoundException('Unable to find User entity.');
      }
      $games = $em->getRepository('SchedulerBundle:Game')->findAllActiveByOffcial($id);
    } else {
      $games = $em->getRepository('SchedulerBundle:Game')->findAllOrderedByDate(false);
    }

    $filename = 'official-calendar.ics';
    $title = $user->getFullName() . ' - Sportac.us';
    $response = $this->render('SchedulerBundle:User:calendar.ics.twig',
      array(
        'games' => $games,
        'title' => $title,
      )
    );
    if ($request->query->get('debug')) {
      echo nl2br($response->getContent());
      die();
    }
    $response->headers->set('Content-Type', 'text/calendar');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

    return $response;
  }

  /**
   * Finds games a user is scheduled for.
   *
   * @Route("/location/{id}", name="location_ical")
   */
  public function locationCalendarAction($id)
  {
    $criteria = new GameListCriteria();

    $em = $this->getDoctrine()->getManager();
    $location = $em->getRepository('SchedulerBundle:Location')->find($id);

    if (!$location) {
      throw $this->createNotFoundException('Unable to find Location entity.');
    }
    $criteria->setLocation($location);
    $d = new \DateTime();
    $d->sub(new \DateInterval('P1Y'));
    $criteria->setStartDate($d);
    $d = new \DateTime();
    $d->add(new \DateInterval('P1Y'));
    $criteria->setEndDate($d);

    $games = $em->getRepository('SchedulerBundle:Game')->findByCriteria(true, $criteria);

    $filename = 'location-calendar.ics';
    $title = $location->getName() . ' - Sportac.us';
    $response = $this->render('SchedulerBundle:User:calendar.ics.twig',
      array(
        'games' => $games,
        'title' => $title,
      )
    );
    $response->headers->set('Content-Type', 'text/calendar');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

    return $response;
  }

  /**
   * Finds games a team is scheduled for.
   *
   * @Route("/team/{id}", name="team_ical")
   */
  public function teamCalendarAction($id)
  {
    $criteria = new GameListCriteria();

    $em = $this->getDoctrine()->getManager();
    $team = $em->getRepository('SchedulerBundle:Team')->find($id);

    if (!$team) {
      throw $this->createNotFoundException('Unable to find Team entity.');
    }
    $criteria->setTeam($team);
    $d = new \DateTime();
    $d->sub(new \DateInterval('P1Y'));
    $criteria->setStartDate($d);
    $d = new \DateTime();
    $d->add(new \DateInterval('P1Y'));
    $criteria->setEndDate($d);

    $games = $em->getRepository('SchedulerBundle:Game')->findByCriteria(true, $criteria);

    $filename = 'team-calendar.ics';
    $title = $team->getName() . ' - Sportac.us';
    $response = $this->render('SchedulerBundle:User:calendar.ics.twig',
      array(
        'games' => $games,
        'title' => $title,
      )
    );
    $response->headers->set('Content-Type', 'text/calendar');

    $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

    return $response;
  }
}
