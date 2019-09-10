<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GameListCriteria
 *
 * #ORM\Table()
 * #ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\GameListCriteriaRepository")
 */
class GameListCriteria
{
  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="start_date", type="date")
   * @Assert\Date(message="Start date is not valid.")
   */
  private $start_date;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="start_time", type="time")
   * @Assert\Time(message="Start time is not valid.")
   */
  private $start_time;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="end_date", type="date")
   * @Assert\Date(message="End date is not valid.")
   */
  private $end_date;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="end_time", type="time")
   * @Assert\Time(message="End time is not valid.")
   */
  private $end_time;

  /**
   * @var string
   *
   * @ORM\Column(name="team", type="string", length=255)
   */
  private $team;

  /**
   * @var string
   *
   * @ORM\Column(name="official", type="string", length=255)
   */
  private $official;

  /**
   * @var string
   *
   * @ORM\Column(name="coach", type="string", length=255)
   */
  private $coach;

  /**
   * @var string
   *
   * @ORM\Column(name="location", type="string", length=255)
   */
  private $location;

  /**
   * @var string
   *
   * @ORM\Column(name="agegroup", type="string", length=255)
   */
  private $agegroup;

  /**
   * @var string
   *
   * @ORM\ManyToOne(targetEntity="Region")
   * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
   */
  private $region;

  /**
   * @var integer $project
   *
   * @ORM\ManyToOne(targetEntity="Project")
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
   */
  private $project;

  private $checkForConflicts = true;
  public function getCheckForConflicts() { return $this->checkForConflicts; }
  public function setCheckForConflicts($value) { $this->checkForConflicts = $value; return $this;}

  public function __construct()
  {
    $this->setStartDate(new \DateTime());
    $this->setStartTime(new \DateTime('00:00'));
    // default end time is one week from today
    $enddate = new \DateTime();
    $enddate->add(new \DateInterval('P7D')); // add 7 days
    $this->setEndDate($enddate);
    $this->setEndTime(new \DateTime('23:59'));
  }

  public function getCriteria()
  {
    $c = array();
    $c['startdate'] = $this->getStartDate()->format('Ymd');
    $c['starttime'] = $this->getStartTime()->format('Hi');
    $c['enddate'] = $this->getEndDate()->format('Ymd');
    $c['endtime'] = $this->getEndTime()->format('Hi');
    $c['team'] = $this->team;
    $c['official'] = $this->official;
    $c['coach'] = $this->coach;
    $c['location'] = $this->location;
    $c['agegroup'] = $this->agegroup;
    $c['region'] = $this->region;
    $c['project'] = $this->project;
    $c['checkForConflicts'] = $this->checkForConflicts;
    return $c;
  }

  public function setCriteria($c)
  {
    try {
      if (!is_array($c))
        return false;
      $sdate = new \DateTime($c['startdate']);
      $stime = new \DateTime($c['starttime']);
      $edate = new \DateTime($c['enddate']);
      $etime = new \DateTime($c['endtime']);
      $team = $c['team'];
      $official = $c['official'];
      $coach = $c['coach'];
      $location = $c['location'];
      $agegroup = $c['agegroup'];
      $region = $c['region'];
      $project = $c['project'];

      $this->setStartDate($sdate);
      $this->setStartTime($stime);
      $this->setEndDate($edate);
      $this->setEndTime($etime);
      $this->setTeam($team);
      $this->setOfficial($official);
      $this->setCoach($coach);
      $this->setLocation($location);
      $this->setAgegroup($agegroup);
      $this->setRegion($region);
      $this->setProject($project);

      if (isset($c['checkForConflicts'])) {
        $this->checkForConflicts = $c['checkForConflicts'] ? true : false;
      }
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  public function setCriteriaIfNotBlank($c)
  {
    try {
      if (!is_array($c))
        return false;
      $sdate = new \DateTime($c['startdate']);
      $stime = new \DateTime($c['starttime']);
      $edate = new \DateTime($c['enddate']);
      $etime = new \DateTime($c['endtime']);
      $team = $c['team'];
      $official = $c['official'];
      $coach = $c['coach'];
      $location = $c['location'];
      $agegroup = $c['agegroup'];
      $region = $c['region'];
      $project = $c['project'];

      if (!empty($sdate))
        $this->setStartDate($sdate);
      if (!empty($stime))
        $this->setStartTime($stime);
      if (!empty($edate))
        $this->setEndDate($edate);
      if (!empty($etime))
        $this->setEndTime($etime);
      if (!empty($team))
        $this->setTeam($team);
      if (!empty($official))
        $this->setOfficial($official);
      if (!empty($coach))
        $this->setCoach($coach);
      if (!empty($location))
        $this->setLocation($location);
      if (!empty($agegroup))
        $this->setAgegroup($agegroup);
      if (!empty($region))
        $this->setRegion($region);
      if (!empty($project))
        $this->setProject($project);

      if (isset($c['checkForConflicts'])) {
        $this->checkForConflicts = $c['checkForConflicts'] ? true : false;
      }
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  /**
   * Get id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set start_date
   *
   * @param \DateTime $startDate
   * @return GameListCriteria
   */
  public function setStartDate($startDate)
  {
    if (empty($startDate))
      $this->start_date = new \DateTime();
    else
      $this->start_date = $startDate;

    return $this;
  }

  /**
   * Get start_date
   *
   * @return \DateTime
   */
  public function getStartDate()
  {
    return $this->start_date;
  }

  /**
   * Set start_time
   *
   * @param \DateTime $startTime
   * @return GameListCriteria
   */
  public function setStartTime($startTime)
  {
    if (empty($startTime))
      $this->start_time = new \DateTime('00:00');
    else
      $this->start_time = $startTime;

    return $this;
  }

  /**
   * Get start_time
   *
   * @return \DateTime
   */
  public function getStartTime()
  {
    return $this->start_time;
  }

  /**
   * Set end_date
   *
   * @param \DateTime $endDate
   * @return GameListCriteria
   */
  public function setEndDate($endDate)
  {
    if (empty($endDate)) {
      $this->end_date = new \DateTime();
      $this->end_date->add(new \DateInterval('P14D')); // add 14 days
    } else
      $this->end_date = $endDate;

    return $this;
  }

  /**
   * Get end_date
   *
   * @return \DateTime
   */
  public function getEndDate()
  {
    return $this->end_date;
  }

  /**
   * Set end_time
   *
   * @param \DateTime $endTime
   * @return GameListCriteria
   */
  public function setEndTime($endTime)
  {
    if (empty($endTime))
      $this->end_time = new \DateTime('23:59');
    else
      $this->end_time = $endTime;

    return $this;
  }

  /**
   * Get end_time
   *
   * @return \DateTime
   */
  public function getEndTime()
  {
    return $this->end_time;
  }

  /**
   * Set team
   *
   * @param string $team
   * @return GameListCriteria
   */
  public function setTeam($team)
  {
    $this->team = $team;

    return $this;
  }

  /**
   * Get team
   *
   * @return string
   */
  public function getTeam()
  {
    return $this->team;
  }

  /**
   * Set official
   *
   * @param string $official
   * @return GameListCriteria
   */
  public function setOfficial($official)
  {
    $this->official = $official;

    return $this;
  }

  /**
   * Get official
   *
   * @return string
   */
  public function getOfficial()
  {
    return $this->official;
  }

  /**
   * Set coach
   *
   * @param string $coach
   * @return GameListCriteria
   */
  public function setCoach($coach)
  {
    $this->coach = $coach;

    return $this;
  }

  /**
   * Get coach
   *
   * @return string
   */
  public function getCoach()
  {
    return $this->coach;
  }

  /**
   * Set location
   *
   * @param string $location
   * @return GameListCriteria
   */
  public function setLocation($location)
  {
    $this->location = $location;

    return $this;
  }

  /**
   * Get location
   *
   * @return string
   */
  public function getLocation()
  {
    return $this->location;
  }

  /**
   * Set agegroup
   *
   * @param string $agegroup
   * @return GameListCriteria
   */
  public function setAgegroup($agegroup)
  {
    $this->agegroup = $agegroup;

    return $this;
  }

  /**
   * Get agegroup
   *
   * @return string
   */
  public function getAgegroup()
  {
    return $this->agegroup;
  }

  /**
   * Set region
   *
   * @param \Scheduler\SchBundle\Entity\Region $region
   * @return GameListCriteria
   */
  public function setRegion(\Scheduler\SchBundle\Entity\Region $region = null)
  {
    $this->region = $region;

    return $this;
  }

  /**
   * Get region
   *
   * @return \Scheduler\SchBundle\Entity\Region
   */
  public function getRegion()
  {
    return $this->region;
  }

  /**
   * Set project
   *
   * @param \Scheduler\SchBundle\Entity\Project $project
   * @return GameListCriteria
   */
  public function setProject(\Scheduler\SchBundle\Entity\Project $project = null)
  {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project
   *
   * @return \Scheduler\SchBundle\Entity\Project
   */
  public function getProject()
  {
    return $this->project;
  }
}
