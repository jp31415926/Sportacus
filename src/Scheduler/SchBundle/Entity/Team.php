<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Cerad\Bundle\ProjectBundle\Entity\ProjectTeamTrait;

/**
 * Scheduler\SchBundle\Entity\Team
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\TeamRepository")
 * #UniqueEntity("name")
 * @UniqueEntity(
 *     fields={"name", "project", "region"},
 *     ignoreNull=true,
 *     message="Name must be unique inside a region and project"
 * )
 */
class Team implements \ArrayAccess
{
  use ProjectTeamTrait;

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
   * @ORM\Column(name="name", type="string", length=64)
   * @Assert\NotBlank()
   */
  private $name;

  /**
   * @var integer $agegroup
   *
   * @ORM\ManyToOne(targetEntity="AgeGroup")
   * @ORM\JoinColumn(name="agegroup_id", referencedColumnName="id")
   **/
  private $agegroup;

  /**
   * @var string $coach
   *
   * @ORM\Column(name="coach_name", type="string", length=64, nullable=true)
   */
  private $coach;

  /**
   * @var string $coach_phone
   *
   * @ORM\Column(name="coach_phone", type="string", length=20, nullable=true)
   */
  private $coach_phone;

  /**
   * @var string $coach_email
   *
   * @ORM\Column(name="coach_email", type="string", length=255, nullable=true)
   * @Assert\Email(
   *     message = "The email '{{ value }}' is not a valid email address.",
   *     checkMX = true
   * )
   */
  private $coach_email;

  /**
   * @var string $poc_email
   *
   * @ORM\Column(name="poc_email", type="string", length=255, nullable=true)
   * @Assert\Email(
   *     message = "The email '{{ value }}' is not a valid email address.",
   *     checkMX = true
   * )
   */
  private $poc_email;

  /**
   * @var string $colors_home
   *
   * @ORM\Column(name="colors_home", type="string", length=64, nullable=true)
   */
  private $colors_home;

  /**
   * @var string $colors_away
   *
   * @ORM\Column(name="colors_away", type="string", length=64, nullable=true)
   */
  private $colors_away;

  /**
   * @var integer $region
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
   * Get id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Reset id
   *
   * @return Team
   */
  public function resetForClone()
  {
    $this->id = null;
    return $this;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return Team
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
   * Set agegroup
   *
   * @param integer $agegroup
   * @return Team
   */
  public function setAgegroup($agegroup)
  {
    $this->agegroup = $agegroup;

    return $this;
  }

  /**
   * Get agegroup
   *
   * @return integer
   */
  public function getAgegroup()
  {
    return $this->agegroup;
  }

  /**
   * Set colors
   *
   * @param string $colors
   * @return Team
   */
  public function setColors($colors)
  {
    $this->colors_home = $colors;

    return $this;
  }

  /**
   * Get colors
   *
   * @return string
   */
  public function getColors()
  {
    return $this->colors_home;
  }

  /**
   * Set colors_home
   *
   * @param string $colors_home
   * @return Team
   */
  public function setColorsHome($colors_home)
  {
    $this->colors_home = $colors_home;

    return $this;
  }

  /**
   * Get colors
   *
   * @return string
   */
  public function getColorsHome()
  {
    return $this->colors_home;
  }

  /**
   * Set colors_away
   *
   * @param string $colors
   * @return Team
   */
  public function setColorsAway($colors)
  {
    $this->colors_away = $colors;

    return $this;
  }

  /**
   * Get colors_away
   *
   * @return string
   */
  public function getColorsAway()
  {
    return $this->colors_away;
  }

  /**
   * Set coach
   *
   * @param string $coach
   * @return Team
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
   * Get string representation of class
   *
   * @return string
   */
  public function __toString()
  {
    $name = $this->getName();
    //$agegroup = $this->getAgegroup();
    //if (!empty($agegroup))
    //    $name = $agegroup . '-' . $name;
    //$coach = $this->getCoach();
    //if (!empty($coach))
    //    $name = $name . '-' . $coach;
    return $name;

    //return $this->getAgegroup().'-'.$this->getName();
    //return $this->getName();
  }


  /**
   * Set region
   *
   * @param \Scheduler\SchBundle\Entity\Region $region
   * @return Team
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
   * Set coach_email
   *
   * @param string $coachEmail
   * @return Team
   */
  public function setCoachEmail($coachEmail)
  {
    $this->coach_email = $coachEmail;

    return $this;
  }

  /**
   * Get coach_email
   *
   * @return string
   */
  public function getCoachEmail()
  {
    return $this->coach_email;
  }

  /**
   * Set poc_email
   *
   * @param string $pocEmail
   * @return Team
   */
  public function setPocEmail($pocEmail)
  {
    $this->poc_email = $pocEmail;

    return $this;
  }

  /**
   * Get poc_email
   *
   * @return string
   */
  public function getPocEmail()
  {
    return $this->poc_email;
  }

  /**
   * Set project
   *
   * @param \Scheduler\SchBundle\Entity\Project $project
   * @return Team
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
   * Set coach_phone
   *
   * @param string $coachPhone
   * @return Team
   */
  public function setCoachPhone($coachPhone)
  {
    // delete all characters except digits
    $coachPhone = preg_replace('/[^0-9]/', '', $coachPhone);
    if (empty($coachPhone))
      $coachPhone = NULL;
    $this->coach_phone = $coachPhone;

    return $this;
  }

  /**
   * Get coach_phone
   *
   * @return string
   */
  public function getCoachPhone()
  {
    return $this->coach_phone;
  }
}