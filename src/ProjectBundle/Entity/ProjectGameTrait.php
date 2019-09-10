<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

/** =======================================================
 * 18 Aug 2015 - Obsolete
 * Merged into SchBundle\Entity\Game
 */
use Doctrine\ORM\Mapping as ORM;

trait ProjectGameTrait
{
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
    switch($offset) {

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
      case 'team1':  //return $this->getTeam1();
      case 'team2':  //return $this->getTeam2();
      case 'score1':
      case 'score2':
        throw new \BadMethodCallException(sprintf('ProjectGameTrait::offSetSet %s',$offset));

      //case 'score1': $this->setScore1($value); return;
      //case 'score2': $this->setScore2($value); return;

    }
    $this->$offset = $value;
  }
  public function offsetGet($offset) {
    switch($offset) {

      case 'number':
        return $this->number;

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->agegroup;

      case 'league':
      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->region;

      case 'project_game_teams':
        return $this->projectGameTeams;

      case 'project_game_officials':
        return $this->projectGameOfficials;

      case 'teams':  return $this->getTeams();
      case 'team1':  return $this->getTeam1();
      case 'team2':  return $this->getTeam2();
      case 'score1': return $this->getScore1();
      case 'score2': return $this->getScore2();

      case 'ref1':   return $this->getRef1();
      case 'ref2':   return $this->getRef2();
      case 'ref3':   return $this->getRef3();
      case 'ref4':   return $this->getRef4();
      case 'ref5':   return $this->getRef5();

    }
    return $this->$offset;
  }
  public function offsetExists($offset) {
    switch($offset) {

      case 'number':
        return isset($this->number);

      case 'level':
        /** @noinspection PhpUndefinedFieldInspection */
        return isset($this->agegroup);

      case 'organization':
        /** @noinspection PhpUndefinedFieldInspection */
        return isset($this->region);

    }
    return isset($this->$offset);
  }
  public function offsetUnset($offset) {
    switch($offset) {

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
  public function getProjectGameTeams()
  {
    return $this->projectGameTeams;
  }
  public function getProjectGameTeam($slot)
  {
    return $this->projectGameTeams[$slot];
  }
  public function getProjectGameTeamHome()
  {
    return $this->getProjectGameTeam('home');
  }
  public function getProjectGameTeamAway()
  {
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
  public function getTeams()
  {
    return [$this->getTeam1(),$this->getTeam2()];
  }
  public function getTeam1()
  {
    return $this->getProjectGameTeamHome()['projectTeam'];
  }
  public function getTeam2()
  {
    return $this->getProjectGameTeamAway()['projectTeam'];
  }
  public function setTeam1($projectTeam)
  {
    return $this->getProjectGameTeamHome()['projectTeam'] = $projectTeam;
  }
  public function setTeam2($projectTeam)
  {
    return $this->getProjectGameTeamAway()['projectTeam'] = $projectTeam;
  }
  public function getScore1()
  {
    return $this->getProjectGameTeamHome()['score'];
  }
  public function getScore2()
  {
    return $this->getProjectGameTeamAway()['score'];
  }
  public function setScore1($score)
  {
    return $this->getProjectGameTeamHome()['score'] = $score;
  }
  public function setScore2($score)
  {
    return $this->getProjectGameTeamAway()['score'] = $score;
  }
  public function resetForClone()
  {
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
  public function __construct()
  {
    $this->setDate(new \DateTime());
    $this->setTime(new \DateTime());
    $this->status = self::STATUS_INACTIVE;
    $this->setCreated(new \DateTime());
    $this->setUpdated(new \DateTime());
    $this->update_count = 0;
    $this->game_conflicts = array();

    $this->projectGameTeams = [
      'home' => new ProjectGameTeam('home',$this),
      'away' => new ProjectGameTeam('away',$this),
    ];
    $this->projectGameOfficials = [
      'ref' => new ProjectGameOfficial('ref',$this),
      'ar1' => new ProjectGameOfficial('ar1',$this),
      'ar2' => new ProjectGameOfficial('ar2',$this),
    ];
  }


  /** =======================================================================
   * ProjectGameOfficials stuff
   */
  public function getRef1() { return isset($this->projectGameOfficials['ref'])     ? $this->projectGameOfficials['ref']    ['projectOfficial'] : null; }
  public function getRef2() { return isset($this->projectGameOfficials['ar1'])     ? $this->projectGameOfficials['ar1']    ['projectOfficial'] : null; }
  public function getRef3() { return isset($this->projectGameOfficials['ar2'])     ? $this->projectGameOfficials['ar2']    ['projectOfficial'] : null; }
  public function getRef4() { return isset($this->projectGameOfficials['standby']) ? $this->projectGameOfficials['standby']['projectOfficial'] : null; }
  public function getRef5() { return isset($this->projectGameOfficials['mentor'])  ? $this->projectGameOfficials['mentor'] ['projectOfficial'] : null; }

  public function getOfficials()
  {
    return [$this['ref1'],$this['ref2'],$this['ref3'],$this['ref4'],$this['ref5']];
  }
  public function setRef1($ref=null)
  {
    if (isset($this->projectGameOfficials['ref'])) {
      $this->projectGameOfficials['ref']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ref',$this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ref'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ref');
  }
  public function setRef2($ref=null)
  {
    if (isset($this->projectGameOfficials['ar1'])) {
      $this->projectGameOfficials['ar1']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ar1',$this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ar1'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ar1');
  }
  public function setRef3($ref=null)
  {
    if (isset($this->projectGameOfficials['ar2'])) {
      $this->projectGameOfficials['ar2']['projectOfficial'] = $ref;
      return;
    }
    $projectGameOfficial = new ProjectGameOfficial('ar2',$this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['ar2'] = $projectGameOfficial;
    return;

    //throw new \RuntimeException('Missing Game Official Slot: ar2');
  }
  public function setRef4($ref=null)
  {
    if (isset($this->projectGameOfficials['standby'])) {
      $this->projectGameOfficials['standby']['projectOfficial'] = $ref;

      // Figure out how to delete if ref is null
      return;
    }
    if (!$ref) return;

    $projectGameOfficial = new ProjectGameOfficial('standby',$this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['standby'] = $projectGameOfficial;
  }
  public function setRef5($ref=null)
  {
    if (isset($this->projectGameOfficials['mentor'])) {
      $this->projectGameOfficials['mentor']['projectOfficial'] = $ref;
      return;
    }
    if (!$ref) return;

    $projectGameOfficial = new ProjectGameOfficial('mentor',$this);
    $projectGameOfficial['projectOfficial'] = $ref;
    $this->projectGameOfficials['mentor'] = $projectGameOfficial;
  }
  public function getProjectGameOfficials()
  {
    return $this->projectGameOfficials;
  }

  public function getProjectGameOfficial($slot)
  {
    return $this->projectGameOfficials[$slot];
  }
  public function setProjectGameOfficials($projectGameOfficials) {
    $this->projectGameOfficials = $projectGameOfficials;
    return $this;
  }


  /* ==================================================
   * Should use array syntax but leave for now
   *
   */
  // Replace idstr with game number
  public function getNumber()
  {
    return $this->number;
  }
  public function setNumber($number)
  {
    $this->number = $number;
    return $this;
  }
}
