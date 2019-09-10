<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefPntsMap
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\RefPntsMapRepository")
 */
class RefPntsMap
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
   * @var integer $user
   *
   * @ORM\ManyToOne(targetEntity="User")
   */
  private $user;

  /**
   * @var string $team
   *
   * @ORM\ManyToOne(targetEntity="Team")
   */
  private $team;

  /**
   * @var integer
   *
   * @ORM\Column(name="priority", type="integer")
   */
  private $priority;

  /**
   * @var integer $project
   *
   * @ORM\ManyToOne(targetEntity="Project")
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
   * Set user
   *
   * @param integer $user
   * @return RefPntsMap
   */
  public function setUser($user)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user
   *
   * @return integer
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Set team
   *
   * @param integer $team
   * @return RefPntsMap
   */
  public function setTeam($team)
  {
    $this->team = $team;

    return $this;
  }

  /**
   * Get team
   *
   * @return integer
   */
  public function getTeam()
  {
    return $this->team;
  }

  /**
   * Set priority
   *
   * @param integer $priority
   * @return RefPntsMap
   */
  public function setPriority($priority)
  {
    $this->priority = $priority;

    return $this;
  }

  /**
   * Get priority
   *
   * @return integer
   */
  public function getPriority()
  {
    return $this->priority;
  }

  /**
   * Set project
   *
   * @param integer $project
   * @return RefPntsMap
   */
  public function setProject($project)
  {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project
   *
   * @return integer
   */
  public function getProject()
  {
    return $this->project;
  }
}
