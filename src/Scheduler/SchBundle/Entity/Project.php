<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Cerad\Bundle\ProjectBundle\Entity\ProjectTrait;

/**
 * Project
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\ProjectRepository")
 */
class Project implements \ArrayAccess
{
  use ProjectTrait;

  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="long_name", type="string", length=255)
   */
  private $long_name;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="start_date", type="date")
   */
  private $start_date;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="end_date", type="date")
   */
  private $end_date;

  /**
   * true if system should use team's region referee point rules instead of referee's region rules
   *
   * @var boolean $use_team_refpnt_rules ;
   *
   * @ORM\Column(name="use_team_refpnt_rules", type="boolean", nullable=true)
   */
  private $use_team_refpnt_rules;

  /**
   * @var boolean $show_referee_region ;
   *
   * @ORM\Column(name="show_referee_region", type="boolean", nullable=true)
   */
  private $show_referee_region;

  /**
   * @var string
   *
   * @ORM\Column(name="sportstr", type="string", length=64, nullable=true)
   */
  private $sportstr;

  /**
   * @ORM\ManyToMany(targetEntity="OffPos", cascade={"persist"})
   * @ORM\JoinTable(name="project_offpos",
   *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
   *      inverseJoinColumns={@ORM\JoinColumn(name="offpos_id", referencedColumnName="id")}
   *      )
   **/
  private $offpositions;

  /**
   * @var boolean $archived
   *
   * @ORM\Column(name="archived", type="boolean", nullable=true, options={"default" : 0})
   */
  private $archived;


  public function __construct()
  {
    $this->offpositions = new \Doctrine\Common\Collections\ArrayCollection();
    // setup some default positions... testing, and only useful for new projects
    $pos = new OffPos();
    $pos->setName('Referee');
    $pos->setShortname('CR');
    $pos->setDiffavail(60);
    $pos->setDiffvisable(60);
    $pos->setDiffreq(80);
    $this->offpositions->add($pos);
    $pos = new OffPos();
    $pos->setName('Asst Ref 1');
    $pos->setShortname('AR1');
    $pos->setDiffavail(80);
    $pos->setDiffvisable(80);
    $pos->setDiffreq(120);
    $this->offpositions->add($pos);
    $pos = new OffPos();
    $pos->setName('Asst Ref 2');
    $pos->setShortname('AR2');
    $pos->setDiffavail(80);
    $pos->setDiffvisable(80);
    $pos->setDiffreq(140);
    $this->offpositions->add($pos);
    $pos = new OffPos();
    $pos->setName('Standby');
    $pos->setShortname('SBY');
    $pos->setDiffavail(100);
    $pos->setDiffvisable(200);
    $pos->setDiffreq(200);
    $this->offpositions->add($pos);
    $pos = new OffPos();
    $pos->setName('4th Official');
    $pos->setShortname('4TH');
    $pos->setDiffavail(140);
    $pos->setDiffvisable(200);
    $pos->setDiffreq(200);
    $this->offpositions->add($pos);
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
   * Set name
   *
   * @param string $name
   * @return Project
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set long_name
   *
   * @param string $longName
   * @return Project
   */
  public function setLongName($longName)
  {
    $this->long_name = $longName;

    return $this;
  }

  /**
   * Get long_name
   *
   * @return string
   */
  public function getLongName()
  {
    return $this->long_name;
  }

  /**
   * Set start_date
   *
   * @param \DateTime $startDate
   * @return Project
   */
  public function setStartDate($startDate)
  {
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
   * Set end_date
   *
   * @param \DateTime $endDate
   * @return Project
   */
  public function setEndDate($endDate)
  {
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

  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Set use_team_refpnt_rules
   *
   * @param boolean $useTeamRefpntRules
   * @return Project
   */
  public function setUseTeamRefpntRules($useTeamRefpntRules)
  {
    $this->use_team_refpnt_rules = $useTeamRefpntRules;

    return $this;
  }

  /**
   * Get use_team_refpnt_rules
   *
   * @return boolean
   */
  public function getUseTeamRefpntRules()
  {
    return $this->use_team_refpnt_rules;
  }

  /**
   * Set sportstr
   *
   * @param string $sportstr
   * @return Project
   */
  public function setSportstr($sportstr)
  {
    $this->sportstr = $sportstr;

    return $this;
  }

  /**
   * Get sport
   *
   * @return string
   */
  public function getSportstr()
  {
    return $this->sportstr;
  }

  /**
   * Add offpositions
   *
   * @param \Scheduler\SchBundle\Entity\OffPos $offpositions
   * @return Project
   */
  public function addOffposition(\Scheduler\SchBundle\Entity\OffPos $offpositions)
  {
    $this->offpositions[] = $offpositions;

    return $this;
  }

  /**
   * Remove offpositions
   *
   * @param \Scheduler\SchBundle\Entity\OffPos $offpositions
   */
  public function removeOffposition(\Scheduler\SchBundle\Entity\OffPos $offpositions)
  {
    $this->offpositions->removeElement($offpositions);
  }

  /**
   * Get offpositions
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getOffpositions()
  {
    // TESTING! - if no positions, populate some defaults until we add code for the user to add them.
    /*      if (count($this->offpositions) == 0)
          {
            $pos = new OffPos();
            $pos->setName('Referee');
            $pos->setShortname('CR');
            $pos->setDiffavail(60);
            $pos->setDiffvisable(60);
            $pos->setDiffreq(80);
            $this->offpositions->add($pos);
            $pos = new OffPos();
            $pos->setName('Asst Ref 1');
            $pos->setShortname('AR1');
            $pos->setDiffavail(80);
            $pos->setDiffvisable(80);
            $pos->setDiffreq(120);
            $this->offpositions->add($pos);
            $pos = new OffPos();
            $pos->setName('Asst Ref 2');
            $pos->setShortname('AR2');
            $pos->setDiffavail(80);
            $pos->setDiffvisable(80);
            $pos->setDiffreq(140);
            $this->offpositions->add($pos);
          }*/
    return $this->offpositions;
  }

  /**
   * Set show_referee_region
   *
   * @param boolean $showRefereeRegion
   * @return Project
   */
  public function setShowRefereeRegion($showRefereeRegion)
  {
    $this->show_referee_region = $showRefereeRegion;

    return $this;
  }

  /**
   * Get show_referee_region
   *
   * @return boolean
   */
  public function getShowRefereeRegion()
  {
    return $this->show_referee_region;
  }
  /**
   * Set archived
   *
   * @param boolean $archived
   * @return Project
   */
  public function setArchived($archived)
  {
    $this->archived = $archived;

    return $this;
  }

  /**
   * Get archived
   *
   * @return boolean
   */
  public function getArchived()
  {
    return $this->archived;
  }
}
