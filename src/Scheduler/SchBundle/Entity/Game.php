<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Cerad\Bundle\ProjectBundle\Entity\ProjectGameTeam;
use Cerad\Bundle\ProjectBundle\Entity\ProjectGameOfficial;

/**
 * Scheduler\SchBundle\Entity\Game
 *
 * @ORM\Table()
 *  ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\GameRepository")
 * @ORM\Entity(repositoryClass="Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Game implements \ArrayAccess {

  //use ProjectGameTrait;

  const STATUS_INACTIVE = 0;
  const STATUS_NORMAL = 1;
  const STATUS_COMPLETE = 2;
  const STATUS_CANCELLED = 3;
  const STATUS_SUSPENDED = 4;
  const STATUS_RAINOUT = 5;
  const STATUS_FORFEIT = 6;
  const STATUS_POSTPONED = 7;

  /**
   * @var integer $id
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \DateTime $date
   *
   * @ORM\Column(name="date", type="date")
   */
  private $date;

  /**
   * @var \DateTime $time
   *
   * @ORM\Column(name="time", type="time")
   */
  private $time;

  /**
   * @var integer $length
   *
   * @ORM\Column(name="length", type="integer", nullable=true)
   */
  private $length;

  /**
   * @var integer $timeslotlength
   *
   * @ORM\Column(name="timeslotlength", type="integer", nullable=true)
   */
  private $timeslotlength;

  /**
   * @var integer $agegroup
   *
   * @ORM\ManyToOne(targetEntity="AgeGroup")
   * */
  private $agegroup;

  /**
   * @var integer $location
   *
   * @ORM\ManyToOne(targetEntity="Location")
   * */
  private $location;

  /**
   * @var string $region
   *
   * @ORM\ManyToOne(targetEntity="Region")
   * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
   */
  private $region;

  /**
   * @var string project_id
   *
   * @ORM\Column(name="project_id", type="integer", nullable=true)
   */
  private $project_id;

  /**
   * @var integer project
   *
   * @ORM\ManyToOne(targetEntity="Project")
   * @ORM\JoinColumn(name="season_id", referencedColumnName="id")
   */
  private $project;

  /**
   * @var integer $status
   *
   * @ORM\Column(name="status", type="integer")
   */
  private $status;

  /**
   * @var string $short_note
   *
   * @ORM\Column(name="short_note", type="string", length=128, nullable=true)
   */
  private $short_note;

  /**
   * @var integer $ref_notes
   *
   * @ORM\Column(name="ref_notes", type="text", nullable=true)
   */
  private $ref_notes;

  /**
   * @var boolean $alert_admin
   *
   * @ORM\Column(name="alert_admin", type="boolean", options={"default" : 0})
   */
  private $alert_admin;

  /**
   * @var boolean $published ;
   *
   * @ORM\Column(name="published", type="boolean")
   */
  private $published;

  /**
   * @var \DateTime $created
   *
   * @ORM\Column(name="created", type="datetime", nullable=true)
   */
  private $created;

  /**
   * @var \DateTime $updated
   *
   * @ORM\Column(name="updated", type="datetime", nullable=true)
   */
  private $updated;

  /**
   * @var integer $update_count
   *
   * @ORM\Column(name="update_count", type="integer", options={"default" : 0})
   */
  private $update_count;

  /**
   * @var string $updated_by
   *
   * @ORM\ManyToOne(targetEntity="User")
   * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id")
   */
  private $updated_by;
  // non-persistant variables
  private $game_conflicts;
  private $team1_conflicts;
  private $team2_conflicts;
  private $ref1_conflicts;
  private $ref2_conflicts;
  private $ref3_conflicts;
  private $ref4_conflicts;
  private $ref5_conflicts;

  public function isLocked() {
    return $this->status == Game::STATUS_COMPLETE;
  }

  /**
   * Get id
   *
   * @return integer
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Updates "updated" value everytime this entity gets flushed
   *
   * @ORM\PreUpdate
   */
  public function setUpdatedValue() {
    $this->setUpdated(new \DateTime());
    ++$this->update_count;
  }

  /**
   * Set date
   *
   * @param \DateTime $date
   * @return Game
   */
  public function setDate($date) {
    // FIXME: this is an experiment to get import to work.
    if ($date instanceOf \DateTime) {
      $this->date = $date;
    } else {
      $this->date = new \DateTime($date);
    }

    return $this;
  }

  /**
   * Get date
   *
   * @return \DateTime
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Set time
   *
   * @param \DateTime $time
   * @return Game
   */
  public function setTime($time) {
    // FIXME: this is an experiment to get import to work.
    if ($time instanceOf \DateTime) {
      $this->time = $time;
    } else {
      $this->time = new \DateTime($time);
    }

    return $this;
  }

  /**
   * Get time
   *
   * @return \DateTime
   */
  public function getTime() {
    return $this->time;
  }

  /**
   * Get end time
   *
   * @param bool $subtract_second
   * @return \DateTime
   */
  public function getEndTime($subtract_second = false) {
    $t = clone $this->time;
    $len = $this->timeslotlength;
    if (empty($len)) {
      $len = $this->length;
    }
    if (!empty($len)) {
      $t->add(new \DateInterval('PT' . $len . 'M'));
      if ($subtract_second) {
        $t->sub(new \DateInterval('PT1S'));
      }
    }
    return $t;
  }

  /**
   * Set length
   *
   * @param integer $length
   * @return Game
   */
  public function setLength($length) {
    $this->length = $length;

    return $this;
  }

  /**
   * Get length
   *
   * @return integer
   */
  public function getLength() {
    return $this->length;
  }

  /**
   * Set agegroup
   *
   * @param AgeGroup $agegroup
   * @return Game
   */
  public function setAgegroup(AgeGroup $agegroup) {
    $this->agegroup = $agegroup;

    return $this;
  }

  /**
   * Get agegroup
   *
   * @return AgeGroup
   */
  public function getAgegroup() {
    return $this->agegroup;
  }

  /**
   * Set location
   *
   * @param string $location
   * @return Game
   */
  public function setLocation($location) {
    $this->location = $location;

    return $this;
  }

  /**
   * Get location
   *
   * @return string
   */
  public function getLocation() {
    return $this->location;
  }

  /**
   * Get OfficialCount - returns count of officials assigned to this game
   *
   * @return integer
   * *** this is still used by referee points pages
   */
  public function getOfficialCount() {
    $count = 0;
    $refs = $this->getOfficials();
    foreach ($refs as $ref) {
      if (!empty($ref)) {
        ++$count;
      }
    }
    return $count;
  }

  /**
   * Get Official Color - return color to display based on number of required refs
   * ** this is still used by referee points pages
   *
   * @param int $required
   * @param int $max
   * @return string
   */
  public function getOfficialColor($required = 1, $max = 3) {
    $refs = $this->getOfficialCount();
    if ($refs < $required)
      return '#ff8080'; // red
    else if ($refs >= $max)
      return '#80ff80'; // green
    return '#ffff80'; // yellow
  }

  /**
   * Get string representation of class
   *
   * @return string
   */
  public function __toString() {
    return $this->getDate()->format('Y-m-d ') . $this->getTime()->format('H:i ') . $this->getTeam1() . ' vs ' . $this->getTeam2();
  }

  /**
   * @Assert\IsTrue(message = "The same official cannot be assigned to more than one position!")
   */
  public function isAssignmentsLegal() {
    $refs = $this->getOfficials();
    $r = array();
    foreach ($refs as $ref) {
      if ($ref) {
        if (in_array($ref, $r, true)) {
          return false;
        } else {
          $r[] = $ref;
        }
      }
    }
    return true;
  }

  /**
   * Set alert_admin
   *
   * @param boolean $alert_admin
   * @return Game
   */
  public function setAlertAdmin($alert_admin) {
    $this->alert_admin = $alert_admin;

    return $this;
  }

  /**
   * Get alert_admin
   *
   * @return boolean
   */
  public function getAlertAdmin() {
    return $this->alert_admin;
  }

  /**
   * Set published
   *
   * @param boolean $published
   * @return Game
   */
  public function setPublished($published) {
    $this->published = $published;

    return $this;
  }

  /**
   * Get published
   *
   * @return boolean
   */
  public function getPublished() {
    return $this->published;
  }

  /**
   * Set region
   *
   * @param Region $region
   * @return Game
   */
  public function setRegion(Region $region = null) {
    $this->region = $region;

    return $this;
  }

  /**
   * Get region
   *
   * @return Region
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * Set timeslotlength
   *
   * @param integer $timeslotlength
   * @return Game
   */
  public function setTimeslotlength($timeslotlength) {
    $this->timeslotlength = $timeslotlength;

    return $this;
  }

  /**
   * Get timeslotlength
   *
   * @return integer
   */
  public function getTimeslotlength() {
    return $this->timeslotlength;
  }

  /**
   * Set status
   *
   * @param integer $status
   * @return Game
   */
  public function setStatus($status) {
    $this->status = $status;

    return $this;
  }

  /**
   * Get status
   *
   * @return integer
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get status
   *
   * @return array
   */
  static public function getStatusValues() {
    return [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_NORMAL => 'Normal',
        self::STATUS_COMPLETE => 'Complete',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_SUSPENDED => 'Suspended',
        self::STATUS_RAINOUT => 'Rained out',
        self::STATUS_FORFEIT => 'Forfeit',
        self::STATUS_POSTPONED => 'Postponed',
    ];
  }

  /**
   * Get status
   *
   * @return array
   */
  static public function getStatusValues2() {
    return [
        'Inactive' => self::STATUS_INACTIVE,
        'Normal' => self::STATUS_NORMAL,
        'Complete' => self::STATUS_COMPLETE,
        'Cancelled' => self::STATUS_CANCELLED,
        'Suspended' => self::STATUS_SUSPENDED,
        'Rained out' => self::STATUS_RAINOUT,
        'Forfeit' => self::STATUS_FORFEIT,
        'Postponed' => self::STATUS_POSTPONED,
    ];
  }

  /**
   * Get status
   *
   * @return string
   */
  public function getStatusString() {
    $s = $this->getStatusValues();
    if (array_key_exists($this->status, $s)) {
      $status = $s[$this->status];
    } else {
      $status = 'Unknown';
    }
    if (!$this->published) {
      $status .= ' (not published)';
    }

    return $status;
  }

  /**
   * Get status color
   *
   * @return string
   */
  public function getStatusColor() {
    $status = NULL;
    if (!$this->getPublished()) {
      $status = 'danger'; // red
    }
    switch ($this->getStatus()) {
      case self::STATUS_INACTIVE:
        $status = 'info'; // blue
        break;
      case self::STATUS_COMPLETE:
        $status = 'success'; // green
        break;
      case self::STATUS_CANCELLED:
        $status = 'danger'; // red
        break;
      case self::STATUS_SUSPENDED:
        $status = 'warning'; // yellow
        break;
      case self::STATUS_RAINOUT:
        $status = 'warning'; // yellow
        break;
      case self::STATUS_FORFEIT:
        $status = 'danger'; // red
        break;
      case self::STATUS_POSTPONED:
        $status = 'warning'; // yellow
        break;
    }
    return $status;
  }

  public function isActive() {
    $status = $this->getStatus();
    return $this->getPublished() &&
            (($status == self::STATUS_COMPLETE) ||
            ($status == self::STATUS_NORMAL));
  }

  public function isInactive() {
    $status = $this->getStatus();
    return $this->getPublished() && ($status == self::STATUS_INACTIVE);
  }

  /**
   * Set ref_notes
   *
   * @param string $refNotes
   * @return Game
   */
  public function setRefNotes($refNotes) {
    $this->ref_notes = $refNotes;

    return $this;
  }

  /**
   * Get ref_notes
   *
   * @return string
   */
  public function getRefNotes() {
    return $this->ref_notes;
  }

  /**
   * Set created
   *
   * @param \DateTime $created
   * @return Game
   */
  public function setCreated($created) {
    $this->created = $created;

    return $this;
  }

  /**
   * Get created
   *
   * @return \DateTime
   */
  public function getCreated() {
    if ($this->created == NULL) {
      return new \DateTime('01-01-1900');
    }
    return $this->created;
  }

  /**
   * Set updated
   *
   * @param \DateTime $updated
   * @return Game
   */
  public function setUpdated($updated) {
    $this->updated = $updated;

    return $this;
  }

  /**
   * Get updated
   *
   * @return \DateTime
   */
  public function getUpdated() {
    if ($this->updated == NULL) {
      return new \DateTime('01-01-1900');
    }
    return $this->updated;
  }

  /**
   * Set update_count
   *
   * @param integer $update_count
   * @return Game
   */
  public function setUpdateCount($update_count) {
    $this->update_count = $update_count;

    return $this;
  }

  /**
   * Get update_count
   *
   * @return integer
   */
  public function getUpdateCount() {
    return $this->update_count;
  }

  /**
   * Set updated_by
   *
   * @param User $updatedBy
   * @return Game
   */
  public function setUpdatedBy(User $updatedBy = null) {
    $this->updated_by = $updatedBy;

    return $this;
  }

  /**
   * Get updated_by
   *
   * @return User
   */
  public function getUpdatedBy() {
    return $this->updated_by;
  }

  /**
   * Set game_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setGameConflicts($gameConflicts) {
    $this->game_conflicts = $gameConflicts;

    return $this;
  }

  /**
   * Get game_conflicts
   *
   * @return Game
   */
  public function getGameConflicts() {
    return $this->game_conflicts;
  }

  /**
   * Set team1_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setTeam1Conflicts($team1Conflicts) {
    $this->team1_conflicts = $team1Conflicts;

    return $this;
  }

  /**
   * Get team1_conflicts
   *
   * @return Game
   */
  public function getTeam1Conflicts() {
    return $this->team1_conflicts;
  }

  /**
   * Set team2_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setTeam2Conflicts($team2Conflicts) {
    $this->team2_conflicts = $team2Conflicts;

    return $this;
  }

  /**
   * Get team2_conflicts
   *
   * @return Game
   */
  public function getTeam2Conflicts() {
    return $this->team2_conflicts;
  }

  /**
   * Set ref1_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setRef1Conflicts($ref1Conflicts) {
    $this->ref1_conflicts = $ref1Conflicts;

    return $this;
  }

  /**
   * Get ref1_conflicts
   *
   * @return Game
   */
  public function getRef1Conflicts() {
    return $this->ref1_conflicts;
  }

  /**
   * Set ref2_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setRef2Conflicts($ref2Conflicts) {
    $this->ref2_conflicts = $ref2Conflicts;

    return $this;
  }

  /**
   * Get ref2_conflicts
   *
   * @return Game
   */
  public function getRef2Conflicts() {
    return $this->ref2_conflicts;
  }

  /**
   * Set ref3_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setRef3Conflicts($ref3Conflicts) {
    $this->ref3_conflicts = $ref3Conflicts;

    return $this;
  }

  /**
   * Get ref3_conflicts
   *
   * @return Game
   */
  public function getRef3Conflicts() {
    return $this->ref3_conflicts;
  }

  /**
   * Set ref4_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setRef4Conflicts($ref4Conflicts) {
    $this->ref4_conflicts = $ref4Conflicts;

    return $this;
  }

  /**
   * Get ref4_conflicts
   *
   * @return Game
   */
  public function getRef4Conflicts() {
    return $this->ref4_conflicts;
  }

  /**
   * Set ref5_conflicts
   *
   * @param array|Game
   * @return Game
   */
  public function setRef5Conflicts($ref5Conflicts) {
    $this->ref5_conflicts = $ref5Conflicts;

    return $this;
  }

  /**
   * Get ref5_conflicts
   *
   * @return Game
   */
  public function getRef5Conflicts() {
    return $this->ref5_conflicts;
  }

  /**
   * Set project
   *
   * @param Project $project
   * @return Game
   */
  public function setProject(Project $project) {
    $this->project = $project;
    $this->project_id = $project->getId();

    return $this;
  }

  /**
   * Get project
   *
   * @return Project
   */
  public function getProject() {
    return $this->project;
  }

  /**
   * Get project
   *
   * @return integer
   */
  public function getProjectId() {
    return $this->project;
  }

  /**
   * Set project_id
   *
   * @param integer $projectId
   * @return Game
   */
  public function setProjectId($projectId) {
    $this->project_id = $projectId;

    return $this;
  }

  /**
   * Set short_note
   *
   * @param string $shortNote
   * @return Game
   */
  public function setShortNote($shortNote) {
    $this->short_note = $shortNote;

    return $this;
  }

  /**
   * Get short_note
   *
   * @return string
   */
  public function getShortNote() {
    return $this->short_note;
  }

  /**
   * @ORM\Column(name="number", type="integer", nullable=true)
   */
  protected $number;

  /**
   * @ORM\OneToMany(
   *   targetEntity="Cerad\Bundle\ProjectBundle\Entity\ProjectGameTeam",
   *   mappedBy = "projectGame",
   *   indexBy  = "slot",
   *   cascade  = {"all"})
   */
  protected $projectGameTeams;

  /**
   * @ORM\OneToMany(
   *   targetEntity="Cerad\Bundle\ProjectBundle\Entity\ProjectGameOfficial",
   *   mappedBy = "projectGame",
   *   indexBy  = "slot",
   *   cascade  = {"all"})
   */
  protected $projectGameOfficials;

  // Array access implementation
  public function offsetSet($offset, $value) {
    switch ($offset) {

      case 'number':
        $this->number = $value;
        return;

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->agegroup = $value;
        return;

      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->region = $value;
        return;

      // These should never happen
      case 'ref1':
      case 'ref2':
      case 'ref3':
      case 'ref4':
      case 'ref5':
      case 'team1': //return $this->getTeam1();
      case 'team2': //return $this->getTeam2();
      case 'score1':
      case 'score2':
        throw new \BadMethodCallException(sprintf('ProjectGameTrait::offSetSet %s', $offset));

      //case 'score1': $this->setScore1($value); return;
      //case 'score2': $this->setScore2($value); return;
    }
    $this->$offset = $value;
  }

  public function offsetGet($offset) {
    switch ($offset) {

      case 'number':
        return $this->number;

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->agegroup;

      case 'league':
      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->region;

      case 'end_time':
        return $this->getEndTime(true);

      case 'is_active':
        return $this->isActive();

      case 'project_game_teams':
        return $this->projectGameTeams;

      case 'project_game_officials':
        return $this->projectGameOfficials;

      case 'teams': return $this->getTeams();
      case 'team1': return $this->getTeam1();
      case 'team2': return $this->getTeam2();
      case 'score1': return $this->getScore1();
      case 'score2': return $this->getScore2();

      case 'ref1': return $this->getRef1();
      case 'ref2': return $this->getRef2();
      case 'ref3': return $this->getRef3();
      case 'ref4': return $this->getRef4();
      case 'ref5': return $this->getRef5();
    }
    return $this->$offset;
  }

  public function offsetExists($offset) {
    switch ($offset) {

      case 'number':
        return isset($this->number);

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        return isset($this->agegroup);

      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        return isset($this->region);
    }
    // Fix this
    return isset($this->$offset);
  }

  // This should never be called
  public function offsetUnset($offset) {
    switch ($offset) {

      case 'number':
        $this->number = null;
        return;

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->agegroup = null;
        return;

      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->region = null;
        return;
    }
    $this->$offset = null;
  }

  /* ================================================================
   * ProjectGameTeams stuff
   *
   */

  public function getProjectGameTeams() {
    return $this->projectGameTeams;
  }

  public function getProjectGameTeam($slot) {
    return $this->projectGameTeams[$slot];
  }

  public function getProjectGameTeamHome() {
    return $this->getProjectGameTeam('home');
  }

  public function getProjectGameTeamAway() {
    return $this->getProjectGameTeam('away');
  }

  public function setProjectGameTeams($projectGameTeams) {
    $this->projectGameTeams = $projectGameTeams;
    return $this;
  }

  /* =====================================================
   * Transitional methods
   *
   */

  public function getTeams() {
    return [$this->getTeam1(), $this->getTeam2()];
  }

  public function getTeam1() {
    return $this->getProjectGameTeamHome()['projectTeam'];
  }

  public function getTeam2() {
    return $this->getProjectGameTeamAway()['projectTeam'];
  }

  public function setTeam1($projectTeam) {
    return $this->getProjectGameTeamHome()['projectTeam'] = $projectTeam;
  }

  public function setTeam2($projectTeam) {
    return $this->getProjectGameTeamAway()['projectTeam'] = $projectTeam;
  }

  public function getScore1() {
    return $this->getProjectGameTeamHome()['score'];
  }

  public function getScore2() {
    return $this->getProjectGameTeamAway()['score'];
  }

  public function setScore1($score) {
    return $this->getProjectGameTeamHome()['score'] = $score;
  }

  public function setScore2($score) {
    return $this->getProjectGameTeamAway()['score'] = $score;
  }

  public function resetForClone() {
    $this->id = null;
    $this->setPublished(false);
    $this->setStatus(self::STATUS_INACTIVE);
    $this->setRef1(null); // TODO should not need these
    $this->setRef2(null);
    $this->setRef3(null);
    $this->setRef4(null);
    $this->setRef5(null);
    $this->setScore1(null);
    $this->setScore2(null);
    $this->setCreated(new \DateTime());
    $this->setUpdated(new \DateTime());
    $this->update_count = 0;
    $this->setNumber(0); // what should the default be? Probably null
    return $this;
  }

  public function __construct() {
    $this->setDate(new \DateTime());
    $this->setTime(new \DateTime());
    $this->status = self::STATUS_INACTIVE;
    $this->setCreated(new \DateTime());
    $this->setUpdated(new \DateTime());
    $this->update_count = 0;
    $this->game_conflicts = array();

    $this->projectGameTeams = [
        'home' => new ProjectGameTeam('home', $this),
        'away' => new ProjectGameTeam('away', $this),
    ];
    $this->projectGameOfficials = [
        'ref' => new ProjectGameOfficial('ref', $this),
        'ar1' => new ProjectGameOfficial('ar1', $this),
        'ar2' => new ProjectGameOfficial('ar2', $this),
    ];
  }

  /** =======================================================================
   * ProjectGameOfficials stuff
   */
  public function getRef1() {
    return isset($this->projectGameOfficials['ref']) ? $this->projectGameOfficials['ref'] ['projectOfficial'] : null;
  }

  public function getRef2() {
    return isset($this->projectGameOfficials['ar1']) ? $this->projectGameOfficials['ar1'] ['projectOfficial'] : null;
  }

  public function getRef3() {
    return isset($this->projectGameOfficials['ar2']) ? $this->projectGameOfficials['ar2'] ['projectOfficial'] : null;
  }

  public function getRef5() {
    return isset($this->projectGameOfficials['standby']) ? $this->projectGameOfficials['standby']['projectOfficial'] : null;
  }

  public function getRef4() {
    return isset($this->projectGameOfficials['mentor']) ? $this->projectGameOfficials['mentor'] ['projectOfficial'] : null;
  }

  public function getOfficials() {
    return [$this['ref1'], $this['ref2'], $this['ref3'], $this['ref4'], $this['ref5']];
  }

  public function setRef1($ref = null) {
    if (isset($this->projectGameOfficials['ref'])) {
      $this->projectGameOfficials['ref']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ref', $this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ref'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ref');
  }

  public function setRef2($ref = null) {
    if (isset($this->projectGameOfficials['ar1'])) {
      $this->projectGameOfficials['ar1']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ar1', $this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ar1'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ar1');
  }

  public function setRef3($ref = null) {
    if (isset($this->projectGameOfficials['ar2'])) {
      $this->projectGameOfficials['ar2']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ar2', $this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ar2'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ar2');
  }

  public function setRef5($ref = null) {
    if (isset($this->projectGameOfficials['standby'])) {
      $this->projectGameOfficials['standby']['projectOfficial'] = $ref;

      // Figure out how to delete if ref is null
      return;
    }
    if (!$ref) {
      return;
    }

    $projectGameOfficial = new ProjectGameOfficial('standby', $this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['standby'] = $projectGameOfficial;
  }

  public function setRef4($ref = null) {
    if (isset($this->projectGameOfficials['mentor'])) {
      $this->projectGameOfficials['mentor']['projectOfficial'] = $ref;
      return;
    }
    if (!$ref) {
      return;
    }

    $projectGameOfficial = new ProjectGameOfficial('mentor', $this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['mentor'] = $projectGameOfficial;
  }

  public function getProjectGameOfficials() {
    return $this->projectGameOfficials;
  }

  public function getProjectGameOfficial($slot) {
    return $this->projectGameOfficials[$slot];
  }

  public function setProjectGameOfficials($projectGameOfficials) {
    $this->projectGameOfficials = $projectGameOfficials;
    return $this;
  }

  public function getNumber() {
    return $this->number;
  }

  public function setNumber($number) {
    $this->number = $number;
    return $this;
  }

  /** =====================================================
   * Need to make a "deep" copy of officials and teams
   * Otherwise the change detection stuff does not work
   *
   * Also take into account the possibility that we may be using pure arrays instead of doctrine collections
   */
  public function __clone() {
    /** @var object|array $projectGameTeams */
    $projectGameTeams = $this->projectGameTeams;
    if (is_object($projectGameTeams)) {
      $projectGameTeams = clone $projectGameTeams;
    }
    foreach ($projectGameTeams as $key => $value) {
      if (is_object($value)) {
        $value = clone $value;
      }
      $projectGameTeams[$key] = $value;
    }
    $this->projectGameTeams = $projectGameTeams;

    /** @var object|array $projectGameOfficials */
    $projectGameOfficials = $this->projectGameOfficials;

    if (is_object($projectGameOfficials)) {
      $projectGameOfficials = clone $projectGameOfficials;
    }
    foreach ($projectGameOfficials as $key => $value) {
      if (is_object($value)) {
        $value = clone $value;
      }
      $projectGameOfficials[$key] = $value;
    }
    $this->projectGameOfficials = $projectGameOfficials;
  }

}
