<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Cerad\Bundle\ProjectBundle\Entity\ProjectLevelTrait;

/**
 * Scheduler\SchBundle\Entity\AgeGroup
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\AgeGroupRepository")
 * #UniqueEntity("name")
 * @UniqueEntity(
 *     fields={"name", "project", "region"},
 *     ignoreNull=true,
 *     message="AgeGroup Name must be unique inside a region and project"
 * )
 */
class AgeGroup implements \ArrayAccess
{
  use ProjectLevelTrait;

  /**
   * @var integer $id
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var string $name
   *
   * @Assert\NotBlank()
   *
   * @ORM\Column(name="name", type="string", length=32)
   */
  private $name;

  /**
   * @var string $region
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

  /**
   * @var integer $difficulty
   *
   * @ORM\Column(name="difficulty", type="integer")
   */
  private $difficulty;

  /**
   * @var integer $points_multiplier
   *
   * @ORM\Column(name="points_multiplier", type="integer")
   */
  private $points_multiplier;

  /**
   * @var integer $points_ref1
   *
   * @ORM\Column(name="points_ref1", type="integer")
   */
  private $points_ref1;

  /**
   * @var integer $points_youth_ref1
   *
   * @ORM\Column(name="points_youth_ref1", type="integer")
   */
  private $points_youth_ref1;

  /**
   * @var integer $points_ref2
   *
   * @ORM\Column(name="points_ref2", type="integer")
   */
  private $points_ref2;

  /**
   * @var integer $points_youth_ref2
   *
   * @ORM\Column(name="points_youth_ref2", type="integer")
   */
  private $points_youth_ref2;

  /**
   * @var integer $points_ref3
   *
   * @ORM\Column(name="points_ref3", type="integer")
   */
  private $points_ref3;

  /**
   * @var integer $points_youth_ref3
   *
   * @ORM\Column(name="points_youth_ref3", type="integer")
   */
  private $points_youth_ref3;

  /**
   * @var integer $points_team_goal
   *
   * @ORM\Column(name="points_team_goal", type="integer")
   */
  private $points_team_goal;


  public function __construct()
  {
    $this->points_multiplier = 1;
    $this->points_ref1 = 1;
    $this->points_youth_ref1 = 1;
    $this->points_ref2 = 1;
    $this->points_youth_ref2 = 1;
    $this->points_ref3 = 1;
    $this->points_youth_ref3 = 1;
    $this->points_team_goal = 0;
  }

  public function pointsNonZero()
  {
    return
      ($this->points_ref1 > 0) ||
      ($this->points_ref2 > 0) ||
      ($this->points_ref3 > 0) ||
      ($this->points_youth_ref1 > 0) ||
      ($this->points_youth_ref2 > 0) ||
      ($this->points_youth_ref3 > 0);
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
   * @return AgeGroup
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
   * Set difficulty
   *
   * @param integer $difficulty
   * @return AgeGroup
   */
  public function setDifficulty($difficulty)
  {
    $this->difficulty = $difficulty;

    return $this;
  }

  /**
   * Get difficulty
   *
   * @return integer
   */
  public function getDifficulty()
  {
    return $this->difficulty;
  }


  /**
   * Get string representation of class
   *
   * @return string
   */
  public function __toString()
  {
    if ($this->getRegion())
      return $this->getRegion()->getName() . ' ' . $this->getName();
    else
      return 'NA ' . $this->getName();
  }


  /**
   * Set points_multiplier
   *
   * @param integer $pointsMultiplier
   * @return AgeGroup
   */
  public function setPointsMultiplier($pointsMultiplier)
  {
    $this->points_multiplier = $pointsMultiplier;

    return $this;
  }

  /**
   * Get points_multiplier
   *
   * @return integer
   */
  public function getPointsMultiplier()
  {
    return $this->points_multiplier;
  }

  /**
   * Set project
   *
   * @param \Scheduler\SchBundle\Entity\Project $project
   * @return AgeGroup
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

  /**
   * Set region
   *
   * @param \Scheduler\SchBundle\Entity\Region $region
   * @return AgeGroup
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
   * Set points_ref1
   *
   * @param integer $pointsRef1
   * @return AgeGroup
   */
  public function setPointsRef1($pointsRef1)
  {
    $this->points_ref1 = $pointsRef1;

    return $this;
  }

  /**
   * Get points_ref1
   *
   * @return integer
   */
  public function getPointsRef1()
  {
    return $this->points_ref1;
  }

  /**
   * Set points_youth_ref1
   *
   * @param integer $pointsYouthRef1
   * @return AgeGroup
   */
  public function setPointsYouthRef1($pointsYouthRef1)
  {
    $this->points_youth_ref1 = $pointsYouthRef1;

    return $this;
  }

  /**
   * Get points_youth_ref1
   *
   * @return integer
   */
  public function getPointsYouthRef1()
  {
    return $this->points_youth_ref1;
  }

  /**
   * Set points_ref2
   *
   * @param integer $pointsRef2
   * @return AgeGroup
   */
  public function setPointsRef2($pointsRef2)
  {
    $this->points_ref2 = $pointsRef2;

    return $this;
  }

  /**
   * Get points_ref2
   *
   * @return integer
   */
  public function getPointsRef2()
  {
    return $this->points_ref2;
  }

  /**
   * Set points_youth_ref2
   *
   * @param integer $pointsYouthRef2
   * @return AgeGroup
   */
  public function setPointsYouthRef2($pointsYouthRef2)
  {
    $this->points_youth_ref2 = $pointsYouthRef2;

    return $this;
  }

  /**
   * Get points_youth_ref2
   *
   * @return integer
   */
  public function getPointsYouthRef2()
  {
    return $this->points_youth_ref2;
  }

  /**
   * Set points_ref3
   *
   * @param integer $pointsRef3
   * @return AgeGroup
   */
  public function setPointsRef3($pointsRef3)
  {
    $this->points_ref3 = $pointsRef3;

    return $this;
  }

  /**
   * Get points_ref3
   *
   * @return integer
   */
  public function getPointsRef3()
  {
    return $this->points_ref3;
  }

  /**
   * Set points_youth_ref3
   *
   * @param integer $pointsYouthRef3
   * @return AgeGroup
   */
  public function setPointsYouthRef3($pointsYouthRef3)
  {
    $this->points_youth_ref3 = $pointsYouthRef3;

    return $this;
  }

  /**
   * Get points_youth_ref3
   *
   * @return integer
   */
  public function getPointsYouthRef3()
  {
    return $this->points_youth_ref3;
  }

  /**
   * Set points_team_goal
   *
   * @param integer $pointsTeamGoal
   * @return AgeGroup
   */
  public function setPointsTeamGoal($pointsTeamGoal)
  {
    $this->points_team_goal = $pointsTeamGoal;

    return $this;
  }

  /**
   * Get points_team_goal
   *
   * @return integer
   */
  public function getPointsTeamGoal()
  {
    return $this->points_team_goal;
  }
}